<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * RANKING_BOBOT (ERD): id_ranking_bobot (PK), kode, kriteria, bobot, tipe_bobot,
         * created_at, updated_at. Berisi 4 kriteria SAW (C1..C4) dengan bobot tetap.
         */
        Schema::create('ranking_bobot', function (Blueprint $table) {
            $table->id('id_ranking_bobot');
            $table->string('kode', 5)->unique();
            $table->string('kriteria', 100);
            $table->decimal('bobot', 5, 2);
            $table->string('tipe_bobot', 20)->default('benefit'); // benefit | cost
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_bobot');
    }
};
