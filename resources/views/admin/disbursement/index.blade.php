@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-primary fw-semibold">
                <iconify-icon icon="solar:hand-money-linear" class="me-1 fs-5 align-middle"></iconify-icon>
                Daftar Pencairan Dana (Disbursement)
            </h5>
            <a href="{{ route('disbursements.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fas fa-plus me-1"></i> Catat Pencairan Baru
            </a>
        </div>
        <div class="card-body">
            @include('components.alert')

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kampanye</th>
                            <th>Nominal</th>
                            <th>Penerima & Rekening</th>
                            <th>Keperluan</th>
                            <th>Bukti Transfer</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($disbursements as $index => $item)
                            <tr>
                                <td>{{ $disbursements->firstItem() + $index }}</td>
                                <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $item->campaign->title ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success fs-6">
                                        Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $item->account_name }}</strong><br>
                                    <small class="text-muted">{{ $item->bank_name }} - {{ $item->account_number }}</small>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $item->purpose }}">
                                        {{ $item->purpose }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->receipt_path)
                                        <a href="{{ asset('storage/' . $item->receipt_path) }}" data-lightbox="receipt-gallery" data-title="Bukti Transfer {{ $item->account_name }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-image me-1"></i> Lihat Resi
                                        </a>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Tanpa Bukti</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('disbursements.show', $item->disbursement_id) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('disbursements.destroy', $item->disbursement_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pencairan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <iconify-icon icon="solar:folder-error-linear" class="fs-8 mb-2"></iconify-icon><br>
                                    Belum ada catatan pencairan dana.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $disbursements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
