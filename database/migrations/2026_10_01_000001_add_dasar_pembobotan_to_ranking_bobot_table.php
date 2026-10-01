<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * REVISI DOSEN 01-10-2026 — DASAR PEMBOBOTAN.
 * Menambah kolom `dasar_pembobotan` pada tabel ranking_bobot yang SUDAH ADA
 * (tidak membuat tabel baru, data bobot lama tidak diubah). Baris kriteria yang
 * sudah ada langsung diisi teks dasar pembobotan bawaan dari config/saw.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ranking_bobot', 'dasar_pembobotan')) {
            Schema::table('ranking_bobot', function (Blueprint $table) {
                $table->text('dasar_pembobotan')->nullable()->after('tipe_bobot');
            });
        }

        foreach ((array) config('saw.kriteria') as $kode => $k) {
            if (blank($k['dasar'] ?? null)) {
                continue;
            }

            DB::table('ranking_bobot')
                ->where('kode', $kode)
                ->whereNull('dasar_pembobotan')
                ->update(['dasar_pembobotan' => $k['dasar']]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ranking_bobot', 'dasar_pembobotan')) {
            Schema::table('ranking_bobot', function (Blueprint $table) {
                $table->dropColumn('dasar_pembobotan');
            });
        }
    }
};
