<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_prodi', function (Blueprint $table) {
            $table->id('id_staff_prodi');
            $table->foreignId('id_user')->unique()->constrained('users', 'id_user')->cascadeOnDelete();
            $table->string('nip', 30)->unique();
            $table->string('nama', 150);
            $table->string('foto')->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_prodi');
    }
};
