<?php

use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user']);
});

it('restricts disbursement module to admin only', function () {
    $this->get('/admin/disbursements')->assertRedirect(route('login'));
    $this->actingAs($this->user)->get('/admin/disbursements')->assertRedirect(route('login'));
    $this->actingAs($this->admin)->get('/admin/disbursements')->assertOk();
});

it('rejects disbursement exceeding available balance', function () {
    $campaign = Campaign::create([
        'title' => 'Kampanye Uji Saldo',
        'description' => 'Deskripsi',
        'target_amount' => 10000000,
        'collected_amount' => 5000000,
        'end_date' => now()->addDays(10),
        'status' => true,
    ]);

    $this->actingAs($this->admin)
        ->from('/admin/disbursements/create')
        ->post('/admin/disbursements', [
            'campaign_id' => $campaign->campaign_id,
            'amount' => 6000000, // Melebihi collected_amount 5jt
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'RS Panti Rapih',
            'purpose' => 'Biaya pengobatan',
        ])
        ->assertRedirect('/admin/disbursements/create')
        ->assertSessionHasErrors('amount');

    expect(Disbursement::count())->toBe(0);
});

it('records valid disbursement successfully', function () {
    $campaign = Campaign::create([
        'title' => 'Kampanye Uji Sukses',
        'description' => 'Deskripsi',
        'target_amount' => 10000000,
        'collected_amount' => 5000000,
        'end_date' => now()->addDays(10),
        'status' => true,
    ]);

    $this->actingAs($this->admin)
        ->post('/admin/disbursements', [
            'campaign_id' => $campaign->campaign_id,
            'amount' => 2000000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'RS Panti Rapih',
            'purpose' => 'Biaya operasi tahap 1',
        ])
        ->assertRedirect(route('disbursements.index'))
        ->assertSessionHas('success');

    $disbursement = Disbursement::first();
    expect($disbursement)->not->toBeNull();
    expect((int) $disbursement->amount)->toBe(2000000);
    expect($campaign->fresh()->total_disbursed)->toBe(2000000);
    expect($campaign->fresh()->available_balance)->toBe(3000000);
});
