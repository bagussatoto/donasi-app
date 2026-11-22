@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary fw-semibold">
                        <iconify-icon icon="solar:hand-money-linear" class="me-1 fs-5 align-middle"></iconify-icon>
                        Form Catat Pencairan Dana
                    </h5>
                </div>
                <div class="card-body">
                    @include('components.alert')

                    <form action="{{ route('disbursements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="campaign_id" class="form-label fw-semibold">Pilih Kampanye Donasi <span class="text-danger">*</span></label>
                            <select name="campaign_id" id="campaign_id" class="form-select @error('campaign_id') is-invalid @enderror" required onchange="updateBalance(this)">
                                <option value="">-- Pilih Kampanye --</option>
                                @foreach ($campaigns as $camp)
                                    <option value="{{ $camp->campaign_id }}" 
                                            data-collected="{{ $camp->collected_amount }}" 
                                            data-disbursed="{{ $camp->total_disbursed }}"
                                            data-available="{{ $camp->available_balance }}"
                                            {{ old('campaign_id') == $camp->campaign_id ? 'selected' : '' }}>
                                        {{ $camp->title }} (Sisa Saldo: Rp {{ number_format($camp->available_balance, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('campaign_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Card Rincian Saldo -->
                        <div class="card bg-light border-0 mb-3" id="balanceCard" style="display: none;">
                            <div class="card-body py-2">
                                <div class="row text-center text-sm">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Terkumpul</small>
                                        <span class="fw-bold text-primary" id="spanCollected">Rp 0</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Telah Dicairkan</small>
                                        <span class="fw-bold text-danger" id="spanDisbursed">Rp 0</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Sisa Saldo</small>
                                        <span class="fw-bold text-success" id="spanAvailable">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Nominal Pencairan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                   placeholder="Contoh: 5000000" min="10000" value="{{ old('amount') }}" required>
                            <small class="text-muted">Nominal tidak boleh melebihi sisa saldo kampanye.</small>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="bank_name" class="form-label fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                                       placeholder="BCA / Mandiri / BRI" value="{{ old('bank_name') }}" required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="account_number" class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" 
                                       placeholder="1234567890" value="{{ old('account_number') }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="account_name" class="form-label fw-semibold">Atas Nama Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="account_name" id="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                                       placeholder="Nama Penerima / RS" value="{{ old('account_name') }}" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="purpose" class="form-label fw-semibold">Keperluan / Tujuan Penyaluran <span class="text-danger">*</span></label>
                            <textarea name="purpose" id="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" 
                                      placeholder="Contoh: Pembayaran biaya rawat inap dan obat tahap 1 di RS Sardjito..." required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="receipt_path" class="form-label fw-semibold">Unggah Bukti Transfer / Resi Bank (Opsional)</label>
                            <input type="file" name="receipt_path" id="receipt_path" class="form-control @error('receipt_path') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
                            @error('receipt_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('disbursements.index') }}" class="btn btn-light rounded-pill px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-check-circle me-1"></i> Simpan Pencairan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function updateBalance(select) {
        const option = select.options[select.selectedIndex];
        const card = document.getElementById('balanceCard');
        if (select.value) {
            const collected = parseInt(option.getAttribute('data-collected') || 0);
            const disbursed = parseInt(option.getAttribute('data-disbursed') || 0);
            const available = parseInt(option.getAttribute('data-available') || 0);

            document.getElementById('spanCollected').innerText = 'Rp ' + collected.toLocaleString('id-ID');
            document.getElementById('spanDisbursed').innerText = 'Rp ' + disbursed.toLocaleString('id-ID');
            document.getElementById('spanAvailable').innerText = 'Rp ' + available.toLocaleString('id-ID');

            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    }

    // Trigger saat load jika ada old value
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('campaign_id');
        if (sel && sel.value) {
            updateBalance(sel);
        }
    });
</script>
@endpush
@endsection
