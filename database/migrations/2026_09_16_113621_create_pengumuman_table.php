<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * PENGUMUMAN (ERD): id_pengumuman (PK), prestasi_id (FK), kategori,
         * staff_prodi_id (FK), judul, isi, status, tanggal_dikirim, notifikasi,
         * dibaca_pada, created_at, updated_at.
         *
         * STAFF_PRODI (1) -- MEMBUAT -- (N) PENGUMUMAN     -> staff_prodi_id
         * PENGUMUMAN (1) -- MENDAPAT -- (1) PRESTASI       -> prestasi_id
         * MAHASISWA (1) -- MENERIMA -- (N) PENGUMUMAN      -> nim
         *   (FK nim menggantikan tabel lama pengumuman_penerima; satu pengumuman
         *    bersifat pribadi untuk satu mahasiswa.)
         * Kolom `notifikasi` + `dibaca_pada` menggantikan tabel lama `notifikasi`.
         */
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id('id_pengumuman');
            $table->foreignId('prestasi_id')->nullable()
                ->constrained('prestasi', 'id_prestasi')->nullOnDelete();
            $table->string('kategori', 50)->nullable();
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nim', 30)->nullable();
            $table->foreign('nim')->references('nim')->on('mahasiswa')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('judul');
            $table->text('isi');
            $table->string('status', 20)->default('draft'); // draft | terkirim | notifikasi (pesan sistem)
            $table->timestamp('tanggal_dikirim')->nullable();
            $table->text('notifikasi')->nullable();         // teks notifikasi di dashboard mahasiswa
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['nim', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
