@extends('layouts.app', ['title' => 'Kebijakan Privasi - Ayo Donasi'])

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl p-6 lg:p-10 shadow-sm border border-slate-100">
        <h1 class="text-3xl font-bold text-sky-950 mb-2">Kebijakan Privasi</h1>
        <p class="text-sm text-gray-500 mb-6">Sesuai Undang-Undang No. 27 Tahun 2022 (UU Pelindungan Data Pribadi / UU PDP)</p>

        <div class="prose prose-slate max-w-none space-y-6 text-gray-700 leading-relaxed text-sm lg:text-base">
            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">1. Komitmen Privasi</h2>
                <p>Platform <strong>Ayo Donasi</strong> menghormati dan menjunjung tinggi pelindungan data pribadi setiap donatur dan pengguna. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan menjaga keamanan data Anda.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">2. Data Pribadi yang Dikumpulkan</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Informasi Akun:</strong> Nama lengkap, alamat email, nomor telepon, dan password terenkripsi.</li>
                    <li><strong>Informasi Transaksi:</strong> Nominal donasi, waktu donasi, pesan doa, dan Order ID Midtrans.</li>
                    <li><strong>Data Teknis:</strong> Alamat IP, jenis peramban (browser), dan rekaman sesi login.</li>
                    <li><em>Catatan Keamanan:</em> Kami <strong>tidak pernah menyimpan nomor kartu kredit/debit atau PIN perbankan Anda</strong>. Semua otorisasi pembayaran kartu ditangani langsung secara aman oleh Midtrans (PCI-DSS bersertifikasi).</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">3. Tujuan Penggunaan Data</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Memproses dan memverifikasi transaksi donasi secara real-time.</li>
                    <li>Mencegah aktivitas mencurigakan dan penipuan (<em>anti-fraud</em>).</li>
                    <li>Menyediakan riwayat donasi pada menu profil pengguna.</li>
                    <li>Menghubungi donatur terkait pembaruan kabar penyaluran donasi atau laporan resmi.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">4. Berbagi Data dengan Pihak Ketiga</h2>
                <p>Kami tidak menjual, menyewakan, atau memperdagangkan data pribadi pengguna kepada pihak ketiga komersial. Data hanya diteruskan kepada penyedia gerbang pembayaran resmi (<strong>Midtrans</strong>) untuk tujuan pemrosesan transaksi yang sah atau jika diwajibkan oleh aparat penegak hukum berdasarkan peraturan perundang-undangan.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-sky-900 mb-2">5. Hak Pemilik Data Pribadi</h2>
                <p>Berdasarkan UU PDP, Anda berhak untuk mengakses informasi data Anda, meminta perbaikan data yang tidak akurat, serta mengajukan permohonan penutupan akun dan penghapusan data dengan menghubungi tim dukungan kami melalui email di <strong>donasi@gmail.com</strong>.</p>
            </section>
        </div>
    </div>
</div>
@endsection
