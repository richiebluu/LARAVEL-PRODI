<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospek_lulusan', function (Blueprint $table) {
            $table->id('id_prospek_lulusan');
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->string('ikon', 50)->default('fa-briefcase');
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospek_lulusan');
    }
};
