<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongan_pekerjaan', function (Blueprint $table) {
            $table->id('id_lowongan_pekerjaan');
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('posisi', 150);
            $table->string('perusahaan', 150);
            $table->string('lokasi', 150)->nullable();
            $table->string('tipe', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('link');
            $table->date('batas_lamaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongan_pekerjaan');
    }
};
