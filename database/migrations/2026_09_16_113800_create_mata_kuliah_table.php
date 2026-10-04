<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->string('kode_mata_kuliah', 20)->primary();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi', 'id_program_studi')->nullOnDelete();
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
