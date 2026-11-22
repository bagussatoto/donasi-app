<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalHeader;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Tampilkan daftar jurnal umum (Double-entry accounting).
     */
    public function index(Request $request)
    {
        $query = JournalHeader::with(['entries.account', 'source']);

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', 'like', '%' . $request->source_type . '%');
        }

        $journals = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::orderBy('account_code')->get();

        return view('admin.journal.index', compact('journals', 'accounts'));
    }
}
