<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * USERS (ERD): id_user (PK), name, email, google_id, email_verified_at,
         * password, role, remember_token, created_at, updated_at.
         *
         * Relasi ERD: USERS (1) -- MEMILIKI -- (1) MAHASISWA  -> mahasiswa.user_id
         *             USERS (1) -- MEMILIKI -- (1) STAFF_PRODI -> staff_prodi.id_user
         * Role login: mahasiswa | staff (Staff Prodi). Free User (publik) tidak login.
         */
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('google_id')->nullable()->unique(); // Login dengan Google (Socialite)
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['mahasiswa', 'staff']);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index(); // berisi users.id_user (bawaan Laravel session)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
