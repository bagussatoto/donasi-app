# Bagan Diagram Database — Ayo Donasi

Dokumen ini berisi bagan Entity Relationship Diagram (ERD) dan rincian struktur tabel database pada aplikasi **Ayo Donasi** berbasis Laravel 11 dan MySQL.

---

## 1. Entity Relationship Diagram (ERD)

Diagram berikut merepresentasikan seluruh entitas utama dan relasinya di dalam sistem database:

```mermaid
erDiagram
    USERS ||--o{ DONATIONS : "melakukan (opsional)"
    USERS ||--o{ SESSIONS : "memiliki"
    USERS ||--o{ DISBURSEMENTS : "mencairkan (admin)"
    CATEGORIES ||--o{ CAMPAIGNS : "mengelompokkan"
    CAMPAIGNS ||--o{ DONATIONS : "menerima"
    CAMPAIGNS ||--o{ DISBURSEMENTS : "menyalurkan"
    CAMPAIGNS ||--o{ CAMPAIGN_UPDATES : "memiliki"
    DONATIONS ||--|| TRANSACTIONS : "menghasilkan"

    %% Double-Entry Accounting Ledger
    ACCOUNTS ||--o{ JOURNAL_ENTRIES : "memiliki riwayat"
    JOURNAL_HEADERS ||--o{ JOURNAL_ENTRIES : "berisi"
    JOURNAL_HEADERS }o--|| DONATIONS : "polymorphic source"
    JOURNAL_HEADERS }o--|| DISBURSEMENTS : "polymorphic source"

    ACCOUNTS {
        string account_code PK "Kode akun unik (e.g. 1-1001)"
        string account_name "Nama akun COA"
        enum account_type "asset, liability, equity, revenue, expense"
        enum normal_balance "debit atau credit"
        text description "Keterangan akun"
    }

    JOURNAL_HEADERS {
        uuid header_id PK "UUID Primary Key"
        string voucher_number UK "Nomor voucher unik (JV-...)"
        date transaction_date "Tanggal transaksi"
        string source_type "Polymorphic class (Donasi/Disbursement)"
        uuid source_id "Polymorphic foreign key"
        text description "Uraian jurnal transaksi"
    }

    JOURNAL_ENTRIES {
        uuid entry_id PK "UUID Primary Key"
        uuid header_id FK "FK ke journal_headers"
        string account_code FK "FK ke accounts"
        unsigned_bigint debit "Nilai debit (Rp)"
        unsigned_bigint credit "Nilai kredit (Rp)"
    }

    DISBURSEMENTS {
        uuid disbursement_id PK "UUID (Primary Key)"
        uuid campaign_id FK "FK ke campaigns"
        uuid user_id FK "FK ke users (pencatat)"
        unsigned_bigint amount "Nominal pencairan"
        string bank_name "Nama bank tujuan"
        string account_number "Nomor rekening tujuan"
        string account_name "Nama pemilik rekening"
        text purpose "Tujuan penggunaan dana"
        string receipt_path "Path bukti transfer/kwitansi"
        enum status "Status: completed, pending, cancelled"
    }

    CAMPAIGN_UPDATES {
        uuid update_id PK "UUID (Primary Key)"
        uuid campaign_id FK "FK ke campaigns"
        string title "Judul kabar terbaru"
        text content "Uraian perkembangan penyaluran"
        string image_path "Foto dokumentasi"
    }

    USERS {
        uuid user_id PK "UUID (Primary Key)"
        string name "Nama lengkap pengguna"
        string email UK "Alamat email unik"
        timestamp email_verified_at "Waktu verifikasi email (opsional)"
        string password "Password terenkripsi (bcrypt)"
        string phone "Nomor telepon (opsional)"
        enum role "Role pengguna: 'user' atau 'admin'"
        string remember_token "Token remember me"
        timestamp created_at "Waktu dibuat"
        timestamp updated_at "Waktu diubah"
    }

    CATEGORIES {
        uuid category_id PK "UUID (Primary Key)"
        string name "Nama kategori (Sosial, Bencana, dll)"
        text description "Deskripsi kategori (opsional)"
        string icon "Path/nama file ikon kategori (opsional)"
        timestamp created_at "Waktu dibuat"
        timestamp updated_at "Waktu diubah"
    }

    CAMPAIGNS {
        uuid campaign_id PK "UUID (Primary Key)"
        uuid category_id FK "FK ke categories (null on delete)"
        string slug UK "Slug unik untuk URL"
        string title "Judul kampanye donasi"
        text description "Deskripsi lengkap kampanye"
        unsigned_bigint target_amount "Target donasi yang dibutuhkan"
        unsigned_bigint collected_amount "Total donasi yang sudah terkumpul"
        date start_date "Tanggal mulai kampanye (opsional)"
        date end_date "Tanggal batas akhir kampanye"
        string image_path "Path file foto kampanye (opsional)"
        boolean status "Status aktif/nonaktif (default: true)"
        timestamp created_at "Waktu dibuat"
        timestamp updated_at "Waktu diubah"
    }

    DONATIONS {
        uuid donation_id PK "UUID (Primary Key)"
        uuid user_id FK "FK ke users (null on delete / donatur guest)"
        uuid campaign_id FK "FK ke campaigns (cascade on delete)"
        string message "Pesan/doa donatur (opsional)"
        unsigned_bigint amount "Nominal donasi (min. Rp 1.000)"
        enum status "Status: 'pending', 'failed', 'completed'"
        boolean show_name "Tampilkan nama (true) / anonim (false)"
        timestamp created_at "Waktu dibuat"
        timestamp updated_at "Waktu diubah"
    }

    TRANSACTIONS {
        uuid transaction_id PK "UUID (Primary Key)"
        uuid donation_id FK "FK ke donations (cascade on delete)"
        string midtrans_order_id UK "Order ID Midtrans (ORD-UUID)"
        unsigned_bigint gross_amount "Total nominal transaksi"
        enum status "Status: 'pending', 'failed', 'completed'"
        string snap_token "Token Snap Midtrans (opsional)"
        timestamp created_at "Waktu dibuat"
        timestamp updated_at "Waktu diubah"
    }

    SESSIONS {
        string id PK "Session ID"
        uuid user_id FK "FK ke users (nullable)"
        string ip_address "IP Address pengguna"
        text user_agent "User Agent browser"
        longtext payload "Data session terenkripsi"
        integer last_activity "Unix timestamp aktivitas terakhir"
    }
```

