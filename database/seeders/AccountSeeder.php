<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'account_code' => '1-1001',
                'name' => 'Kas & Bank - Penampung Donasi',
                'type' => 'asset',
                'description' => 'Rekening penampungan dana titipan donasi masyarakat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '1-1002',
                'name' => 'Piutang Payment Gateway (Midtrans)',
                'type' => 'asset',
                'description' => 'Saldo settlement donasi di Midtrans yang belum ditarik ke bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '2-1001',
                'name' => 'Dana Donasi Terikat (Titipan Donasi)',
                'type' => 'liability',
                'description' => 'Amanah donasi yang wajib disalurkan ke penerima manfaat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '4-1001',
                'name' => 'Pendapatan Hak Operasional Platform',
                'type' => 'revenue',
                'description' => 'Hak kelola operasional resmi (ujrah) yayasan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '5-2001',
                'name' => 'Penyaluran Donasi Program',
                'type' => 'expense',
                'description' => 'Realisasi pencairan dana ke penerima manfaat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('accounts')->insertOrIgnore($accounts);
    }
}
