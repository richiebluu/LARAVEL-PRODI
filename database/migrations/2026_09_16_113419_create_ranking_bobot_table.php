<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking_bobot', function (Blueprint $table) {
            $table->id('id_ranking_bobot');
            $table->string('kode', 5)->unique();
            $table->string('kriteria', 100);
            $table->decimal('bobot', 5, 2);
            $table->string('tipe_bobot', 20)->default('benefit');
            $table->text('dasar_pembobotan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_bobot');
    }
};
