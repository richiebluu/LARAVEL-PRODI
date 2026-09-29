<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder utama.
 *
 *  1. StaffProdiSeeder   -> akun Staff Prodi pertama (Sylvi, A.Md).
 *  2. RankingBobotSeeder -> 4 kriteria SAW dengan bobot tetap.
 *  3. DataDummySawSeeder -> 25 mahasiswa dummy dari file Excel
 *                           "DATA DUMMY MAHASISWA_METODE SAW_KELOMPOK-3.xlsx"
 *                           (revisi dosen 22-09-2026), lalu ranking dihitung.
 *
 * Untuk database tanpa data dummy, jalankan hanya seeder 1 dan 2:
 *   php artisan db:seed --class=StaffProdiSeeder
 *   php artisan db:seed --class=RankingBobotSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StaffProdiSeeder::class,
            RankingBobotSeeder::class,
            DataDummySawSeeder::class,
        ]);
    }
}
