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
        Schema::create('campaign_updates', function (Blueprint $table) {
            $table->uuid('update_id')->primary();
            $table->uuid('campaign_id');
            $table->string('title'); // Judul kabar terbaru
            $table->text('content'); // Isi berita / perkembangan pasien/bantuan
            $table->string('image_path')->nullable(); // Foto dokumentasi perkembangan
            $table->timestamps();

            $table->foreign('campaign_id')->references('campaign_id')->on('campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_updates');
    }
};
