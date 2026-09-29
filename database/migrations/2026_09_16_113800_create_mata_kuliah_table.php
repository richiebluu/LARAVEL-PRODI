<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * MATA_KULIAH (Kurikulum) — TIDAK terdapat pada ERD.
         * Dipertahankan atas permintaan pemilik project (fitur Profil > Kurikulum
         * dan CRUD + impor CSV Staff Prodi tetap berjalan). Tabel berdiri sendiri.
         */
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('sks');
            $table->string('jenis', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
