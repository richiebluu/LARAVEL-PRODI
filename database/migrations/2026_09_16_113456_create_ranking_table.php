<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking', function (Blueprint $table) {
            $table->id('id_ranking');
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ranking_bobot_id')->nullable()->constrained('ranking_bobot', 'id_ranking_bobot')->nullOnDelete();
            $table->decimal('nilai_ipk', 6, 2)->nullable();
            $table->decimal('poin_prestasi_akademik', 6, 2)->nullable();
            $table->decimal('poin_prestasi_nonakademik', 6, 2)->nullable();
            $table->decimal('poin_keaktifan_organisasi', 6, 2)->nullable();
            $table->decimal('normalisasi_nilai_ipk', 10, 6)->nullable();
            $table->decimal('normalisasi_prestasi_akademik', 10, 6)->nullable();
            $table->decimal('normalisasi_prestasi_nonakademik', 10, 6)->nullable();
            $table->decimal('normalisasi_keaktifan_organisasi', 10, 6)->nullable();
            $table->unsignedInteger('peringkat');
            $table->decimal('nilai_akhir', 10, 6);
            $table->year('tahun');
            $table->timestamps();

            $table->unique(['nim', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking');
    }
};
