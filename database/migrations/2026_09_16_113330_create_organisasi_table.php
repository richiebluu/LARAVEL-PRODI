<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ORGANISASI (ERD): id_organisasi (PK), nim (FK), nama_organisasi, jabatan,
         * created_at, updated_at.
         * MAHASISWA (1) -- MEMILIKI -- (N) ORGANISASI. Sumber kriteria Keaktifan Organisasi.
         */
        Schema::create('organisasi', function (Blueprint $table) {
            $table->id('id_organisasi');
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nama_organisasi', 150);
            $table->string('jabatan', 50); // daftar jabatan: config/saw.php
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisasi');
    }
};
