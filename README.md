<div align="center">

# Ayo Donasi

Platform penggalangan dana dan donasi online berbasis web yang memudahkan masyarakat berdonasi secara cepat, aman, dan transparan, dengan verifikasi pembayaran otomatis (Midtrans Gateway), modul pencairan dana (*disbursement*), sistem kabar perkembangan, serta dashboard analitik administrator.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Midtrans](https://img.shields.io/badge/Midtrans-Payment_Gateway-002855?style=for-the-badge)
![Pest Testing](https://img.shields.io/badge/Testing-Pest_PHP-f43f5e?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

</div>

---

## Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Skema Database](#skema-database)
- [Pratinjau Aplikasi](#pratinjau-aplikasi)
- [Panduan Instalasi](#panduan-instalasi)
- [Konfigurasi Midtrans](#konfigurasi-midtrans)
- [Dokumentasi Lengkap Proyek](#dokumentasi-lengkap-proyek)
- [Struktur Direktori](#struktur-direktori)
- [Pembuat](#pembuat)
- [Lisensi](#lisensi)

---

## Tentang Proyek

**Ayo Donasi** adalah aplikasi web penggalangan dana (*crowdfunding*) yang dibangun menggunakan **Laravel 11** untuk menjembatani donatur dengan berbagai kampanye sosial, medis, dan kemanusiaan secara transparan dan akuntabel.

Platform ini dirancang untuk menyelesaikan kebutuhan inti dalam ekosistem filantropi digital:
- **Kemudahan & Kepercayaan Donatur**: Mendukung donasi cepat tanpa wajib login (*guest donation*) maupun login, opsi donasi anonim, pesan doa, kabar perkembangan berkala (*campaign updates*), serta verifikasi pembayaran instan via **Midtrans Snap API**.
- **Efisiensi & Transparansi Pengelolaan**: Menyediakan panel admin terintegrasi untuk kurasi kampanye, pemantauan analitik Chart.js, serta pencatatan audit **pencairan dana (*disbursement*)** ke rekening penerima manfaat secara terverifikasi.
- **Kepatuhan Regulasi & Industri**: Dilengkapi dengan klausul Syarat & Ketentuan, Kebijakan Privasi sesuai UU PDP No. 27/2022, SOP mitigasi *fraud*, dan pedoman pencatatan akuntansi donasi.

---

## Fitur Utama

### Pengguna & Donatur

- **Eksplorasi & Pencarian Kampanye**: Menjelajahi katalog kampanye aktif, filter per kategori (`/category/{id}`), dan pencarian terpaginasi bebas N+1 query (`/selengkapnya`).
- **Detail Kampanye & Transparansi**: Menampilkan rincian target dana, dana terkumpul, progres bar real-time, sisa hari, dan daftar donatur terverifikasi.
- **Kabar Terbaru & Perkembangan**: Donatur dapat membaca kronologi kabar perkembangan penerima manfaat (*campaign updates*) lengkap dengan foto dokumentasi.
- **Donasi Fleksibel (Guest & Terdaftar)**: Pengguna dapat berdonasi tanpa login atau dengan akun terdaftar, dilengkapi opsi menyembunyikan nama (*anonim*).
- **Pembayaran Terintegrasi Midtrans**: Integrasi Midtrans Snap (QRIS, Virtual Account, E-Wallet) dengan verifikasi notifikasi webhook otomatis (`SHA-512`).
- **Profil & Riwayat Donasi**: Pengguna terdaftar dapat memantau total donasi yang telah disalurkan (`/profile`) dan riwayat transaksinya (`/donasi/history`).
- **Legalitas & Kepercayaan**: Tersedia halaman publik resmi untuk [Syarat & Ketentuan](/syarat-ketentuan) dan [Kebijakan Privasi](/kebijakan-privasi).

### Panel Administrator

> Seluruh rute di bawah prefix `/admin` dilindungi oleh middleware `admin`.

- **Dashboard Statistik & Analitik**: Menampilkan ringkasan total kampanye, total donatur terverifikasi, jumlah kategori, dan grafik capaian kampanye berbasis **Chart.js** via endpoint `/admin/statistics`.
- **Manajemen Kategori (CRUD)**: Pengelolaan kategori donasi lengkap dengan deskripsi dan pembersihan otomatis file ikon lama di storage saat diupdate/dihapus (`/admin/categories`).
- **Manajemen Kampanye (CRUD)**: Pengelolaan kampanye donasi, slug otomatis, upload foto, dan switch status aktif/nonaktif (`/admin/campaigns`).
- **Kabar Terbaru Kampanye (Updates)**: Admin dapat menambahkan berita perkembangan kondisi penerima manfaat dan foto bukti dari halaman detail kampanye.
- **Modul Pencairan Dana (Disbursements)**: Mencatat pengajuan dan penyaluran dana ke rekening bank penerima manfaat, validasi otomatis batas saldo tersedia (`available_balance`), serta upload bukti resi transfer (`/admin/disbursements`).
- **Sistem Pembukuan Berpasangan (Double-Entry Accounting Ledger)**: Pencatatan jurnal otomatis saat donasi masuk (Debit Kas Penampung, Kredit Dana Donasi Terikat) dan pencairan dana (Debit Dana Donasi Terikat, Kredit Kas Penampung) dengan relasi polimorfik ke `donations` dan `disbursements` (`/admin/journals`).
- **Laporan Neraca Saldo (Trial Balance)**: Rekapitulasi mutasi debit/kredit dan saldo akhir seluruh akun buku besar, deteksi otomatis status keseimbangan (*balanced*), serta filter periode pelaporan (`/admin/trial-balance`).
- **Manajemen Pengguna**: Pencarian, paginasi data pengguna, dan proteksi agar akun bertipe `admin` tidak dapat dihapus (`/admin/userman`).

---

## Teknologi yang Digunakan

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend** | Laravel 11 (`^11.31`), PHP `^8.2`, `fahlisaputra/laravel-minify` |
| **Database** | MySQL (dengan Primary Key berbasis UUID) |
| **Frontend (Pengguna)** | Blade Templates, Tailwind CSS `^3.4`, DaisyUI `^4.12`, Vite `^6.0`, Splide, Toastify |
| **Frontend (Admin)** | Bootstrap, Chart.js `^4.4`, SweetAlert2 `^11.15`, Lightbox2 `^2.11`, Solar Iconify |
| **Payment Gateway** | Midtrans (`midtrans/midtrans-php` `^2.6` — Snap API & Webhook Callback) |
| **Testing & CI** | Pest PHP `^3.7`, SQLite In-Memory, GitHub Actions CI Workflow |

---

## Skema Database

![Schema Database](https://i.imgur.com/0a53VSs.png)

### Ringkasan Tabel Utama

| Tabel | Primary Key | Kolom & Relasi Kunci |
| :--- | :--- | :--- |
| `users` | `user_id` (UUID) | `name`, `email` (unique), `phone`, `password`, `role` (`user`, `admin`) |
| `categories` | `category_id` (UUID) | `name`, `description`, `icon` |
| `campaigns` | `campaign_id` (UUID) | `category_id` (FK -> `categories`), `slug` (unique), `title`, `description`, `target_amount`, `collected_amount`, `start_date`, `end_date`, `image_path`, `status` |
| `campaign_updates` | `update_id` (UUID) | `campaign_id` (FK -> `campaigns`), `title`, `content`, `image_path` |
| `donations` | `donation_id` (UUID) | `user_id` (FK -> `users`, nullable), `campaign_id` (FK -> `campaigns`), `amount`, `message`, `show_name`, `status` (`pending`, `failed`, `completed`) |
| `transactions` | `transaction_id` (UUID) | `donation_id` (FK -> `donations`), `midtrans_order_id` (unique), `gross_amount`, `snap_token`, `status` (`pending`, `failed`, `completed`) |
| `disbursements` | `disbursement_id` (UUID) | `campaign_id` (FK -> `campaigns`), `user_id` (FK -> `users`), `amount`, `bank_name`, `account_number`, `account_name`, `purpose`, `receipt_path`, `status` |

---

## Pratinjau Aplikasi

| Tampilan 1 | Tampilan 2 |
| :---: | :---: |
| ![Website Screenshot 1](https://i.imgur.com/jS5BGo0.jpeg) | ![Website Screenshot 2](https://i.imgur.com/k9HIUwX.jpeg) |
| **Tampilan 3** | **Tampilan 4** |
| ![Website Screenshot 3](https://i.imgur.com/Wbtpnkm.jpeg) | ![Website Screenshot 4](https://i.imgur.com/7l7nFYO.jpeg) |
| **Tampilan 5** | **Tampilan 6** |
| ![Website Screenshot 5](https://i.imgur.com/BFfn4lh.jpeg) | ![Website Screenshot 6](https://i.imgur.com/6SMU1RB.jpeg) |
| **Tampilan 7** | |
| ![Website Screenshot 7](https://i.imgur.com/PGXaaUW.jpeg) | |

---

## Panduan Instalasi

### Prasyarat

- **PHP** >= 8.2 beserta ekstensi (`mbstring`, `openssl`, `pdo_mysql`, `curl`, dll.)
- **Composer**
- **Node.js & npm**
- **MySQL**
- Akun **Midtrans** (Sandbox atau Production)

### Langkah-langkah

1. **Clone repositori**
   ```bash
   git clone https://github.com/bagussatoto/donasi-app.git
   cd donasi-app
   ```

2. **Instal dependensi backend dan frontend**
   ```bash
   composer install
   npm install
   ```

3. **Salin file environment dan buat application key**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Di Windows Command Prompt/PowerShell: `copy .env.example .env`)*

4. **Konfigurasi database dan Midtrans di `.env`**
   Sesuaikan koneksi database MySQL (`DB_DATABASE=donasi`, `DB_USERNAME`, `DB_PASSWORD`) dan kunci API Midtrans.

5. **Jalankan migrasi database dan buat symbolic link storage**
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

6. **(Opsional) Isi data awal menggunakan Database Seeder**
   ```bash
   php artisan db:seed
   ```
   > **Catatan**: `DatabaseSeeder` otomatis menjalankan `UserSeeder`, `CategorySeeder`, dan `CampaignSeeder` secara berurutan. Akun admin bawaan dibuat dari `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env` (default: `admin@example.com` / `ChangeMe123!`).

7. **Jalankan pengujian otomatis (Pest Testing)**
   ```bash
   ./vendor/bin/pest
   ```

8. **Jalankan server pengembangan**
   ```bash
   composer run dev
   ```
   *(Atau jalankan terpisah: `php artisan serve` dan `npm run dev`)*

---

## Konfigurasi Midtrans

Tambahkan variabel berikut ke dalam file `.env`:

```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
MIDTRANS_CALLBACK_URL=https://your-public-domain/donasi/payment/callback
```

### Alur Webhook & Keamanan Callback

1. Saat donatur menekan donasi (`POST /donasi/payment`), aplikasi membuat record `donations` dan `transactions`, lalu menerbitkan Snap Token via Midtrans API.
2. Saat pembayaran lunas, Midtrans mengirimkan notifikasi HTTP ke endpoint `POST /donasi/payment/callback`.
3. `TransaksiController::handleCallback` memvalidasi keaslian signature key (`SHA-512`), mencocokkan nominal `gross_amount`, memperbarui status transaksi menjadi `completed`, serta menghitung ulang `collected_amount` kampanye.
4. Sistem juga menangani skenario refund/chargeback secara aman dengan membatalkan status transaksi dan mengurangi akumulasi donasi kampanye.

---

## Dokumentasi Lengkap Proyek

Seluruh dokumentasi teknis, perancangan, hukum, dan tata kelola platform tersedia di direktori [`docs/`](./docs):

- 📘 [**Alur Proyek & Arsitektur Lengkap**](./docs/ALUR_PROYEK.md): Menjelaskan arsitektur detail sistem, pipeline middleware, alur webhook, dan peta rute.
- 🗄️ [**Bagan Diagram Database (ERD)**](./docs/DIAGRAM_DATABASE.md): Diagram ERD Mermaid dan kamus data (*Data Dictionary*) seluruh tabel database.
- 📐 [**Use Case Diagram & Skenario UML**](./docs/USE_CASE_DIAGRAM_DAN_SKENARIO.md): Diagram use case UML, identifikasi 4 aktor, matriks hak akses, dan narasi skenario use case.
- 🎨 [**Wireframe & Spesifikasi Desain UI/UX**](./docs/WIREFRAME_DAN_UI_UX_DESIGN.md): Cetak biru wireframe ASCII, hierarki visual, user flow, dan panduan prototipe Figma.
- ⚖️ [**Saran Badan Hukum & Kepatuhan Legal**](./docs/SARAN_RELASI_BADAN_HUKUM.md): Panduan perizinan PUB Kemensos, bentuk yayasan, dan usulan skema kepatuhan hukum.
- 🛡️ [**SOP Verifikasi & Mitigasi Fraud**](./docs/SOP_VERIFIKASI_DAN_MITIGASI_FRAUD.md): Standar prosedur KYC/KYB, kurasi kampanye, anti-fraud payment gateway, dan tata kelola pencairan.
- 📊 [**Pedoman Jurnal Keuangan & Akuntansi Donasi**](./docs/PEDOMAN_JURNAL_KEUANGAN_DONASI.md): Standar pembukuan dana titipan PSAK 109, ilustrasi jurnal debit/kredit, dan laporan LPJ finansial.
- 📜 [**Syarat Ketentuan & Kebijakan Privasi**](./docs/SYARAT_KETENTUAN_DAN_PRIVASI.md): Naskah hukum perlindungan data UU PDP No. 27/2022 dan kepatuhan Midtrans Production.

---

## Struktur Direktori

```text
donasi/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Auth, Campaign, Category, Dashboard, Donasi, Transaksi, Disbursement, CampaignUpdate, Userman
│   │   └── Middleware/       # isAdmin middleware
│   ├── Models/               # Campaign, CampaignUpdate, Category, Disbursement, Donasi, Transaksi, User
│   └── View/Components/      # ModalPayment, ModalShare, Navbar
├── config/
│   └── midtrans.php          # Konfigurasi kunci & callback Midtrans
├── database/
│   ├── migrations/           # Skema tabel users, categories, campaigns, updates, donations, transactions, disbursements
│   └── seeders/              # UserSeeder, CategorySeeder, CampaignSeeder, DatabaseSeeder
├── docs/                     # Dokumentasi arsitektur, ERD, Use Case, UI/UX, SOP, Jurnal, dan Legalitas
├── public/                   # Aset publik, gambar statis, placeholder.svg, dan hasil build Vite
├── resources/
│   ├── css/                  # Entrypoint Tailwind CSS
│   ├── js/                   # Entrypoint JS & Slider
│   └── views/
│       ├── admin/            # Dashboard, manajemen campaign, category, disbursement, dan user
│       ├── auth/             # Halaman login & register (lengkap dengan validasi phone & confirmation)
│       ├── components/       # Komponen UI reusable (modal, navbar, alert)
│       ├── layouts/          # Layout utama pengguna (app) & admin
│       └── Pages/            # Halaman detail donasi, payment, riwayat, profil, kategori, syarat ketentuan, privasi
├── routes/
│   └── web.php               # Definisi seluruh rute aplikasi
└── tests/
    └── Feature/              # Pengujian Pest: AdminAccess, Auth, CampaignUpdate, Disbursement, DonationFlow
```

---

## Pembuat

<div align="center">

**Bagus Budi Satoto**

*Web Developer*

[![GitHub](https://img.shields.io/badge/GitHub-bagussatoto-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/bagussatoto)

</div>

---

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
