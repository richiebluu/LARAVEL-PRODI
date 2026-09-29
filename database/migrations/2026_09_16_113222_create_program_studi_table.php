<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * PROGRAM_STUDI (ERD): id_program_studi (PK), staff_prodi_id (FK), nama_prodi,
         * deskripsi, visi, misi, jumlah_alumni, jumlah_dosen, link_akamawa, kode_etik,
         * created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) PROGRAM_STUDI.
         */
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id('id_program_studi');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama_prodi', 150);
            $table->text('deskripsi')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->unsignedInteger('jumlah_alumni')->default(0);
            $table->unsignedInteger('jumlah_dosen')->default(0);
            $table->string('link_akamawa')->nullable();
            $table->string('kode_etik')->nullable(); // path PDF Kode Etik Mahasiswa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
