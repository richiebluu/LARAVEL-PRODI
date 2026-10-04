<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman_penerima', function (Blueprint $table) {
            $table->id('id_pengumuman_penerima');
            $table->foreignId('pengumuman_id')->constrained('pengumuman', 'id_pengumuman')->cascadeOnDelete();
            $table->string('nim', 30);
            $table->foreign('nim')->references('nim')->on('mahasiswa')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->unique(['pengumuman_id', 'nim']);
            $table->index(['nim', 'dibaca_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman_penerima');
    }
};
