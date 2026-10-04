<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sarana_prasarana', function (Blueprint $table) {
            $table->id('id_sarana_prasarana');
            $table->foreignId('staff_prodi_id')->nullable()->constrained('staff_prodi', 'id_staff_prodi')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('gedung', 100)->nullable();
            $table->unsignedSmallInteger('kapasitas')->nullable();
            $table->text('fasilitas')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarana_prasarana');
    }
};
