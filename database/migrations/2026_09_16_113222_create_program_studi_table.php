<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id('id_program_studi');
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama_prodi', 150);
            $table->text('deskripsi')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->unsignedInteger('jumlah_alumni')->default(0);
            $table->unsignedInteger('jumlah_dosen')->default(0);
            $table->string('link_akamawa')->nullable();
            $table->string('kode_etik')->nullable();
            $table->text('link_media_sosial')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
