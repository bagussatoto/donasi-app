<div align="center">

# Ayo Donasi

Platform penggalangan dana dan donasi online berbasis web yang memudahkan masyarakat berdonasi secara cepat, aman, dan transparan, dengan verifikasi pembayaran otomatis serta dashboard analitik untuk administrator.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Midtrans](https://img.shields.io/badge/Midtrans-Payment_Gateway-002855?style=for-the-badge)
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
- [Struktur Direktori](#struktur-direktori)
- [Pembuat](#pembuat)
- [Lisensi](#lisensi)

---

## Tentang Proyek

**Ayo Donasi** adalah aplikasi web penggalangan dana (*crowdfunding*) yang dibangun menggunakan Laravel 11 untuk menjembatani donatur dengan berbagai kampanye sosial dan kemanusiaan secara transparan.

Platform ini dirancang untuk menyelesaikan dua kebutuhan utama dalam pengelolaan donasi digital:
- **Kemudahan & Kepercayaan Donatur**: Mendukung donasi cepat baik sebagai tamu (*guest*) maupun pengguna terdaftar, lengkap dengan pilihan donasi anonim, pesan/doa, pelacakan progres kampanye secara langsung, dan pembayaran otomatis melalui **Midtrans Snap**.
- **Efisiensi Pengelolaan Admin**: Menyediakan panel administrator terintegrasi untuk mengelola kategori, kampanye, data pengguna, serta memantau metrik dan grafik progres penghimpunan dana.

---

## Fitur Utama

### Pengguna & Donatur

- **Eksplorasi & Pencarian Kampanye**: Menjelajahi daftar kampanye aktif, menyaring kampanye berdasarkan kategori (`/category/{id}`), dan mencari kampanye melalui halaman katalog (`/selengkapnya`).
- **Detail Kampanye & Transparansi**: Menampilkan informasi target dana, dana terkumpul, persentase progres, sisa hari kampanye, daftar donatur beserta pesan/doa, serta fitur berbagi kampanye.
- **Donasi Fleksibel (Guest & Terdaftar)**: Pengguna dapat berdonasi secara langsung tanpa login maupun menggunakan akun terdaftar untuk kemudahan pelacakan, disertai opsi menampilkan atau menyembunyikan nama (`show_name`).
- **Pembayaran Terintegrasi Midtrans**: Pembuatan transaksi dan *Snap Token* secara otomatis dengan pembaruan status pembayaran *real-time* melalui webhook callback.
- **Profil & Riwayat Donasi**: Pengguna yang masuk dapat melihat ringkasan total kontribusi (`/profile`) serta riwayat transaksi donasinya (`/donasi/history`).

### Panel Administrator

> Seluruh rute di bawah prefix `/admin` dilindungi oleh middleware `admin`.

- **Dashboard Statistik & Analitik**: Menampilkan ringkasan total kampanye, total donatur terverifikasi, jumlah kategori, daftar kampanye terbaru, serta visualisasi grafik progres setiap kampanye berbasis **Chart.js** melalui endpoint `/admin/statistics`.
- **Manajemen Kategori (CRUD)**: Menambah, mengubah, dan menghapus kategori donasi lengkap dengan deskripsi dan unggahan ikon (`/admin/categories`).
- **Manajemen Kampanye (CRUD)**: Mengelola judul, kategori, deskripsi, target dana, periode kampanye (`start_date` & `end_date`), unggahan gambar kampanye, serta status aktif/nonaktif (`/admin/campaigns`).
- **Manajemen Pengguna**: Melihat daftar pengguna dengan fitur pencarian dan paginasi, serta menghapus akun non-admin dengan proteksi terhadap akun bertipe `admin` (`/admin/userman`).

---

## Teknologi yang Digunakan

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend** | Laravel 11 (`^11.31`), PHP `^8.2`, `fahlisaputra/laravel-minify` |
| **Database** | MySQL (dengan primary key berbasis UUID) |
| **Frontend (Pengguna)** | Blade Templates, Tailwind CSS `^3.4`, DaisyUI `^4.12`, Vite `^6.0`, Swiper, Splide, Owl Carousel, Toastify |
| **Frontend (Admin)** | Bootstrap, Chart.js, SweetAlert2, Lightbox2 |
| **Payment Gateway** | Midtrans (`midtrans/midtrans-php` `^2.6` — Snap API & Webhook Callback) |
| **Testing & Kualitas Kode** | Pest PHP `^3.7`, Laravel Pint |

---

## Skema Database

![Schema Database](https://i.imgur.com/0a53VSs.png)

### Ringkasan Tabel Utama

| Tabel | Primary Key | Kolom & Relasi Kunci |
| :--- | :--- | :--- |
| `users` | `user_id` (UUID) | `name`, `email` (unique), `phone`, `password`, `role` (`user`, `admin`) |
| `categories` | `category_id` (UUID) | `name`, `description`, `icon` |
| `campaigns` | `campaign_id` (UUID) | `category_id` (FK -> `categories`), `slug` (unique), `title`, `description`, `target_amount`, `collected_amount`, `start_date`, `end_date`, `image_path`, `status` |
| `donations` | `donation_id` (UUID) | `user_id` (FK -> `users`, nullable), `campaign_id` (FK -> `campaigns`), `amount`, `message`, `show_name`, `status` (`pending`, `failed`, `completed`) |
| `transactions` | `transaction_id` (UUID) | `donation_id` (FK -> `donations`), `midtrans_order_id` (unique), `gross_amount`, `snap_token`, `status` (`pending`, `failed`, `completed`) |

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

- **PHP** >= 8.2 beserta ekstensi standar Laravel (`mbstring`, `openssl`, `pdo_mysql`, dll.)
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
   *(Di Windows Command Prompt/PowerShell, Anda dapat menggunakan `copy .env.example .env`)*

4. **Konfigurasi database dan Midtrans di `.env`**
   Sesuaikan koneksi MySQL (default pada `.env.example` menggunakan `DB_DATABASE=donasi`) serta tambahkan variabel konfigurasi Midtrans (lihat bagian [Konfigurasi Midtrans](#konfigurasi-midtrans)).

5. **Jalankan migrasi database dan buat symbolic link storage**
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

6. **(Opsional) Jalankan seeder data awal secara berurutan**
   ```bash
   php artisan db:seed --class=UserSeeder
   php artisan db:seed --class=CategorySeeder
   php artisan db:seed --class=CampaignSeeder
   ```
   > **Catatan Penting**:
   > - `CampaignSeeder` bergantung pada `category_id` tertentu yang dibuat oleh `CategorySeeder`, sehingga `CategorySeeder` harus dijalankan terlebih dahulu.
   > - `UserSeeder` membuat akun admin dari `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env` (bawaan: `admin@example.com` / `ChangeMe123!`). **Segera ubah kredensial ini sebelum digunakan di lingkungan production.**
   > - Atau jalankan semuanya sekaligus dengan `php artisan db:seed`.

7. **Jalankan server pengembangan**
   Jalankan seluruh layanan pengembangan (Laravel server, queue listener, dan Vite) sekaligus:
   ```bash
   composer run dev
   ```
   Atau jalankan secara terpisah pada dua terminal:
   ```bash
   php artisan serve
   npm run dev
   ```

---

## Konfigurasi Midtrans

Tambahkan variabel berikut ke dalam file `.env` sesuai konfigurasi pada `config/midtrans.php`:

```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
MIDTRANS_CALLBACK_URL=https://your-public-domain/donasi/payment/callback
```

### Alur Webhook & Callback

1. Saat donatur mengirim formulir donasi (`POST /donasi/payment`), aplikasi membuat catatan `donations` dan `transactions`, lalu meminta `snap_token` melalui Midtrans Snap API.
2. Ketika pembayaran diproses, Midtrans mengirimkan notifikasi HTTP ke endpoint `POST /donasi/payment/callback` (jalur `donasi*` telah dikecualikan dari verifikasi CSRF pada `bootstrap/app.php`).
3. `TransaksiController::handleCallback` memvalidasi `signature_key` menggunakan hash `SHA-512`, memperbarui `transactions.status` dan `donations.status`, lalu menghitung ulang `campaigns.collected_amount` berdasarkan total donasi berstatus `completed`.
4. Untuk pengujian webhook di lingkungan lokal (`localhost`), ekspos aplikasi menggunakan *tunnel* publik (misalnya **ngrok**) dan arahkan `MIDTRANS_CALLBACK_URL` serta pengaturan Notification URL pada dashboard Midtrans ke domain publik tersebut.

---

## Struktur Direktori

```text
donasi/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Auth, Campaign, Category, Dashboard, Donasi, Transaksi, Userman
│   │   └── Middleware/       # isAdmin middleware
│   ├── Models/               # Campaign, Category, Donasi, Transaksi, User
│   └── View/Components/      # ModalPayment, ModalShare, Navbar
├── config/
│   └── midtrans.php          # Konfigurasi kunci & callback Midtrans
├── database/
│   ├── migrations/           # Skema tabel users, categories, campaigns, donations, transactions
│   └── seeders/              # UserSeeder, CategorySeeder, CampaignSeeder
├── public/                   # Aset publik, gambar statis, dan hasil build
├── resources/
│   ├── css/                  # Entrypoint Tailwind CSS
│   ├── js/                   # Entrypoint JS & Slider
│   └── views/
│       ├── admin/            # Dashboard, manajemen campaign, category, dan user
│       ├── auth/             # Halaman login & register
│       ├── components/       # Komponen UI reusable (modal, navbar, alert)
│       ├── layouts/          # Layout utama pengguna (app) & admin
│       └── Pages/            # Halaman detail donasi, payment, riwayat, profil, kategori
└── routes/
    └── web.php               # Definisi rute publik, donasi, autentikasi, dan panel admin
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
