<?php

namespace Database\Seeders;

use App\Models\StaffProdi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Membuat SATU akun Staff Prodi sebagai pintu masuk pertama sistem.
 * Bukan data dummy bisnis: tanpa akun ini tidak ada yang bisa login
 * untuk menginput mahasiswa/dosen.
 *
 * Nilai dapat diatur lewat .env:
 *   STAFF_EMAIL, STAFF_PASSWORD, STAFF_NAMA, STAFF_NIP
 */
class StaffProdiSeeder extends Seeder
{
    /** Nama Staff Prodi beserta gelar (gelar A.Md wajib dipertahankan). */
    public const NAMA_DEFAULT = 'Sylvi, A.Md';

    public function run(): void
    {
        $email = env('STAFF_EMAIL', 'staff@politala.ac.id');
        $nip = env('STAFF_NIP', '198001012005011001');

        $nama = env('STAFF_NAMA', self::NAMA_DEFAULT);

        if ($user = User::where('email', $email)->first()) {
            // Akun lama masih memakai nama bawaan lama -> sesuaikan ke nama resmi
            // (gelar A.Md dipertahankan apa adanya).
            if ($user->name === 'Staff Prodi Teknologi Informasi' && $nama !== $user->name) {
                $user->update(['name' => $nama]);
                $user->staffProdi?->update(['nama' => $nama]);
                $this->command?->info('Nama akun Staff Prodi diperbarui menjadi: '.$nama);
            } else {
                $this->command?->warn('Akun Staff Prodi "'.$email.'" sudah ada. Dilewati.');
            }

            return;
        }

        DB::transaction(function () use ($email, $nip, $nama) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => env('STAFF_PASSWORD', 'password123'),
                'role' => 'staff',
            ]);

            // ERD: USERS (1) -- MEMILIKI -- (1) STAFF_PRODI lewat staff_prodi.id_user.
            StaffProdi::create([
                'id_user' => $user->id_user,
                'nip' => $nip,
                'nama' => $user->name,
                'jabatan' => 'Staff Prodi',
                'email' => $user->email,
            ]);
        });

        $this->command?->info('Akun Staff Prodi dibuat: '.$email);
    }
}
