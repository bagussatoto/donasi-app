<?php

use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Transaksi;
use App\Models\User;

function makeCampaign(array $overrides = []): Campaign
{
    return Campaign::create(array_merge([
        'title' => 'Kampanye Uji',
        'description' => 'Deskripsi',
        'target_amount' => 1000000,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(10),
        'status' => true,
    ], $overrides));
}

function makePendingTransaction(Campaign $campaign, int $amount = 50000, ?string $userId = null): Transaksi
{
    $donation = Donasi::create([
        'user_id' => $userId,
        'campaign_id' => $campaign->campaign_id,
        'amount' => $amount,
        'show_name' => 1,
    ]);

    return Transaksi::create([
        'donation_id' => $donation->donation_id,
        'midtrans_order_id' => 'ORD-TEST-' . uniqid(),
        'gross_amount' => $amount,
    ]);
}

function callbackPayload(Transaksi $transaksi, string $grossAmount, string $status = 'settlement'): array
{
    $serverKey = config('midtrans.server_key');

    return [
        'order_id' => $transaksi->midtrans_order_id,
        'status_code' => '200',
        'gross_amount' => $grossAmount,
        'transaction_status' => $status,
        'signature_key' => hash('sha512', $transaksi->midtrans_order_id . '200' . $grossAmount . $serverKey),
    ];
}

beforeEach(function () {
    config(['midtrans.server_key' => 'test-server-key']);
});

it('rejects donations to inactive campaigns', function () {
    $campaign = makeCampaign(['status' => false]);

    $this->from('/')->post('/donasi/payment', [
        'campaign_id' => $campaign->campaign_id,
        'amount' => 50000,
    ])->assertRedirect('/')->assertSessionHas('error');

    expect(Donasi::count())->toBe(0);
});

it('rejects donations to expired campaigns', function () {
    $campaign = makeCampaign(['end_date' => now()->subDays(2)]);

    $this->from('/')->post('/donasi/payment', [
        'campaign_id' => $campaign->campaign_id,
        'amount' => 50000,
    ])->assertRedirect('/')->assertSessionHas('error');

    expect(Donasi::count())->toBe(0);
});

it('rejects donations below the minimum amount', function () {
    $campaign = makeCampaign();

    $this->post('/donasi/payment', [
        'campaign_id' => $campaign->campaign_id,
        'amount' => 500,
    ])->assertSessionHasErrors('amount');
});

it('rejects callbacks with an invalid signature', function () {
    $transaksi = makePendingTransaction(makeCampaign());

    $payload = callbackPayload($transaksi, '50000.00');
    $payload['signature_key'] = 'salah';

    $this->postJson('/donasi/payment/callback', $payload)->assertStatus(403);
});

it('marks a transaction completed and updates the collected amount', function () {
    $campaign = makeCampaign();
    $transaksi = makePendingTransaction($campaign, 50000);

    $this->postJson('/donasi/payment/callback', callbackPayload($transaksi, '50000.00'))->assertOk();

    expect($transaksi->fresh()->status)->toBe('completed');
    expect((int) $campaign->fresh()->collected_amount)->toBe(50000);
});

it('rejects callbacks with a mismatched amount', function () {
    $transaksi = makePendingTransaction(makeCampaign(), 50000);

    $this->postJson('/donasi/payment/callback', callbackPayload($transaksi, '10000.00'))->assertStatus(400);

    expect($transaksi->fresh()->status)->toBe('pending');
});

it('ignores callbacks for already completed transactions', function () {
    $transaksi = makePendingTransaction(makeCampaign(), 50000);
    $transaksi->update(['status' => 'completed']);

    $this->postJson('/donasi/payment/callback', callbackPayload($transaksi, '50000.00', 'expire'))->assertOk();

    expect($transaksi->fresh()->status)->toBe('completed');
});

it('marks completed transaction failed and recalculates collected amount on refund', function () {
    $campaign = makeCampaign(['collected_amount' => 50000]);
    $transaksi = makePendingTransaction($campaign, 50000);
    $transaksi->update(['status' => 'completed']);
    $transaksi->donation->update(['status' => 'completed']);

    $this->postJson('/donasi/payment/callback', callbackPayload($transaksi, '50000.00', 'refund'))->assertOk();

    expect($transaksi->fresh()->status)->toBe('failed');
    expect($transaksi->donation->fresh()->status)->toBe('failed');
    expect((int) $campaign->fresh()->collected_amount)->toBe(0);
});

it('marks completed transaction failed and recalculates collected amount on chargeback', function () {
    $campaign = makeCampaign(['collected_amount' => 50000]);
    $transaksi = makePendingTransaction($campaign, 50000);
    $transaksi->update(['status' => 'completed']);
    $transaksi->donation->update(['status' => 'completed']);

    $this->postJson('/donasi/payment/callback', callbackPayload($transaksi, '50000.00', 'chargeback'))->assertOk();

    expect($transaksi->fresh()->status)->toBe('failed');
    expect($transaksi->donation->fresh()->status)->toBe('failed');
    expect((int) $campaign->fresh()->collected_amount)->toBe(0);
});

it('restricts guest payment details to matching session', function () {
    $transaksi = makePendingTransaction(makeCampaign(), 50000);

    $this->get('/donasi/payment/' . $transaksi->transaction_id)->assertStatus(403);

    $this->withSession(['guest_transactions' => [$transaksi->transaction_id]])
        ->get('/donasi/payment/' . $transaksi->transaction_id)
        ->assertOk();
});

it('restricts registered user payment details to the owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $transaksi = makePendingTransaction(makeCampaign(), 50000, $owner->user_id);

    $this->get('/donasi/payment/' . $transaksi->transaction_id)->assertStatus(403);
    $this->actingAs($otherUser)->get('/donasi/payment/' . $transaksi->transaction_id)->assertStatus(403);
    $this->actingAs($owner)->get('/donasi/payment/' . $transaksi->transaction_id)->assertOk();
});
