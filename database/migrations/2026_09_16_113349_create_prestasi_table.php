<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasi', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('judul');
            $table->string('kategori', 50);
            $table->string('tingkat', 30)->nullable();
            $table->string('penyelenggara')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('dokumen')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi');
    }
};
