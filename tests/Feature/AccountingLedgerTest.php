<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donasi;
use App\Models\JournalEntry;
use App\Models\JournalHeader;
use App\Models\User;
use App\Services\AccountingService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CategorySeeder::class,
            AccountSeeder::class,
        ]);
    }

    public function test_chart_of_accounts_are_seeded(): void
    {
        $this->assertDatabaseHas('accounts', [
            'account_code' => '1-1001',
            'account_name' => 'Kas & Bank - Penampung Donasi',
        ]);

        $this->assertDatabaseHas('accounts', [
            'account_code' => '2-1001',
            'account_name' => 'Dana Donasi Terikat (Titipan Donatur)',
        ]);
    }

    public function test_record_donation_creates_balanced_journal_entries(): void
    {
        $campaign = Campaign::create([
            'title' => 'Bantu Korban Banjir',
            'category_id' => 1,
            'target_amount' => 10000000,
            'collected_amount' => 0,
            'description' => 'Bantuan korban banjir',
            'end_date' => now()->addDays(30),
            'status' => true,
        ]);

        $donation = Donasi::create([
            'campaign_id' => $campaign->campaign_id,
            'amount' => 500000,
            'status' => 'completed',
            'show_name' => true,
        ]);

        $journal = AccountingService::recordDonation($donation);

        $this->assertNotNull($journal);
        $this->assertEquals(Donasi::class, $journal->source_type);
        $this->assertEquals($donation->donation_id, $journal->source_id);

        // Pastikan ada 2 entri (Debit dan Kredit)
        $entries = JournalEntry::where('header_id', $journal->header_id)->get();
        $this->assertCount(2, $entries);

        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');
        $this->assertEquals(500000, $totalDebit);
        $this->assertEquals(500000, $totalCredit);

        // Debit 1-1001 (Kas), Kredit 2-1001 (Dana Donasi Terikat)
        $debitEntry = $entries->where('account_code', '1-1001')->first();
        $creditEntry = $entries->where('account_code', '2-1001')->first();

        $this->assertEquals(500000, $debitEntry->debit);
        $this->assertEquals(0, $debitEntry->credit);

        $this->assertEquals(0, $creditEntry->debit);
        $this->assertEquals(500000, $creditEntry->credit);
    }

    public function test_record_disbursement_creates_balanced_journal_entries(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $campaign = Campaign::create([
            'title' => 'Bantu Korban Banjir',
            'category_id' => 1,
            'target_amount' => 10000000,
            'collected_amount' => 10000000,
            'description' => 'Bantuan korban banjir',
            'end_date' => now()->addDays(30),
            'status' => true,
        ]);

        $disbursement = Disbursement::create([
            'campaign_id' => $campaign->campaign_id,
            'user_id' => $user->user_id,
            'amount' => 2000000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Relawan Lapangan',
            'purpose' => 'Pembelian sembako tahap 1',
            'status' => 'completed',
        ]);

        $journal = AccountingService::recordDisbursement($disbursement);

        $this->assertNotNull($journal);
        $this->assertEquals(Disbursement::class, $journal->source_type);
        $this->assertEquals($disbursement->disbursement_id, $journal->source_id);

        $entries = JournalEntry::where('header_id', $journal->header_id)->get();
        $this->assertCount(2, $entries);

        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');
        $this->assertEquals(2000000, $totalDebit);
        $this->assertEquals(2000000, $totalCredit);

        // Debit 2-1001 (Dana Donasi Terikat), Kredit 1-1001 (Kas Bank)
        $debitEntry = $entries->where('account_code', '2-1001')->first();
        $creditEntry = $entries->where('account_code', '1-1001')->first();

        $this->assertEquals(2000000, $debitEntry->debit);
        $this->assertEquals(0, $debitEntry->credit);

        $this->assertEquals(0, $creditEntry->debit);
        $this->assertEquals(2000000, $creditEntry->credit);
    }

    public function test_admin_can_access_journals_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('journals.index'));
        $response->assertStatus(200);
        $response->assertSee('Jurnal Keuangan Umum');
        $response->assertSee('Bagan Akun Standar');
    }
}
