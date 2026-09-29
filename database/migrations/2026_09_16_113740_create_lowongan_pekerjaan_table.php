<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * LOWONGAN_PEKERJAAN (ERD): id_lowongan_pekerjaan (PK), staff_prodi_id (FK), posisi,
         * perusahaan, lokasi, tipe, deskripsi, link, batas_lamaran, status,
         * created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) LOWONGAN_PEKERJAAN.
         */
        Schema::create('lowongan_pekerjaan', function (Blueprint $table) {
            $table->id('id_lowongan_pekerjaan');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('posisi', 150);
            $table->string('perusahaan', 150);
            $table->string('lokasi', 150)->nullable();
            $table->string('tipe', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('link');
            $table->date('batas_lamaran')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongan_pekerjaan');
    }
};
