@extends('layouts.app', ['title' => 'Syarat & Ketentuan - Ayo Donasi'])

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl p-6 lg:p-10 shadow-sm border border-slate-100">
        <h1 class="text-3xl font-bold text-sky-950 mb-2">Syarat & Ketentuan</h1>
        <p class="text-sm text-gray-500 mb-6">Terakhir Diperbarui: 22 November 2026</p>

        <div class="prose prose-slate max-w-none space-y-6 text-gray-700 leading-relaxed text-sm lg:text-base">
            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">1. Ketentuan Umum</h2>
                <p>Selamat datang di platform <strong>Ayo Donasi</strong>. Dengan mengakses, menjelajahi, atau berdonasi di situs web ini, Anda menyatakan setuju untuk terikat oleh Syarat dan Ketentuan yang berlaku. Jika Anda tidak menyetujui ketentuan ini, Anda disarankan untuk tidak menggunakan platform ini.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">2. Akun & Keamanan</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Pengguna wajib memberikan data yang sah dan akurat saat registrasi akun.</li>
                    <li>Pengguna bertanggung jawab penuh atas kerahasiaan kata sandi dan keamanan akun masing-masing.</li>
                    <li>Sistem berhak menangguhkan akun yang terindikasi melakukan aktivitas penipuan, carding, atau penyalahgunaan sistem.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">3. Ketentuan Donasi & Pembayaran</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Nominal donasi minimal adalah <strong>Rp 1.000 (seribu rupiah)</strong>.</li>
                    <li>Seluruh transaksi diproses secara aman dan terenkripsi melalui payment gateway berizin resmi Bank Indonesia (<strong>Midtrans</strong>).</li>
                    <li>Donatur dapat memilih opsi "Anonim" pada formulir donasi jika tidak ingin namanya dipublikasikan pada daftar donatur publik.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">4. Kebijakan Pengembalian Dana (Refund Policy)</h2>
                <p>Donasi yang telah berhasil dibayarkan (status <em>completed</em>) bersifat sukarela dan <strong>tidak dapat dibatalkan atau ditarik kembali secara sepihak oleh donatur</strong>, kecuali terbukti terjadi kegagalan pemrosesan ganda pada sistem gerbang pembayaran atau kampanye terbukti melakukan penipuan sebelum dana disalurkan.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">5. Larangan Konten Kampanye</h2>
                <p>Dilarang keras menggalang dana untuk kepentingan politik praktis, pelunasan utang pinjaman online konsumtif/judi online, pendanaan kegiatan terorisme/radikalisme, serta kampanye palsu/fiktif yang merugikan pihak lain.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">6. Batasan Tanggung Jawab</h2>
                <p>Ayo Donasi bertindak sebagai platform penyedia fasilitas teknologi perantara antara donatur dan penerima manfaat. Kami menjalankan proses verifikasi dokumen secara wajar dan tidak bertanggung jawab atas sengketa yang timbul di luar kendali wajar teknologi sistem.</p>
            </section>
        </div>
    </div>
</div>
@endsection
