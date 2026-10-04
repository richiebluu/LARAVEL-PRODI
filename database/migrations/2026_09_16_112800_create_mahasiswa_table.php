<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->string('status_mahasiswa', 20)->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
