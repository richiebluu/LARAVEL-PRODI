<?php

namespace Database\Seeders;

use App\Models\StaffProdi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffProdiSeeder extends Seeder
{
    public const NAMA_DEFAULT = 'Sylvi, A.Md';

    public function run(): void
    {
        $email = env('STAFF_EMAIL', 'staff@politala.ac.id');
        $nip = env('STAFF_NIP', '198001012005011001');

        $nama = env('STAFF_NAMA', self::NAMA_DEFAULT);

        if ($user = User::where('email', $email)->first()) {
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
