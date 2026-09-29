<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * RANKING (ERD): id_ranking (PK), nim (FK), ranking_bobot_id (FK),
         * nilai_ipk, poin_prestasi_akademik, poin_prestasi_nonakademik,
         * poin_keaktifan_organisasi, normalisasi_nilai_ipk,
         * normalisasi_prestasi_akademik, normalisasi_prestasi_nonakademik,
         * normalisasi_keaktifan_organisasi, peringkat, nilai_akhir, tahun,
         * created_at, updated_at.
         * MAHASISWA (1) -- MEMILIKI -- (N) RANKING
         * RANKING_BOBOT (1) -- MENGGUNAKAN -- (N) RANKING
         */
        Schema::create('ranking', function (Blueprint $table) {
            $table->id('id_ranking');
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ranking_bobot_id')->nullable()
                ->constrained('ranking_bobot', 'id_ranking_bobot')->nullOnDelete();

            // Matriks keputusan X (nilai kriteria C1..C4)
            $table->decimal('nilai_ipk', 6, 2)->nullable();
            $table->decimal('poin_prestasi_akademik', 6, 2)->nullable();
            $table->decimal('poin_prestasi_nonakademik', 6, 2)->nullable();
            $table->decimal('poin_keaktifan_organisasi', 6, 2)->nullable();

            // Matriks ternormalisasi R (Rij = Xij / max Xj)
            $table->decimal('normalisasi_nilai_ipk', 10, 6)->nullable();
            $table->decimal('normalisasi_prestasi_akademik', 10, 6)->nullable();
            $table->decimal('normalisasi_prestasi_nonakademik', 10, 6)->nullable();
            $table->decimal('normalisasi_keaktifan_organisasi', 10, 6)->nullable();

            $table->unsignedInteger('peringkat');
            $table->decimal('nilai_akhir', 10, 6); // Vi x 100 (skala 0-100)
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
