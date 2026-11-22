@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="fw-bold mb-1 text-dark">Jurnal Keuangan Umum</h3>
                            <p class="text-muted mb-0 small">Buku besar pencatatan berpasangan (Double-Entry Ledger: Akun, Header, & Entri Jurnal).</p>
                        </div>
                    </div>

                    <!-- Navigasi Tab Modul Keuangan -->
                    <ul class="nav nav-pills mb-4 border-bottom pb-2">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" href="{{ route('journals.index') }}">
                                <i class="ti ti-list me-1"></i> Buku Jurnal Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('journals.trial_balance') }}">
                                <i class="ti ti-chart-bar me-1"></i> Neraca Saldo (Trial Balance)
                            </a>
                        </li>
                    </ul>

                    <form method="GET" action="{{ route('journals.index') }}" class="row g-2 mb-4 bg-light p-3 rounded">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tanggal Sampai</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Sumber Transaksi</label>
                            <select name="source_type" class="form-select form-select-sm">
                                <option value="">Semua Sumber (Donasi & Pencairan)</option>
                                <option value="Donasi" {{ request('source_type') == 'Donasi' ? 'selected' : '' }}>Donasi Masuk</option>
                                <option value="Disbursement" {{ request('source_type') == 'Disbursement' ? 'selected' : '' }}>Pencairan Dana</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-primary text-dark small fw-bold">
                                <tr>
                                    <th style="width: 14%;">No. Voucher & Tgl</th>
                                    <th style="width: 32%;">Keterangan & Sumber</th>
                                    <th style="width: 26%;">Kode Akun & Nama Akun</th>
                                    <th style="width: 14%;" class="text-end">Debit (Rp)</th>
                                    <th style="width: 14%;" class="text-end">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($journals as $journal)
                                    @php
                                        $entryCount = $journal->entries->count();
                                    @endphp
                                    @foreach ($journal->entries as $index => $entry)
                                        <tr class="{{ $loop->parent->even ? 'bg-light' : '' }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ $entryCount }}" class="align-top bg-white">
                                                    <span class="badge bg-secondary mb-1">{{ $journal->voucher_number }}</span>
                                                    <div class="small text-muted">{{ \Carbon\Carbon::parse($journal->transaction_date)->format('d M Y') }}</div>
                                                </td>
                                                <td rowspan="{{ $entryCount }}" class="align-top bg-white">
                                                    <div class="fw-semibold text-dark">{{ $journal->description }}</div>
                                                    <div class="small text-muted mt-1">
                                                        Sumber: 
                                                        <span class="badge bg-info-subtle text-info">
                                                            {{ class_basename($journal->source_type) }} #{{ substr($journal->source_id, 0, 8) }}
                                                        </span>
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="fw-semibold">{{ $entry->account_code }}</span> - {{ optional($entry->account)->account_name ?? 'Akun Tidak Ditemukan' }}
                                                @if ($entry->credit > 0)
                                                    <span class="text-muted ms-2 small"><em>(Kredit)</em></span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-success">
                                                {{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end fw-semibold text-danger">
                                                {{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', '.') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan jurnal keuangan yang terdata.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $journals->links() }}
                    </div>
                </div>
            </div>

            <!-- Daftar Chart of Accounts (COA) -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Bagan Akun Standar (Chart of Accounts / COA)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Tipe Akun</th>
                                    <th>Saldo Normal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $acc)
                                    <tr>
                                        <td class="fw-bold">{{ $acc->account_code }}</td>
                                        <td>{{ $acc->account_name }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($acc->account_type) }}</span></td>
                                        <td><span class="badge {{ $acc->normal_balance === 'debit' ? 'bg-success' : 'bg-primary' }}">{{ strtoupper($acc->normal_balance) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
