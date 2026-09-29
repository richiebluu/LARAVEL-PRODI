<?php

namespace Database\Seeders;

use App\Services\RankingService;
use Illuminate\Database\Seeder;

/**
 * Mengisi 4 baris kriteria ranking dengan BOBOT TETAP sesuai Excel acuan
 * (sheet "Bobot Kriteria"):
 *   C1 Nilai Akademik 0.35 · C2 Prestasi Akademik 0.30 ·
 *   C3 Prestasi Non-Akademik 0.20 · C4 Keaktifan Organisasi 0.15.
 *
 * Bobot tidak dapat diubah melalui interface website (revisi dosen).
 */
class RankingBobotSeeder extends Seeder
{
    public function run(): void
    {
        app(RankingService::class)->sinkronBobot();
    }
}
