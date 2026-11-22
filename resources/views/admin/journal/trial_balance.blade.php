@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="fw-bold mb-1 text-dark">Laporan Neraca Saldo (Trial Balance)</h3>
                            <p class="text-muted mb-0 small">Rangkuman mutasi dan saldo akhir seluruh akun buku besar per tanggal pelaporan.</p>
                        </div>
                        <div>
                            @if ($isBalanced)
                                <span class="badge bg-success-subtle text-success px-3 py-2 fs-6 border border-success">
                                    <i class="ti ti-check me-1"></i> STATUS: SEIMBANG (BALANCED)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-6 border border-danger">
                                    <i class="ti ti-alert-triangle me-1"></i> STATUS: TIDAK SEIMBANG
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Navigasi Tab Modul Keuangan -->
                    <ul class="nav nav-pills mb-4 border-bottom pb-2">
                        <li class="nav-item">
                            <a class="nav-link text-muted" href="{{ route('journals.index') }}">
                                <i class="ti ti-list me-1"></i> Buku Jurnal Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" href="{{ route('journals.trial_balance') }}">
                                <i class="ti ti-chart-bar me-1"></i> Neraca Saldo (Trial Balance)
                            </a>
                        </li>
                    </ul>

                    <!-- Filter Rentang Periode -->
                    <form method="GET" action="{{ route('journals.trial_balance') }}" class="row g-2 mb-4 bg-light p-3 rounded align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Awal Transaksi</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal Akhir Transaksi</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="ti ti-filter"></i> Tampilkan Neraca
                            </button>
                            <a href="{{ route('journals.trial_balance') }}" class="btn btn-sm btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </form>

                    <!-- Tabel Neraca Saldo -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary text-dark small fw-bold text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle" style="width: 12%;">Kode Akun</th>
                                    <th rowspan="2" class="align-middle" style="width: 28%;">Nama Akun</th>
                                    <th rowspan="2" class="align-middle" style="width: 12%;">Klasifikasi</th>
                                    <th colspan="2" style="width: 24%;">Total Mutasi (Rp)</th>
                                    <th colspan="2" style="width: 24%;">Saldo Akhir (Rp)</th>
                                </tr>
                                <tr>
                                    <th style="width: 12%;">Debit</th>
                                    <th style="width: 12%;">Kredit</th>
                                    <th style="width: 12%;">Debit</th>
                                    <th style="width: 12%;">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <td class="fw-bold text-center">{{ $row['account']->account_code }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $row['account']->name }}</span>
                                            <div class="small text-muted">{{ $row['account']->description }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark text-capitalize">
                                                {{ $row['account']->type }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace">
                                            {{ $row['mutation_debit'] > 0 ? number_format($row['mutation_debit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end font-monospace">
                                            {{ $row['mutation_credit'] > 0 ? number_format($row['mutation_credit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end fw-semibold text-success font-monospace">
                                            {{ $row['ending_debit'] > 0 ? number_format($row['ending_debit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end fw-semibold text-danger font-monospace">
                                            {{ $row['ending_credit'] > 0 ? number_format($row['ending_credit'], 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-uppercase">TOTAL KESELURUHAN</td>
                                    <td class="text-end font-monospace text-primary">
                                        Rp {{ number_format($totalMutationDebit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-primary">
                                        Rp {{ number_format($totalMutationCredit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-success">
                                        Rp {{ number_format($totalEndingDebit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace text-danger">
                                        Rp {{ number_format($totalEndingCredit, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Ringkasan Eksekutif Keuangan -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted small">Total Kas Penampung Donasi (Akun 1-1001)</div>
                                <div class="fs-5 fw-bold text-success mt-1">
                                    Rp {{ number_format(collect($rows)->firstWhere('account.account_code', '1-1001')['ending_debit'] ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted small">Sisa Dana Donasi Terikat / Titipan (Akun 2-1001)</div>
                                <div class="fs-5 fw-bold text-danger mt-1">
                                    Rp {{ number_format(collect($rows)->firstWhere('account.account_code', '2-1001')['ending_credit'] ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted small">Total Penyaluran Terealisasi (Akun 5-2001)</div>
                                <div class="fs-5 fw-bold text-primary mt-1">
                                    Rp {{ number_format(collect($rows)->firstWhere('account.account_code', '5-2001')['ending_debit'] ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
