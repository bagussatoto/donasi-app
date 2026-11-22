-- =====================================================================
-- SKRIP SQL DDL (DATA DEFINITION LANGUAGE) & INITIAL DATA
-- APLIKASI: Ayo Donasi (Platform Crowdfunding & Filantropi Digital)
-- BASIS DATA: MySQL 8.0+ / MariaDB 10.5+
-- ENCODING: UTF-8 Unicode (utf8mb4 / utf8mb4_unicode_ci)
-- ENGINE: InnoDB
-- GENERATED DATE: 2026-11-22
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ---------------------------------------------------------------------
-- 1. DROP TABEL JIKA SUDAH ADA (URUTAN TERBALIK RELASI)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `journal_entries`;
DROP TABLE IF EXISTS `journal_headers`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `campaign_updates`;
DROP TABLE IF EXISTS `disbursements`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `donations`;
DROP TABLE IF EXISTS `campaigns`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;

-- ---------------------------------------------------------------------
-- 2. TABEL: users
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
    `user_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(255) NULL DEFAULT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABEL: password_reset_tokens
-- ---------------------------------------------------------------------
CREATE TABLE `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABEL: sessions
-- ---------------------------------------------------------------------
CREATE TABLE `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` CHAR(36) NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABEL: categories
-- ---------------------------------------------------------------------
CREATE TABLE `categories` (
    `category_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `icon` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABEL: campaigns
-- ---------------------------------------------------------------------
CREATE TABLE `campaigns` (
    `campaign_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `category_id` CHAR(36) NULL DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `target_amount` BIGINT UNSIGNED NOT NULL,
    `collected_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `start_date` DATE NULL DEFAULT NULL,
    `end_date` DATE NOT NULL,
    `image_path` VARCHAR(255) NULL DEFAULT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`campaign_id`),
    UNIQUE KEY `campaigns_slug_unique` (`slug`),
    KEY `campaigns_category_id_foreign` (`category_id`),
    CONSTRAINT `campaigns_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABEL: donations
-- ---------------------------------------------------------------------
CREATE TABLE `donations` (
    `donation_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `user_id` CHAR(36) NULL DEFAULT NULL,
    `campaign_id` CHAR(36) NOT NULL,
    `message` VARCHAR(255) NULL DEFAULT NULL,
    `amount` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'failed', 'completed') NOT NULL DEFAULT 'pending',
    `show_name` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`donation_id`),
    KEY `donations_user_id_foreign` (`user_id`),
    KEY `donations_campaign_id_foreign` (`campaign_id`),
    CONSTRAINT `donations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    CONSTRAINT `donations_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. TABEL: transactions (Midtrans Payment Gateway)
-- ---------------------------------------------------------------------
CREATE TABLE `transactions` (
    `transaction_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `donation_id` CHAR(36) NOT NULL,
    `midtrans_order_id` VARCHAR(255) NOT NULL,
    `gross_amount` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'failed', 'completed') NOT NULL DEFAULT 'pending',
    `snap_token` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`transaction_id`),
    UNIQUE KEY `transactions_midtrans_order_id_unique` (`midtrans_order_id`),
    KEY `transactions_donation_id_foreign` (`donation_id`),
    CONSTRAINT `transactions_donation_id_foreign` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`donation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. TABEL: disbursements (Pencairan Dana Donasi)
-- ---------------------------------------------------------------------
CREATE TABLE `disbursements` (
    `disbursement_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `campaign_id` CHAR(36) NOT NULL,
    `user_id` CHAR(36) NOT NULL COMMENT 'Admin pemroses pencairan',
    `amount` BIGINT UNSIGNED NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(50) NOT NULL,
    `account_name` VARCHAR(150) NOT NULL,
    `purpose` TEXT NOT NULL,
    `receipt_path` VARCHAR(255) NULL DEFAULT NULL,
    `status` ENUM('pending', 'completed', 'rejected') NOT NULL DEFAULT 'completed',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`disbursement_id`),
    KEY `disbursements_campaign_id_foreign` (`campaign_id`),
    KEY `disbursements_user_id_foreign` (`user_id`),
    CONSTRAINT `disbursements_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`) ON DELETE CASCADE,
    CONSTRAINT `disbursements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. TABEL: campaign_updates (Kabar Terbaru & Penyaluran Donasi)
-- ---------------------------------------------------------------------
CREATE TABLE `campaign_updates` (
    `update_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `campaign_id` CHAR(36) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `image_path` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`update_id`),
    KEY `campaign_updates_campaign_id_foreign` (`campaign_id`),
    CONSTRAINT `campaign_updates_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. TABEL: accounts (Bagan Akun Standar / Chart of Accounts)
-- ---------------------------------------------------------------------
CREATE TABLE `accounts` (
    `account_code` VARCHAR(20) NOT NULL COMMENT 'Kode Akun COA',
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`account_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. TABEL: journal_headers (Kepala Jurnal Umum / Voucher)
-- ---------------------------------------------------------------------
CREATE TABLE `journal_headers` (
    `header_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `voucher_number` VARCHAR(50) NOT NULL,
    `transaction_date` DATE NOT NULL,
    `source_type` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Polymorphic Model Class',
    `source_id` CHAR(36) NULL DEFAULT NULL COMMENT 'Polymorphic Foreign UUID',
    `description` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`header_id`),
    UNIQUE KEY `journal_headers_voucher_number_unique` (`voucher_number`),
    KEY `journal_headers_source_type_source_id_index` (`source_type`, `source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. TABEL: journal_entries (Entri Baris Jurnal Berpasangan Debit/Kredit)
-- ---------------------------------------------------------------------
CREATE TABLE `journal_entries` (
    `entry_id` CHAR(36) NOT NULL COMMENT 'UUID Primary Key',
    `header_id` CHAR(36) NOT NULL,
    `account_code` VARCHAR(20) NOT NULL,
    `debit` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `credit` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`entry_id`),
    KEY `journal_entries_header_id_foreign` (`header_id`),
    KEY `journal_entries_account_code_foreign` (`account_code`),
    CONSTRAINT `journal_entries_header_id_foreign` FOREIGN KEY (`header_id`) REFERENCES `journal_headers` (`header_id`) ON DELETE CASCADE,
    CONSTRAINT `journal_entries_account_code_foreign` FOREIGN KEY (`account_code`) REFERENCES `accounts` (`account_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. TABEL BAWAAN FRAMEWORK (CACHE & QUEUE JOBS)
-- ---------------------------------------------------------------------
CREATE TABLE `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL DEFAULT NULL,
    `cancelled_at` INT NULL DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. DATA AWAL (INITIAL SEEDING)
-- ---------------------------------------------------------------------

-- Master Bagan Akun Standar (COA)
INSERT INTO `accounts` (`account_code`, `name`, `type`, `description`, `created_at`, `updated_at`) VALUES
('1-1001', 'Kas & Bank - Penampung Donasi', 'asset', 'Rekening penampungan dana titipan donasi masyarakat', NOW(), NOW()),
('1-1002', 'Piutang Payment Gateway (Midtrans)', 'asset', 'Saldo settlement donasi di Midtrans yang belum ditarik ke bank', NOW(), NOW()),
('2-1001', 'Dana Donasi Terikat (Titipan Donasi)', 'liability', 'Amanah donasi yang wajib disalurkan ke penerima manfaat', NOW(), NOW()),
('4-1001', 'Pendapatan Hak Operasional Platform', 'revenue', 'Hak kelola operasional resmi (ujrah) yayasan', NOW(), NOW()),
('5-2001', 'Penyaluran Donasi Program', 'expense', 'Pengeluaran pencairan dana langsung ke pihak penerima manfaat', NOW(), NOW());

-- Master Kategori Donasi Awal
INSERT INTO `categories` (`category_id`, `name`, `description`, `icon`, `created_at`, `updated_at`) VALUES
('4d77698c-414d-4e78-8281-2574fcd10859', 'Sosial', 'Bantuan untuk biaya sosial, kemasyarakatan, dan yatim piatu.', '/icons/education.png', NOW(), NOW()),
('8f2316e2-2a54-46c9-b701-3829019cf301', 'Bencana Alam', 'Donasi darurat untuk korban bencana alam seperti gempa, banjir, dan longsor.', '/icons/disaster.png', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
-- =====================================================================
-- SELESAI EKSEKUSI SKRIP SQL DDL AYO DONASI
-- =====================================================================
