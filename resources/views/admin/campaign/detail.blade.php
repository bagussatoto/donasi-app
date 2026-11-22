@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card p-2 p-lg-5">
                <h1 class="fw-bold fs-5 ">{{ $campaign->title }}</h1>
                <div class="row">
                    <!-- Main Section -->
                    <div class="col-lg-7 px-3">
                        <img src="{{ $campaign->image_url }}" alt="campaign-image"
                            class="rounded img-fluid w-100">
                        <div class="mt-4">
                            <h5 class="fw-bold">Deskripsi</h5>
                            <p>{{ $campaign->description }}</p>
                        </div>
                        <!-- Mobile Rincian Dana -->
                        <div class="bg-light p-3 shadow mt-4 d-lg-none rounded">
                            <h1 class="fw-semibold fs-5 mt-3">{{ $campaign->title }}</h1>
                            <h6 class="mt-2">{{ $campaign->created_at }}</h6>
                            <div class="d-flex gap-3 mt-3">
                                <h6><i class="fas fa-user me-1"></i> {{ $campaign->total_donations }} Donatur</h6>
                                <h6><i class="fas fa-clock me-1"></i> {{ $campaign->days_remaining }} Hari Lagi</h6>
                            </div>
                            <div class="progress mt-3" style="height: 1.5rem;">
                                <div class="progress-bar progress-bar-striped bg-info" role="progressbar"
                                    style="width: {{ $campaign->progress }}%;" aria-valuenow="{{ $campaign->progress }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    {{ round($campaign->progress, 2) }}%
                                </div>
                            </div>
                            <h2 class="mt-3 fw-bold">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}
                                <span class="fs-6 fw-medium">Terkumpul</span>
                            </h2>

                            <div class="mt-4">
                                <h5 class="fw-bold">Deskripsi</h5>
                                <p>{{ $campaign->description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Section -->
                    <div class="d-none d-lg-block col-lg-5 rounded bg-light p-4 shadow">
                        <h4 class="fw-semibold text-center">Rincian Penggalangan Dana</h4>
                        <h6 class="mt-4">Donasi Terkumpul</h6>
                        <h2 class="fw-bold text-primary mt-2">Rp
                            {{ number_format($campaign->collected_amount, 0, ',', '.') }}</h2>
                        <h6 class="fw-bold text-end"><i class="fas fa-clock me-1"></i>{{ $campaign->days_remaining }} Hari
                            Lagi</h6>
                        <div class="progress mt-3" style="height: 1.5rem;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $campaign->progress }}%;"
                                aria-valuenow="{{ $campaign->progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ round($campaign->progress, 2) }}%
                            </div>
                        </div>
                        <h5 class="fw-semibold mt-5">Donatur:</h5>
                        @forelse ($donatur->slice(0, 5) as $data)
                            <div class="d-flex gap-3 mt-3">
                                <i class="fas fa-user fs-4"></i>
                                <div>
                                    <h6>Rp {{ number_format($data->amount, 0, ',', '.') }}</h6>
                                    <p class="text-muted mb-0">Oleh: {{ $data->name ?? 'Anonimus' }} ·
                                        {{ number_format($data->days_ago, 0, ',', '.') == 0 ? 'Hari ini' : number_format($data->days_ago, 0, ',', '.') . ' Hari Yang Lalu' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">Belum ada donatur</p>
                        @endforelse
                    </div>
                </div>
                <div class="row mt-4">
                    <!-- Bagian Kabar Terbaru / Campaign Updates -->
                    <div class="col-lg-7">
                        <div class="card border shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-bullhorn me-2"></i>Kabar Terbaru & Perkembangan</h5>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addUpdateModal">
                                    <i class="fas fa-plus me-1"></i> Tambah Kabar
                                </button>
                            </div>
                            <div class="card-body">
                                @forelse ($campaign->updates as $update)
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $update->title }}</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i>{{ $update->created_at->format('d M Y, H:i') }}</small>
                                                <form action="{{ route('campaigns.updates.destroy', $update->update_id) }}" method="POST" onsubmit="return confirm('Hapus kabar terbaru ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus"><i class="fas fa-times"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="mb-2 text-secondary">{{ $update->content }}</p>
                                        @if ($update->image_path)
                                            <a href="{{ asset('storage/' . $update->image_path) }}" data-lightbox="update-gallery">
                                                <img src="{{ asset('storage/' . $update->image_path) }}" alt="Foto Update" class="img-thumbnail rounded" style="max-height: 140px;">
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted text-center py-3 mb-0">Belum ada kabar terbaru yang dipublikasikan untuk kampanye ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tambah Kabar Terbaru -->
                <div class="modal fade" id="addUpdateModal" tabindex="-1" aria-labelledby="addUpdateModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('campaigns.updates.store', $campaign->campaign_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="addUpdateModalLabel">Tambah Kabar Terbaru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label fw-semibold">Judul Kabar <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Operasi Tahap 1 Selesai Dijalankan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="content" class="form-label fw-semibold">Rincian Perkembangan <span class="text-danger">*</span></label>
                                        <textarea name="content" id="content" rows="4" class="form-control" placeholder="Tuliskan kondisi terbaru penerima manfaat..." required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image_path" class="form-label fw-semibold">Foto Dokumentasi (Opsional)</label>
                                        <input type="file" name="image_path" id="image_path" class="form-control" accept="image/jpeg,image/png,image/jpg">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Publikasikan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <a href="{{ route('campaigns.index') }}" class="btn btn-warning">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection
