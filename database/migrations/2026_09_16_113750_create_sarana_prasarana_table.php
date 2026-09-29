<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * SARANA_PRASARANA (ERD): id_sarana_prasarana (PK), staff_prodi_id (FK), nama, jenis,
         * lokasi, kapasitas, fasilitas, deskripsi, foto, status, created_at, updated_at.
         * STAFF_PRODI (1) -- MENGELOLA -- (N) SARANA_PRASARANA.
         */
        Schema::create('sarana_prasarana', function (Blueprint $table) {
            $table->id('id_sarana_prasarana');
            $table->foreignId('staff_prodi_id')->nullable()
                ->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('jenis', 50);
            $table->string('lokasi', 150)->nullable();
            $table->unsignedSmallInteger('kapasitas')->nullable();
            $table->text('fasilitas')->nullable(); // satu baris = satu fasilitas
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarana_prasarana');
    }
};
