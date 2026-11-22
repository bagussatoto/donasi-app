<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DisbursementController extends Controller
{
    /**
     * Tampilkan riwayat semua pengajuan pencairan dana.
     */
    public function index()
    {
        $disbursements = Disbursement::with(['campaign', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.disbursement.index', compact('disbursements'));
    }

    /**
     * Form pengajuan pencairan dana.
     */
    public function create()
    {
        // Hanya kampanye yang memiliki dana terkumpul > 0
        $campaigns = Campaign::where('collected_amount', '>', 0)->get();

        return view('admin.disbursement.create', compact('campaigns'));
    }

    /**
     * Simpan data pencairan dana baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,campaign_id',
            'amount' => 'required|numeric|min:10000',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:150',
            'purpose' => 'required|string|max:500',
            'receipt_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'amount.min' => 'Nominal pencairan minimal Rp 10.000.',
            'campaign_id.required' => 'Pilih kampanye yang dicairkan.',
        ]);

        $campaign = Campaign::findOrFail($validated['campaign_id']);

        // Validasi ketersediaan saldo
        if ($validated['amount'] > $campaign->available_balance) {
            return back()->withInput()->withErrors([
                'amount' => 'Nominal pencairan (Rp ' . number_format($validated['amount'], 0, ',', '.') . 
                            ') melebihi saldo tersedia (Rp ' . number_format($campaign->available_balance, 0, ',', '.') . ').'
            ]);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt_path')) {
            $receiptPath = $request->file('receipt_path')->store('disbursements', 'public');
        }

        Disbursement::create([
            'campaign_id' => $campaign->campaign_id,
            'user_id' => Auth::user()->user_id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'purpose' => $validated['purpose'],
            'receipt_path' => $receiptPath,
            'status' => 'completed',
        ]);

        return redirect()->route('disbursements.index')->with('success', 'Pencairan dana berhasil dicatat!');
    }

    /**
     * Tampilkan detail pencairan dan bukti transfer.
     */
    public function show(string $id)
    {
        $disbursement = Disbursement::with(['campaign', 'user'])->findOrFail($id);

        return view('admin.disbursement.detail', compact('disbursement'));
    }

    /**
     * Hapus riwayat pencairan dana.
     */
    public function destroy(string $id)
    {
        $disbursement = Disbursement::findOrFail($id);

        if ($disbursement->receipt_path && Storage::disk('public')->exists($disbursement->receipt_path)) {
            Storage::disk('public')->delete($disbursement->receipt_path);
        }

        $disbursement->delete();

        return redirect()->route('disbursements.index')->with('success', 'Catatan pencairan berhasil dihapus.');
    }
}
