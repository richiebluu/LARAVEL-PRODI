<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * PRESTASI (ERD): id_prestasi (PK), nim (FK), judul, kategori, tingkat,
         * penyelenggara, tanggal, dokumen, deskripsi, status, catatan,
         * created_at, updated_at.
         * MAHASISWA (1) -- MENGAJUKAN -- (N) PRESTASI           -> nim
         * STAFF_PRODI (1) -- MEMVERIFIKASI -- (N) PRESTASI      -> staff_prodi_id
         *   (kolom FK wajib ada agar relasi MEMVERIFIKASI pada ERD benar-benar
         *    tersimpan di database; null selama status masih "menunggu").
         */
        Schema::create('prestasi', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('judul');
            $table->string('kategori', 50);       // Prestasi Akademik | Prestasi Non-Akademik
            $table->string('tingkat', 30)->nullable(); // Internal | Regional | Nasional | Internasional
            $table->string('penyelenggara')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('dokumen')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu | disetujui | ditolak
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi');
    }
};
