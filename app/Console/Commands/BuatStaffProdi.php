<?php

namespace App\Console\Commands;

use App\Models\StaffProdi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Membuat akun Staff Prodi baru secara interaktif.
 *
 *   php artisan staff:buat
 *   php artisan staff:buat --email=a@b.c --nama="Nama" --nip=123 --password=rahasia123
 */
class BuatStaffProdi extends Command
{
    protected $signature = 'staff:buat
                            {--nama= : Nama lengkap staff}
                            {--email= : Email untuk login}
                            {--nip= : NIP staff}
                            {--password= : Password login (minimal 8 karakter)}';

    protected $description = 'Membuat akun Staff Prodi baru (users + staff_prodi)';

    public function handle(): int
    {
        $nama = $this->option('nama') ?: $this->ask('Nama lengkap staff');
        $email = $this->option('email') ?: $this->ask('Email untuk login');
        $nip = $this->option('nip') ?: $this->ask('NIP');
        $password = $this->option('password') ?: $this->secret('Password (minimal 8 karakter)');

        $validator = Validator::make(
            compact('nama', 'email', 'nip', 'password'),
            [
                'nama' => ['required', 'string', 'max:150'],
                // Email Staff Prodi wajib email institusi (login dibatasi sesuai role).
                'email' => ['required', 'email', 'max:150', 'unique:users,email', User::aturanDomainEmail('staff')],
                'nip' => ['required', 'string', 'max:30', 'unique:staff_prodi,nip'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($nama, $email, $nip, $password) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => $password,
                'role' => 'staff',
            ]);

            StaffProdi::create([
                'id_user' => $user->id_user,
                'nip' => $nip,
                'nama' => $nama,
                'jabatan' => 'Staff Prodi',
                'email' => $email,
            ]);
        });

        $this->info('Akun Staff Prodi berhasil dibuat.');
        $this->line('  Email : '.$email);
        $this->line('  Login : '.config('app.url').'/login');

        return self::SUCCESS;
    }
}
