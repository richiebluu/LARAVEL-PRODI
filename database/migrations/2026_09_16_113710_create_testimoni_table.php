<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * TESTIMONI (ERD): id_testimoni (PK), staff_prodi_id (FK), nama, tahun_kelulusan,
         * nama_perusahaan, jabatan, foto, isi, created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) TESTIMONI.
         */
        Schema::create('testimoni', function (Blueprint $table) {
            $table->id('id_testimoni');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama', 150);
            $table->unsignedSmallInteger('tahun_kelulusan')->nullable();
            $table->string('nama_perusahaan', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('foto')->nullable();
            $table->text('isi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimoni');
    }
};