---

## 2. Rincian Kamus Data (Data Dictionary)

### 2.1. Tabel `users`
Menyimpan data akun pengguna dan administrator.
- **Primary Key:** `user_id` (UUID)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `user_id` | `CHAR(36)` / UUID | Tidak | ID unik pengguna |
| `name` | `VARCHAR(255)` | Tidak | Nama pengguna |
| `email` | `VARCHAR(255)` | Tidak | Alamat email (Unique) |
| `email_verified_at` | `TIMESTAMP` | Ya | Waktu verifikasi email |
| `password` | `VARCHAR(255)` | Tidak | Password terenkripsi (Hash::make) |
| `phone` | `VARCHAR(255)` | Ya | Nomor telepon |
| `role` | `ENUM('user', 'admin')` | Tidak | Role pengguna (Default: `'user'`) |
| `remember_token` | `VARCHAR(100)` | Ya | Token "Ingat saya" |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.2. Tabel `categories`
Menyimpan kategori kampanye (contoh: Pendidikan, Bencana Alam, Sosial, Medis).
- **Primary Key:** `category_id` (UUID)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `category_id` | `CHAR(36)` / UUID | Tidak | ID unik kategori |
| `name` | `VARCHAR(255)` | Tidak | Nama kategori |
| `description` | `TEXT` | Ya | Keterangan kategori |
| `icon` | `VARCHAR(255)` | Ya | Path file ikon pada storage |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.3. Tabel `campaigns`
Menyimpan data kampanye penggalangan dana.
- **Primary Key:** `campaign_id` (UUID)
- **Foreign Key:** `category_id` $\rightarrow$ `categories.category_id` (`ON DELETE SET NULL`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `campaign_id` | `CHAR(36)` / UUID | Tidak | ID unik kampanye |
| `category_id` | `CHAR(36)` / UUID | Ya | Relasi ke kategori donasi |
| `slug` | `VARCHAR(255)` | Tidak | URL slug (Unique) |
| `title` | `VARCHAR(255)` | Tidak | Judul kampanye |
| `description` | `TEXT` | Tidak | Deskripsi lengkap |
| `target_amount` | `BIGINT UNSIGNED` | Tidak | Target donasi |
| `collected_amount` | `BIGINT UNSIGNED` | Tidak | Dana terkumpul (Default: `0`) |
| `start_date` | `DATE` | Ya | Tanggal mulai kampanye |
| `end_date` | `DATE` | Tidak | Tanggal berakhir kampanye |
| `image_path` | `VARCHAR(255)` | Ya | Path file gambar kampanye |
| `status` | `BOOLEAN` | Tidak | Status kampanye (`true`=aktif, `false`=nonaktif) |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.4. Tabel `donations`
Menyimpan pencatatan donasi yang dilakukan pengguna (baik guest maupun user login).
- **Primary Key:** `donation_id` (UUID)
- **Foreign Keys:**
  - `user_id` $\rightarrow$ `users.user_id` (`ON DELETE SET NULL`)
  - `campaign_id` $\rightarrow$ `campaigns.campaign_id` (`ON DELETE CASCADE`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `donation_id` | `CHAR(36)` / UUID | Tidak | ID unik donasi |
| `user_id` | `CHAR(36)` / UUID | Ya | Relasi user (null jika donatur guest) |
| `campaign_id` | `CHAR(36)` / UUID | Tidak | Relasi kampanye tujuan |
| `message` | `VARCHAR(255)` | Ya | Pesan / doa dari donatur |
| `amount` | `BIGINT UNSIGNED` | Tidak | Nominal donasi |
| `status` | `ENUM('pending', 'failed', 'completed')` | Tidak | Status donasi (Default: `'pending'`) |
| `show_name` | `BOOLEAN` | Tidak | Tampilkan nama (`true`) / Anonim (`false`) |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.5. Tabel `transactions`
Menyimpan data transaksi gateway pembayaran (Midtrans Snap).
- **Primary Key:** `transaction_id` (UUID)
- **Foreign Key:** `donation_id` $\rightarrow$ `donations.donation_id` (`ON DELETE CASCADE`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `transaction_id` | `CHAR(36)` / UUID | Tidak | ID unik transaksi internal |
| `donation_id` | `CHAR(36)` / UUID | Tidak | Relasi ke donasi |
| `midtrans_order_id`| `VARCHAR(255)` | Tidak | Order ID unik Midtrans (`ORD-{UUID}`) |
| `gross_amount` | `BIGINT UNSIGNED` | Tidak | Nominal bruto transaksi |
| `status` | `ENUM('pending', 'failed', 'completed')` | Tidak | Status transaksi (Default: `'pending'`) |
| `snap_token` | `VARCHAR(255)` | Ya | Token Snap untuk memunculkan popup pembayaran |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.6. Tabel `disbursements`
Menyimpan data pengajuan dan penyaluran dana donasi ke rekening penerima manfaat.
- **Primary Key:** `disbursement_id` (UUID)
- **Foreign Keys:**
  - `campaign_id` $\rightarrow$ `campaigns.campaign_id` (`ON DELETE CASCADE`)
  - `user_id` $\rightarrow$ `users.user_id` (`ON DELETE CASCADE`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `disbursement_id` | `CHAR(36)` / UUID | Tidak | ID unik pencairan dana |
| `campaign_id` | `CHAR(36)` / UUID | Tidak | Relasi kampanye asal dana |
| `user_id` | `CHAR(36)` / UUID | Tidak | Relasi admin pemroses pencairan |
| `amount` | `BIGINT UNSIGNED` | Tidak | Nominal pencairan (Min. Rp 10.000) |
| `bank_name` | `VARCHAR(100)` | Tidak | Nama bank penerima manfaat |
| `account_number` | `VARCHAR(50)` | Tidak | Nomor rekening penerima manfaat |
| `account_name` | `VARCHAR(150)` | Tidak | Nama pemilik rekening |
| `purpose` | `TEXT` | Tidak | Alasan / peruntukan pencairan |
| `receipt_path` | `VARCHAR(255)` | Ya | Path file foto bukti transfer/kwitansi |
| `status` | `ENUM('pending', 'completed', 'cancelled')` | Tidak | Status pencairan (Default: `'completed'`) |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.7. Tabel `campaign_updates`
Menyimpan riwayat berita, rilis kabar terbaru, dan dokumentasi perkembangan kampanye.
- **Primary Key:** `update_id` (UUID)
- **Foreign Key:** `campaign_id` $\rightarrow$ `campaigns.campaign_id` (`ON DELETE CASCADE`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `update_id` | `CHAR(36)` / UUID | Tidak | ID unik kabar perkembangan |
| `campaign_id` | `CHAR(36)` / UUID | Tidak | Relasi kampanye tujuan |
| `title` | `VARCHAR(255)` | Tidak | Judul kabar perkembangan |
| `content` | `TEXT` | Tidak | Isi teks perkembangan penyaluran |
| `image_path` | `VARCHAR(255)` | Ya | Foto dokumentasi kegiatan |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.8. Tabel `accounts` (Bagan Akun / Chart of Accounts)
Menyimpan master data kode akun akuntansi buku besar berpasangan.
- **Primary Key:** `account_code` (`VARCHAR(20)`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `account_code` | `VARCHAR(20)` | Tidak | Kode akun (e.g. `1-1001`, `2-1001`) |
| `account_name` | `VARCHAR(255)` | Tidak | Nama akun buku besar |
| `account_type` | `ENUM('asset', 'liability', 'equity', 'revenue', 'expense')` | Tidak | Klasifikasi tipe akun |
| `normal_balance` | `ENUM('debit', 'credit')` | Tidak | Saldo normal akun |
| `description` | `TEXT` | Ya | Deskripsi peruntukan akun |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.9. Tabel `journal_headers`
Menyimpan header bukti jurnal transaksi umum (General Journal Voucher) berpasangan polimorfik.
- **Primary Key:** `header_id` (UUID)
- **Polymorphic Relation:** `source_type` & `source_id` ke `donations` atau `disbursements`.

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `header_id` | `CHAR(36)` / UUID | Tidak | ID unik jurnal voucher |
| `voucher_number` | `VARCHAR(50)` | Tidak | Nomor referensi unik (`JV-...`) |
| `transaction_date` | `DATE` | Tidak | Tanggal transaksi finansial |
| `source_type` | `VARCHAR(255)` | Ya | Class target (App\Models\Donasi / Disbursement) |
| `source_id` | `CHAR(36)` / UUID | Ya | ID entitas sumber transaksi |
| `description` | `TEXT` | Tidak | Uraian / memo jurnal |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

### 2.10. Tabel `journal_entries`
Menyimpan rincian baris debit dan kredit yang saling seimbang (*balanced double-entry*) untuk setiap header voucher.
- **Primary Key:** `entry_id` (UUID)
- **Foreign Keys:**
  - `header_id` $\rightarrow$ `journal_headers.header_id` (`ON DELETE CASCADE`)
  - `account_code` $\rightarrow$ `accounts.account_code` (`ON DELETE RESTRICT`)

| Kolom | Tipe Data | Nullable | Keterangan |
| :--- | :--- | :---: | :--- |
| `entry_id` | `CHAR(36)` / UUID | Tidak | ID unik entri baris |
| `header_id` | `CHAR(36)` / UUID | Tidak | Relasi ke header voucher |
| `account_code` | `VARCHAR(20)` | Tidak | Kode akun yang didebit/dikredit |
| `debit` | `BIGINT UNSIGNED` | Tidak | Nilai debet (Default: `0`) |
| `credit` | `BIGINT UNSIGNED` | Tidak | Nilai kredit (Default: `0`) |
| `memo` | `VARCHAR(255)` | Ya | Catatan khusus baris jurnal |
| `created_at` | `TIMESTAMP` | Ya | Waktu record dibuat |
| `updated_at` | `TIMESTAMP` | Ya | Waktu record terakhir diperbarui |

---

## 3. Tabel Pendukung Framework (Bawaan Laravel)

1. **`sessions`**: Menyimpan sesi pengguna saat menggunakan database driver session (`SESSION_DRIVER=database`).
2. **`password_reset_tokens`**: Menyimpan token reset kata sandi pengguna.
3. **`cache` & `cache_locks`**: Menyimpan data cache sistem saat menggunakan database cache driver.
4. **`jobs`, `job_batches`, `failed_jobs`**: Mengelola antrean asynchronous (queue worker) untuk pengiriman email atau proses background.

---

## 4. Trigger Validasi Basis Data (Database-Level Constraints)

Untuk menjamin integritas data secara mutlak bahkan jika terjadi manipulasi data manual di luar aplikasi Laravel (misal via phpMyAdmin/DBeaver), sistem dilengkapi dengan 4 trigger validasi MySQL:

1. **`trg_check_journal_entry_parity_insert` & `trg_check_journal_entry_parity_update`**:
   - **Tabel:** `journal_entries` (`BEFORE INSERT`, `BEFORE UPDATE`)
   - **Fungsi:** Memastikan setiap baris entri jurnal bernilai tunggal (hanya Debit saja ATAU Kredit saja). Menolak jika kedua kolom bernilai `> 0` atau keduanya bernilai `0`.
2. **`trg_check_disbursement_limit`**:
   - **Tabel:** `disbursements` (`BEFORE INSERT`)
   - **Fungsi:** Menghitung sisa saldo kampanye (`collected_amount - total_disbursements_completed`). Menolak transaksi pencairan jika nominal pengajuan melebihi saldo yang tersedia.
3. **`trg_check_donation_campaign_active`**:
   - **Tabel:** `donations` (`BEFORE INSERT`)
   - **Fungsi:** Memeriksa status kampanye target (`status = 1`) dan tanggal kadaluarsa (`end_date >= CURDATE()`). Menolak penerimaan donasi baru untuk kampanye nonaktif atau yang telah lewat tenggat waktu.
