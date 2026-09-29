<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * STRUKTUR_ORGANISASI (ERD): id_struktur_organisasi (PK), program_studi_id (FK),
         * dosen_id (FK -> dosen.nuptk), nama, jabatan, foto, created_at, updated_at.
         * PROGRAM_STUDI (1) -- MEMILIKI -- (N) STRUKTUR_ORGANISASI
         * DOSEN (1) -- MENJABAT -- (N) STRUKTUR_ORGANISASI
         */
        Schema::create('struktur_organisasi', function (Blueprint $table) {
            $table->id('id_struktur_organisasi');
            $table->foreignId('program_studi_id')->nullable()
                ->constrained('program_studi', 'id_program_studi')->nullOnDelete();
            $table->string('dosen_id', 30)->nullable();
            $table->foreign('dosen_id')->references('nuptk')->on('dosen')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->string('nama', 150)->nullable(); // dipakai bila pejabat bukan dosen (mis. Staff Prodi)
            $table->string('jabatan', 150);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasi');
    }
};
