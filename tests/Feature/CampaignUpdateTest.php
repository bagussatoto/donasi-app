<?php

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user']);
    $this->campaign = Campaign::create([
        'title' => 'Kampanye Update Test',
        'description' => 'Deskripsi',
        'target_amount' => 10000000,
        'end_date' => now()->addDays(10),
        'status' => true,
    ]);
});

it('restricts adding campaign update to admin only', function () {
    $this->post("/admin/campaigns/{$this->campaign->campaign_id}/updates", [
        'title' => 'Kabar Baru',
        'content' => 'Pasien membaik',
    ])->assertRedirect(route('login'));

    $this->actingAs($this->user)
        ->post("/admin/campaigns/{$this->campaign->campaign_id}/updates", [
            'title' => 'Kabar Baru',
            'content' => 'Pasien membaik',
        ])->assertRedirect(route('login'));
});

it('allows admin to publish and delete campaign update', function () {
    $this->actingAs($this->admin)
        ->post("/admin/campaigns/{$this->campaign->campaign_id}/updates", [
            'title' => 'Operasi Berhasil',
            'content' => 'Alhamdulillah operasi telah selesai dengan lancar.',
        ])
        ->assertRedirect(route('campaigns.show', $this->campaign->campaign_id))
        ->assertSessionHas('success');

    $update = CampaignUpdate::first();
    expect($update)->not->toBeNull();
    expect($update->title)->toBe('Operasi Berhasil');
    expect($this->campaign->fresh()->updates)->toHaveCount(1);

    // Hapus update
    $this->actingAs($this->admin)
        ->delete("/admin/campaigns/updates/{$update->update_id}")
        ->assertRedirect(route('campaigns.show', $this->campaign->campaign_id));

    expect(CampaignUpdate::count())->toBe(0);
});
