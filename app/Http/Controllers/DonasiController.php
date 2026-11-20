<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Donasi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonasiController extends Controller
{
    public function index()
    {
        $category = Category::all();
        $campaign = Campaign::withTotalDonations()->get()
            ->each(fn ($item) => $item->decorate());

        return view('index', compact('campaign', 'category'));
    }

    public function Selengkapnya()
    {
        $search = request()->input('q');

        $campaigns = Campaign::withTotalDonations()
            ->when($search, function ($query) use ($search) {
                // Dikelompokkan agar kondisi OR tidak merusak query lain
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('campaign_id', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $campaigns->getCollection()->each(fn ($item) => $item->decorate());

        return view('Pages.Selengkapnya', compact('campaigns', 'search'));
    }

    public function DetailDonasi($slug)
    {
        $campaign = Campaign::withTotalDonations()->where('slug', $slug)->firstOrFail();
        $campaign->decorate();

        $donatur = $campaign->donatur = Donasi::where('campaign_id', $campaign->campaign_id)
            ->where('status', 'completed')
            ->with('user')
            ->paginate(5)
            ->through(function ($donasi) {
                $donasi->days_ago = Carbon::parse($donasi->created_at)->diffInDays(Carbon::now());
                return $donasi;
            });

        $campaigns = Campaign::withTotalDonations()
            ->where('campaign_id', '!=', $campaign->campaign_id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->each(fn ($item) => $item->decorate());

        return view('Pages.DetailDonasi', compact('campaign', 'campaigns', 'donatur'));
    }

    public function TentangKami()
    {
        return view('Pages.TentangKami');
    }

    public function CategoryDonasi($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan.');
        }

        $campaigns = $category->campaigns()->withTotalDonations()->get()
            ->each(fn ($item) => $item->decorate());

        return view('Pages.CategoryDonasi', compact('category', 'campaigns'));
    }

    public function ProfileDonasi()
    {
        $donasi = Donasi::where('user_id', Auth::user()->user_id)->sum('amount');
        return view('Pages.Profile', compact('donasi'));
    }

    public function DetailPayment($id)
    {
        $transaction = Transaksi::with('donation')->with('user')->findOrFail($id);

        // Donasi milik user terdaftar hanya boleh dilihat pemiliknya; donasi tamu harus cocok dengan session
        $ownerId = optional($transaction->donation)->user_id;
        if ($ownerId) {
            if (!Auth::check() || Auth::user()->user_id !== $ownerId) {
                abort(403);
            }
        } elseif (!in_array($transaction->transaction_id, session('guest_transactions', []), true)) {
            abort(403);
        }

        if ($transaction->status == 'completed') {
            return view('Pages.Feedback', compact('transaction'));
        }
        return view('Pages.DetailPayment', compact('transaction'));
    }

    public function HistoryDonasi()
    {
        $donasi = Donasi::where('user_id', Auth::user()->user_id)->with('campaign')->get();
        return view('Pages.HistoryProfile', compact('donasi'));
    }
}
