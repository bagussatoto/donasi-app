<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Triggers menggunakan sintaks khusus MySQL (SIGNAL SQLSTATE)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. Trigger Validasi Integritas Entri Jurnal (INSERT):
        // Memastikan tidak ada baris debit dan kredit terisi bersamaan atau keduanya nol
        DB::unprepared("
            CREATE TRIGGER `trg_check_journal_entry_parity_insert` BEFORE INSERT ON `journal_entries`
            FOR EACH ROW
            BEGIN
                IF (NEW.debit > 0 AND NEW.credit > 0) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Entri jurnal tidak boleh memiliki nilai Debit dan Kredit sekaligus!';
                END IF;
                IF (NEW.debit = 0 AND NEW.credit = 0) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Entri jurnal harus memiliki nilai Debit atau Kredit (tidak boleh keduanya 0)!';
                END IF;
            END
        ");

        // 2. Trigger Validasi Integritas Entri Jurnal (UPDATE):
        DB::unprepared("
            CREATE TRIGGER `trg_check_journal_entry_parity_update` BEFORE UPDATE ON `journal_entries`
            FOR EACH ROW
            BEGIN
                IF (NEW.debit > 0 AND NEW.credit > 0) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Entri jurnal tidak boleh memiliki nilai Debit dan Kredit sekaligus!';
                END IF;
                IF (NEW.debit = 0 AND NEW.credit = 0) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Entri jurnal harus memiliki nilai Debit atau Kredit (tidak boleh keduanya 0)!';
                END IF;
            END
        ");

        // 3. Trigger Validasi Batas Pencairan Dana (INSERT):
        // Mencegah pencairan dana melebihi sisa dana kampanye yang terkumpul
        DB::unprepared("
            CREATE TRIGGER `trg_check_disbursement_limit` BEFORE INSERT ON `disbursements`
            FOR EACH ROW
            BEGIN
                DECLARE v_collected BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_disbursed BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_available BIGINT DEFAULT 0;

                SELECT COALESCE(collected_amount, 0) INTO v_collected
                FROM `campaigns`
                WHERE `campaign_id` = NEW.campaign_id;

                SELECT COALESCE(SUM(amount), 0) INTO v_disbursed
                FROM `disbursements`
                WHERE `campaign_id` = NEW.campaign_id AND `status` = 'completed';

                SET v_available = CAST(v_collected AS SIGNED) - CAST(v_disbursed AS SIGNED);

                IF CAST(NEW.amount AS SIGNED) > v_available THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Nominal pencairan melebihi sisa dana kampanye yang tersedia!';
                END IF;
            END
        ");

        // 4. Trigger Validasi Status Kampanye untuk Donasi Baru (INSERT):
        // Menolak donasi pada kampanye nonaktif atau telah berakhir
        DB::unprepared("
            CREATE TRIGGER `trg_check_donation_campaign_active` BEFORE INSERT ON `donations`
            FOR EACH ROW
            BEGIN
                DECLARE v_status TINYINT DEFAULT 1;
                DECLARE v_end_date DATE;

                SELECT `status`, `end_date` INTO v_status, v_end_date
                FROM `campaigns`
                WHERE `campaign_id` = NEW.campaign_id;

                IF v_status = 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Kampanye donasi sedang tidak aktif!';
                END IF;

                IF v_end_date < CURDATE() THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Gagal: Batas waktu penerimaan donasi kampanye ini sudah berakhir!';
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared("DROP TRIGGER IF EXISTS `trg_check_journal_entry_parity_insert`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_check_journal_entry_parity_update`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_check_disbursement_limit`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_check_donation_campaign_active`");
    }
};
