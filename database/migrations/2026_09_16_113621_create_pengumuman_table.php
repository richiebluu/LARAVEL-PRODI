<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id('id_pengumuman');
            $table->foreignId('prestasi_id')->nullable()->constrained('prestasi', 'id_prestasi')->nullOnDelete();
            $table->string('kategori', 50)->nullable();
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nim', 30)->nullable();
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('judul');
            $table->text('isi');
            $table->string('status', 20)->default('draft');
            $table->timestamp('tanggal_dikirim')->nullable();
            $table->text('notifikasi')->nullable();
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
