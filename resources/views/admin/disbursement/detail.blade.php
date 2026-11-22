@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-semibold">Rincian Pencairan Dana</h5>
                    <a href="{{ route('disbursements.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">ID Pencairan:</div>
                        <div class="col-sm-8 font-monospace">{{ $disbursement->disbursement_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Kampanye:</div>
                        <div class="col-sm-8 fw-semibold">{{ $disbursement->campaign->title ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nominal Pencairan:</div>
                        <div class="col-sm-8">
                            <span class="fs-5 fw-bold text-success">
                                Rp {{ number_format($disbursement->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Rekening Penerima:</div>
                        <div class="col-sm-8">
                            <strong>{{ $disbursement->account_name }}</strong><br>
                            {{ $disbursement->bank_name }} - {{ $disbursement->account_number }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Tujuan Penyaluran:</div>
                        <div class="col-sm-8">{{ $disbursement->purpose }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Dicatat Oleh:</div>
                        <div class="col-sm-8">{{ $disbursement->user->name ?? 'Admin' }} ({{ $disbursement->created_at->format('d F Y, H:i') }})</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Bukti Transfer:</div>
                        <div class="col-sm-8">
                            @if ($disbursement->receipt_path)
                                <img src="{{ asset('storage/' . $disbursement->receipt_path) }}" 
                                     alt="Bukti Transfer" class="img-fluid rounded border shadow-sm" style="max-height: 350px;">
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Tidak ada bukti gambar diunggah.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
