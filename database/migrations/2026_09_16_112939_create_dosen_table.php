<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * DOSEN (ERD): nuptk (PK), nama, foto, pendidikan_terakhir, google_scholar,
         * email, alamat, tanggal_lahir, status, created_at, updated_at.
         * Identitas dosen = NUPTK (bukan NIDN). Dosen adalah Data Master (tanpa akun login).
         */
        Schema::create('dosen', function (Blueprint $table) {
            $table->string('nuptk', 30)->primary();
            $table->string('nama', 150);
            $table->string('foto')->nullable();
            $table->text('pendidikan_terakhir')->nullable();
            $table->string('google_scholar')->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->text('alamat')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif | pendidikan | nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
