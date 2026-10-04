<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id('id_berita');
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('jenis', 30)->default('berita');
            $table->text('ringkasan')->nullable();
            $table->string('kategori', 100)->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->string('penyelenggara', 150)->nullable();
            $table->longText('isi');
            $table->string('gambar')->nullable();
            $table->string('link_media_sosial', 500)->nullable();
            $table->date('tanggal');
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
