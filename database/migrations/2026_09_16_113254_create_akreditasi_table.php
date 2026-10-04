<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akreditasi', function (Blueprint $table) {
            $table->id('id_akreditasi');
            $table->foreignId('program_studi_id')->constrained('program_studi', 'id_program_studi')->cascadeOnDelete();
            $table->string('peringkat', 50);
            $table->string('nomor_sk', 100)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_berakhir')->nullable();
            $table->string('lembaga', 150)->nullable();
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi');
    }
};
