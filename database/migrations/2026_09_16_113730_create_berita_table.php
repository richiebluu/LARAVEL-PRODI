<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * BERITA (ERD): id_berita (PK), staff_prodi_id (FK), judul, slug, ringkasan,
         * kategori, isi, gambar, tanggal, status, created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) BERITA.
         */
        Schema::create('berita', function (Blueprint $table) {
            $table->id('id_berita');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('ringkasan')->nullable();
            $table->string('kategori', 100)->nullable();
            $table->longText('isi');
            $table->string('gambar')->nullable();
            $table->date('tanggal');
            $table->string('status', 20)->default('draft'); // draft | terbit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
