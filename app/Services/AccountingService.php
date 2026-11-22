<?php

namespace App\Services;

use App\Models\Disbursement;
use App\Models\Donasi;
use App\Models\JournalEntry;
use App\Models\JournalHeader;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Otomatis catat jurnal saat donasi berstatus completed (Midtrans Settlement).
     *
     * Jurnal:
     * (Debit)  1-1001 Kas & Bank - Penampung Donasi   Rp [nominal]
     *     (Kredit) 2-1001 Dana Donasi Terikat (Titipan)    Rp [nominal]
     */
    public static function recordDonation(Donasi $donasi): ?JournalHeader
    {
        // Hindari pencatatan jurnal ganda untuk donasi yang sama
        $exists = JournalHeader::where('source_type', Donasi::class)
            ->where('source_id', $donasi->donation_id)
            ->exists();

        if ($exists) {
            return null;
        }

        return DB::transaction(function () use ($donasi) {
            $campaignTitle = optional($donasi->campaign)->title ?? 'Kampanye';
            $voucherNumber = 'JV-DON-' . date('Ymd') . '-' . substr($donasi->donation_id, 0, 8);

            $header = JournalHeader::create([
                'voucher_number' => $voucherNumber,
                'transaction_date' => now()->toDateString(),
                'source_type' => Donasi::class,
                'source_id' => $donasi->donation_id,
                'description' => "Penerimaan Donasi: {$campaignTitle}",
            ]);

            // 1. Debit Kas Penampung
            JournalEntry::create([
                'header_id' => $header->header_id,
                'account_code' => '1-1001',
                'debit' => $donasi->amount,
                'credit' => 0,
            ]);

            // 2. Kredit Dana Donasi Terikat
            JournalEntry::create([
                'header_id' => $header->header_id,
                'account_code' => '2-1001',
                'debit' => 0,
                'credit' => $donasi->amount,
            ]);

            return $header;
        });
    }

    /**
     * Otomatis catat jurnal saat dana dicairkan ke penerima manfaat (Disbursement Completed).
     *
     * Jurnal:
     * (Debit)  2-1001 Dana Donasi Terikat (Titipan)   Rp [nominal]
     *     (Kredit) 1-1001 Kas & Bank - Penampung Donasi    Rp [nominal]
     */
    public static function recordDisbursement(Disbursement $disbursement): ?JournalHeader
    {
        $exists = JournalHeader::where('source_type', Disbursement::class)
            ->where('source_id', $disbursement->disbursement_id)
            ->exists();

        if ($exists) {
            return null;
        }

        return DB::transaction(function () use ($disbursement) {
            $campaignTitle = optional($disbursement->campaign)->title ?? 'Kampanye';
            $voucherNumber = 'JV-DISB-' . date('Ymd') . '-' . substr($disbursement->disbursement_id, 0, 8);

            $header = JournalHeader::create([
                'voucher_number' => $voucherNumber,
                'transaction_date' => now()->toDateString(),
                'source_type' => Disbursement::class,
                'source_id' => $disbursement->disbursement_id,
                'description' => "Pencairan Dana: {$campaignTitle} ke {$disbursement->account_name} ({$disbursement->bank_name})",
            ]);

            // 1. Debit Dana Donasi Terikat (Kewajiban amanah berkurang)
            JournalEntry::create([
                'header_id' => $header->header_id,
                'account_code' => '2-1001',
                'debit' => $disbursement->amount,
                'credit' => 0,
            ]);

            // 2. Kredit Kas Bank Penampung (Kas keluar)
            JournalEntry::create([
                'header_id' => $header->header_id,
                'account_code' => '1-1001',
                'debit' => 0,
                'credit' => $disbursement->amount,
            ]);

            return $header;
        });
    }
}
