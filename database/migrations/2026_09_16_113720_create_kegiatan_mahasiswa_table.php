<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * KEGIATAN_MAHASISWA (ERD): id_kegiatan_mahasiswa (PK), staff_prodi_id (FK), judul,
         * kategori, tanggal, lokasi, penyelenggara, deskripsi, foto, status,
         * created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) KEGIATAN_MAHASISWA.
         */
        Schema::create('kegiatan_mahasiswa', function (Blueprint $table) {
            $table->id('id_kegiatan_mahasiswa');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('judul', 150);
            $table->string('kategori', 50);
            $table->date('tanggal');
            $table->string('lokasi', 150)->nullable();
            $table->string('penyelenggara', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif
            $table->timestamps();

            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_mahasiswa');
    }
};
