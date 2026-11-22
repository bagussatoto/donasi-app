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

    /**
     * Tampilkan laporan Neraca Saldo (Trial Balance).
     */
    public function trialBalance(Request $request)
    {
        $accounts = Account::orderBy('account_code')->get();

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $rows = [];
        $totalMutationDebit = 0;
        $totalMutationCredit = 0;
        $totalEndingDebit = 0;
        $totalEndingCredit = 0;

        foreach ($accounts as $account) {
            $entriesQuery = \App\Models\JournalEntry::where('account_code', $account->account_code)
                ->whereHas('header', function ($q) use ($startDate, $endDate) {
                    if ($startDate) {
                        $q->whereDate('transaction_date', '>=', $startDate);
                    }
                    if ($endDate) {
                        $q->whereDate('transaction_date', '<=', $endDate);
                    }
                });

            $mutationDebit = (int) $entriesQuery->sum('debit');
            $mutationCredit = (int) $entriesQuery->sum('credit');

            $totalMutationDebit += $mutationDebit;
            $totalMutationCredit += $mutationCredit;

            $endingDebit = 0;
            $endingCredit = 0;

            if ($account->normal_balance === 'debit') {
                $net = $mutationDebit - $mutationCredit;
                if ($net >= 0) {
                    $endingDebit = $net;
                } else {
                    $endingCredit = abs($net);
                }
            } else {
                $net = $mutationCredit - $mutationDebit;
                if ($net >= 0) {
                    $endingCredit = $net;
                } else {
                    $endingDebit = abs($net);
                }
            }

            $totalEndingDebit += $endingDebit;
            $totalEndingCredit += $endingCredit;

            $rows[] = [
                'account' => $account,
                'mutation_debit' => $mutationDebit,
                'mutation_credit' => $mutationCredit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
            ];
        }

        $isBalanced = ($totalMutationDebit === $totalMutationCredit) && ($totalEndingDebit === $totalEndingCredit);

        return view('admin.journal.trial_balance', compact(
            'rows',
            'totalMutationDebit',
            'totalMutationCredit',
            'totalEndingDebit',
            'totalEndingCredit',
            'isBalanced',
            'startDate',
            'endDate'
        ));
    }
}
