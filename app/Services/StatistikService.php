<?php

namespace App\Services;

use App\Models\Berita;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prestasi;
use App\Models\ProgramStudi;

class StatistikService
{
    public function mahasiswaAktif(): int
    {
        return Mahasiswa::aktif()->count();
    }

    public function totalMahasiswa(): int
    {
        return Mahasiswa::count();
    }

    public function alumni(): int
    {
        $dariMahasiswa = Mahasiswa::alumni()->count();

        if ($dariMahasiswa > 0) {
            return $dariMahasiswa;
        }

        return (int) (ProgramStudi::query()->value('jumlah_alumni') ?? 0);
    }

    public function dosenAktif(): int
    {
        return Dosen::aktif()->count();
    }

    public function totalDosen(): int
    {
        return Dosen::count();
    }

    public function prestasiDisetujui(): int
    {
        return Prestasi::disetujui()->count();
    }

    public function prestasiMenunggu(): int
    {
        return Prestasi::menunggu()->count();
    }

    public function mahasiswaBerprestasi(): int
    {
        return Prestasi::disetujui()->distinct('nim')->count('nim');
    }

    public function beritaTerbit(): int
    {
        return Berita::terbit()->count();
    }

    public function capaianProdi(): array
    {
        return [
            'mahasiswa' => $this->mahasiswaAktif(),
            'alumni' => $this->alumni(),
            'prestasi' => $this->prestasiDisetujui(),
            'dosen' => $this->dosenAktif(),
            'berprestasi' => $this->mahasiswaBerprestasi(),
        ];
    }

    public function ringkasanStaff(): array
    {
        return [
            'mahasiswa' => $this->totalMahasiswa(),
            'dosen' => $this->totalDosen(),
            'prestasiDisetujui' => $this->prestasiDisetujui(),
            'prestasiMenunggu' => $this->prestasiMenunggu(),
            'beritaTerbit' => $this->beritaTerbit(),
            'mahasiswaBerprestasi' => $this->mahasiswaBerprestasi(),
        ];
    }
}
