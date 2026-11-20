<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Donasi;
use App\Models\Campaign;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConf;

class TransaksiController extends Controller
{
    public function __construct()
    {
        MidtransConf::$serverKey = config('midtrans.server_key');
        MidtransConf::$isProduction = config('midtrans.is_production');
        MidtransConf::$isSanitized = config('midtrans.is_sanitized');
        MidtransConf::$is3ds = config('midtrans.is_3ds');
        MidtransConf::$overrideNotifUrl = config('midtrans.callback_url');
    }

    public function berDonasi(Request $request)
    {
        $validatedData = $request->validate([
            'campaign_id' => 'required|exists:campaigns,campaign_id',
            'message' => 'nullable|string|max:200',
            'amount' => 'required|numeric|min:1000',
            'show_name' => 'nullable|string|in:on,off',
        ]);

        // Kampanye harus aktif dan belum berakhir
        $targetCampaign = Campaign::findOrFail($validatedData['campaign_id']);
        if (!$targetCampaign->status || $targetCampaign->end_date->endOfDay()->isPast()) {
            return back()->with('error', 'Kampanye ini sudah tidak menerima donasi.');
        }

        if (Auth::check()) {
            $validatedData['user_id'] = Auth::user()->user_id;
        }

        $validatedData['show_name'] = $request->input('show_name') === 'on' ? 1 : 0;
        $donation = Donasi::create($validatedData);
        $transaksi = Transaksi::create([
            'donation_id' => $donation->donation_id,
            'midtrans_order_id' => 'ORD-' . Str::uuid(),
            'gross_amount' => $donation->amount,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $transaksi->midtrans_order_id,
                'gross_amount' => $transaksi->gross_amount,
            ],
            "item_details" => array(
                [
                    "id" => uniqid(),
                    "name" => "Donasi- " . $targetCampaign->title,
                    "quantity" => 1,
                    "price" => $donation->amount,
                ]
            ),
            'customer_details' => [
                'first_name' => Auth::user()->name ?? 'Guest',
                'email' => Auth::user()->email ?? null,
                'phone' => Auth::user()->phone ?? null,
            ],
        ];

        $transaksi->snap_token = Snap::getSnapToken($params);
        $transaksi->save();

        if (!Auth::check()) {
            $request->session()->push('guest_transactions', $transaksi->transaction_id);
        }

        return redirect()->to('donasi/payment/' . $transaksi->transaction_id);
    }

    public function handleCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashedKey !== $request->signature_key) {
            Log::warning('Midtrans callback invalid signature', [
                'order_id' => $request->order_id,
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;

        // Temukan transaksi berdasarkan `order_id`
        $transaction = Transaksi::where('midtrans_order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Midtrans callback order not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Pastikan nominal notifikasi sesuai dengan transaksi
        if ((int) round((float) $request->gross_amount) !== (int) $transaction->gross_amount) {
            Log::warning('Midtrans callback amount mismatch', [
                'order_id' => $orderId,
                'expected' => (int) $transaction->gross_amount,
                'received' => $request->gross_amount,
            ]);
            return response()->json(['message' => 'Amount mismatch'], 400);
        }

        // Temukan donasi yang terkait dengan transaksi
        $donation = Donasi::find($transaction->donation_id);

        if (!$donation) {
            Log::warning('Midtrans callback donation not found', [
                'order_id' => $orderId,
                'donation_id' => $transaction->donation_id,
            ]);
            return response()->json(['message' => 'Donation not found'], 404);
        }

        if (in_array($transactionStatus, ['partial_refund', 'partial_chargeback'], true)) {
            Log::warning('Midtrans partial refund/chargeback requires manual review', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
            return response()->json(['message' => 'Callback received successfully']);
        }

        $isReversal = in_array($transactionStatus, ['refund', 'chargeback'], true);

        // Abaikan notifikasi untuk transaksi yang sudah final kecuali refund/chargeback penuh
        if ($transaction->status === 'completed' && !$isReversal) {
            return response()->json(['message' => 'Transaction already completed']);
        }

        // Perbarui status transaksi dan donasi berdasarkan notifikasi
        if ($isReversal) {
            $transaction->status = 'failed';
            $donation->status = 'failed';
            Log::warning('Midtrans refund/chargeback processed', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
        } elseif ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($transactionStatus === 'capture' && $request->fraud_status === 'challenge') {
                $transaction->status = 'pending';
                $donation->status = 'pending';
            } else {
                $transaction->status = 'completed';
                $donation->status = 'completed';
            }
        } elseif ($transactionStatus === 'pending') {
            $transaction->status = 'pending';
            $donation->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $transaction->status = 'failed';
            $donation->status = 'failed';
        } else {
            Log::warning('Unhandled Midtrans transaction_status', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
            return response()->json(['message' => 'Callback received successfully']);
        }

        $transaction->save();
        $donation->save();
        $campaign = Campaign::find($donation->campaign_id);

        if ($campaign) {
            $collectedAmount = Donasi::where('campaign_id', $campaign->campaign_id)
                ->where('status', 'completed')
                ->sum('amount');

            $campaign->collected_amount = $collectedAmount;
            $campaign->save();
        }

        return response()->json(['message' => 'Callback received successfully']);
    }
}
