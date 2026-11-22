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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->uuid('disbursement_id')->primary();
            $table->uuid('campaign_id');
            $table->uuid('user_id'); // Admin/Pengaju yang mencatat pencairan
            $table->unsignedBigInteger('amount'); // Nominal pencairan
            $table->string('bank_name'); // Nama Bank penerima
            $table->string('account_number'); // Nomor rekening
            $table->string('account_name'); // Atas nama rekening penerima
            $table->text('purpose'); // Tujuan / keperluan penggunaan dana
            $table->string('receipt_path')->nullable(); // Foto/PDF bukti transfer
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->timestamps();

            $table->foreign('campaign_id')->references('campaign_id')->on('campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
