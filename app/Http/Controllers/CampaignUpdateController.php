<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignUpdateController extends Controller
{
    /**
     * Simpan kabar terbaru untuk suatu kampanye.
     */
    public function store(Request $request, string $campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'title.required' => 'Judul kabar terbaru wajib diisi.',
            'content.required' => 'Isi perkembangan kabar terbaru wajib diisi.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('campaign_updates', 'public');
        }

        CampaignUpdate::create([
            'campaign_id' => $campaign->campaign_id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('campaigns.show', $campaign->campaign_id)
            ->with('success', 'Kabar terbaru berhasil ditambahkan!');
    }

    /**
     * Hapus kabar terbaru.
     */
    public function destroy(string $id)
    {
        $update = CampaignUpdate::findOrFail($id);
        $campaignId = $update->campaign_id;

        if ($update->image_path && Storage::disk('public')->exists($update->image_path)) {
            Storage::disk('public')->delete($update->image_path);
        }

        $update->delete();

        return redirect()->route('campaigns.show', $campaignId)
            ->with('success', 'Kabar terbaru berhasil dihapus.');
    }
}
