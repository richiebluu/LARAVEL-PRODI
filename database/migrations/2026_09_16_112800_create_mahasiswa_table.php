<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * MAHASISWA (ERD): nim (PK), user_id (FK), nama, foto, angkatan, kelas,
         * email, no_hp, ipk, status_mahasiswa, created_at, updated_at.
         * USERS (1) -- MEMILIKI -- (1) MAHASISWA.
         */
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim', 30)->primary();
            $table->foreignId('user_id')->unique()->constrained('users', 'id_user')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('foto')->nullable();
            $table->year('angkatan')->nullable();
            $table->string('kelas', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->decimal('ipk', 3, 2)->nullable();
            // aktif | alumni | cuti | nonaktif | do | dispen
            $table->string('status_mahasiswa', 20)->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
