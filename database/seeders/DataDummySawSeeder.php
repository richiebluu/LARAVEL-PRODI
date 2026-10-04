<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Prestasi;
use App\Models\StaffProdi;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataDummySawSeeder extends Seeder
{
    private const TINGKAT = [
        'Kampus' => 'Internal',
        'Internal' => 'Internal',
        'Regional' => 'Regional',
        'Nasional' => 'Nasional',
        'Internasional' => 'Internasional',
    ];

    private ?int $verifikatorId = null;

    public function run(): void
    {
        $data = require __DIR__.'/data/data_dummy_saw.php';
        $password = env('DUMMY_PASSWORD', 'password123');
        $dibuat = 0;

        $this->verifikatorId = StaffProdi::query()->value('id_staff_prodi');

        DB::transaction(function () use ($data, $password, &$dibuat) {
            foreach ($data as $row) {
                if (Mahasiswa::where('nim', $row['nim'])->exists()) {
                    continue;
                }

                $email = $row['nim'].'@mhs.politala.ac.id';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    ['name' => $row['nama'], 'password' => $password, 'role' => 'mahasiswa']
                );

                $mahasiswa = Mahasiswa::create([
                    'nim' => $row['nim'],
                    'user_id' => $user->id_user,
                    'nama' => $row['nama'],
                    'angkatan' => 2000 + (int) substr($row['nim'], 0, 2),
                    'email' => $email,
                    'ipk' => $row['ipk'],
                    'status_mahasiswa' => Mahasiswa::STATUS_AKTIF,
                ]);

                $this->buatPrestasi($mahasiswa, Prestasi::KATEGORI_AKADEMIK,
                    $row['pa_tingkat'], $row['pa_deskripsi'], (int) $row['pa_tambahan']);

                $this->buatPrestasi($mahasiswa, Prestasi::KATEGORI_NON_AKADEMIK,
                    $row['pna_tingkat'], $row['pna_deskripsi'], (int) $row['pna_tambahan']);

                foreach ([1, 2] as $i) {
                    $nama = $row['organisasi_'.$i];
                    $jabatan = $row['jabatan_'.$i];

                    if (blank($nama) || blank($jabatan) || $jabatan === 'Tidak Ada') {
                        continue;
                    }

                    Organisasi::create([
                        'nim' => $mahasiswa->nim,
                        'nama_organisasi' => $nama,
                        'jabatan' => $jabatan,
                    ]);
                }

                $dibuat++;
            }
        });

        $jumlah = app(RankingService::class)->simpan((int) date('Y'));

        $this->command?->info("Data dummy Excel: {$dibuat} mahasiswa baru, ranking {$jumlah} mahasiswa dihitung.");
    }

    private function buatPrestasi(Mahasiswa $m, string $kategori, ?string $tingkatExcel, ?string $deskripsi, int $tambahan): void
    {
        if (blank($tingkatExcel) || $tingkatExcel === 'Tidak Ada') {
            return;
        }

        $tingkatUtama = self::TINGKAT[$tingkatExcel] ?? 'Internal';
        $judul = array_values(array_filter(array_map('trim', explode(';', (string) $deskripsi)), fn ($j) => $j !== '' && $j !== '-'));

        for ($i = 0; $i <= $tambahan; $i++) {
            $teks = $judul[$i] ?? ($kategori.' tambahan '.$i);

            Prestasi::create([
                'nim' => $m->nim,
                'staff_prodi_id' => $this->verifikatorId,
                'judul' => $teks,
                'kategori' => $kategori,
                'tingkat' => $i === 0 ? $tingkatUtama : $this->tingkatDariTeks($teks, $tingkatUtama),
                'status' => Prestasi::STATUS_DISETUJUI,
            ]);
        }
    }

    private function tingkatDariTeks(string $teks, string $bawaan): string
    {
        $t = mb_strtolower($teks);

        return match (true) {
            str_contains($t, 'internasional') => 'Internasional',
            str_contains($t, 'nasional') => 'Nasional',
            str_contains($t, 'wilayah'), str_contains($t, 'regional') => 'Regional',
            str_contains($t, 'kampus'), str_contains($t, 'internal') => 'Internal',
            default => $bawaan,
        };
    }
}
