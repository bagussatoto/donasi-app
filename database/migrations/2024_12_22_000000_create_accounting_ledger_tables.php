<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Bagan Akun (Chart of Accounts / COA)
        Schema::create('accounts', function (Blueprint $table) {
            $table->string('account_code', 20)->primary(); // Contoh: 1-1001, 2-1001
            $table->string('name', 100);
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Kepala Jurnal (Journal Headers - Polymorphic ke donations / disbursements)
        Schema::create('journal_headers', function (Blueprint $table) {
            $table->uuid('header_id')->primary();
            $table->string('voucher_number', 50)->unique(); // Contoh: JV-202611-0001
            $table->date('transaction_date');
            $table->string('source_type')->nullable(); // Polymorphic Model (App\Models\Donasi / App\Models\Disbursement)
            $table->uuid('source_id')->nullable(); // Polymorphic UUID
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        // 3. Tabel Baris Jurnal / Jurnal Masukan (Journal Entries - Double Entry Debit & Credit)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('entry_id')->primary();
            $table->uuid('header_id');
            $table->string('account_code', 20);
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->timestamps();

            $table->foreign('header_id')->references('header_id')->on('journal_headers')->onDelete('cascade');
            $table->foreign('account_code')->references('account_code')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journal_headers');
        Schema::dropIfExists('accounts');
    }
};
