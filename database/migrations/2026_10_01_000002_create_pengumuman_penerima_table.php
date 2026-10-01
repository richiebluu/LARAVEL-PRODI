<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * REVISI DOSEN 01-10-2026 — SATU PENGUMUMAN UNTUK BANYAK MAHASISWA.
 *
 *   PENGUMUMAN (1) --< PENGUMUMAN_PENERIMA >-- (1) MAHASISWA
 *
 * Tabel pivot `pengumuman_penerima` (many-to-many) menyimpan setiap mahasiswa
 * penerima satu pengumuman beserta status baca notifikasinya masing-masing
 * (`dibaca_pada`). Kolom lama `pengumuman.nim` TETAP dipakai untuk pesan
 * sistem (status = 'notifikasi', mis. hasil verifikasi prestasi) yang memang
 * hanya untuk satu mahasiswa.
 *
 * Data lama (pengumuman Staff dengan satu penerima di kolom nim) dipindahkan ke
 * tabel pivot sehingga tidak ada pengumuman yang hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengumuman_penerima')) {
            Schema::create('pengumuman_penerima', function (Blueprint $table) {
                $table->id('id_pengumuman_penerima');
                $table->foreignId('pengumuman_id')
                    ->constrained('pengumuman', 'id_pengumuman')->cascadeOnDelete();
                $table->string('nim', 30);
                $table->foreign('nim')->references('nim')->on('mahasiswa')
                    ->cascadeOnUpdate()->cascadeOnDelete();
                $table->timestamp('dibaca_pada')->nullable(); // status baca per mahasiswa
                $table->timestamps();

                $table->unique(['pengumuman_id', 'nim']); // mahasiswa tidak bisa dipilih dua kali
                $table->index(['nim', 'dibaca_pada']);
            });
        }

        // Pindahkan penerima lama (pengumuman Staff, bukan pesan sistem) ke tabel pivot.
        $lama = DB::table('pengumuman')
            ->where('status', '!=', 'notifikasi')
            ->whereNotNull('nim')
            ->get(['id_pengumuman', 'nim', 'dibaca_pada', 'created_at', 'updated_at']);

        foreach ($lama as $g) {
            DB::table('pengumuman_penerima')->insertOrIgnore([
                'pengumuman_id' => $g->id_pengumuman,
                'nim' => $g->nim,
                'dibaca_pada' => $g->dibaca_pada,
                'created_at' => $g->created_at ?? now(),
                'updated_at' => $g->updated_at ?? now(),
            ]);
        }

        DB::table('pengumuman')
            ->where('status', '!=', 'notifikasi')
            ->whereNotNull('nim')
            ->update(['nim' => null, 'dibaca_pada' => null]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pengumuman_penerima')) {
            return;
        }

        // Kembalikan penerima pertama ke kolom pengumuman.nim sebelum tabel dihapus.
        $pertama = DB::table('pengumuman_penerima')
            ->orderBy('id_pengumuman_penerima')
            ->get()
            ->unique('pengumuman_id');

        foreach ($pertama as $p) {
            DB::table('pengumuman')
                ->where('id_pengumuman', $p->pengumuman_id)
                ->update(['nim' => $p->nim, 'dibaca_pada' => $p->dibaca_pada]);
        }

        Schema::dropIfExists('pengumuman_penerima');
    }
};
