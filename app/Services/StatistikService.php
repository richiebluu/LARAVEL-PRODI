<?php

namespace App\Services;

use App\Models\Berita;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prestasi;
use App\Models\ProgramStudi;

/**
 * Semua angka statistik dihitung langsung dari tabel aslinya.
 * Tidak ada tabel penampung jumlah dan tidak ada angka hardcode.
 */
class StatistikService
{
    /** Mahasiswa dengan status aktif. */
    public function mahasiswaAktif(): int
    {
        return Mahasiswa::aktif()->count();
    }

    /** Seluruh mahasiswa apa pun statusnya. */
    public function totalMahasiswa(): int
    {
        return Mahasiswa::count();
    }

    /**
     * Alumni.
     * Utama: mahasiswa berstatus alumni.
     * Cadangan: field program_studi.jumlah_alumni yang dikelola Staff Prodi
     * (sesuai ERD, tidak ada tabel alumni terpisah).
     */
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

    /** Prestasi yang sudah diverifikasi Staff Prodi. */
    public function prestasiDisetujui(): int
    {
        return Prestasi::disetujui()->count();
    }

    public function prestasiMenunggu(): int
    {
        return Prestasi::menunggu()->count();
    }

    /** Jumlah mahasiswa unik yang memiliki minimal satu prestasi disetujui. */
    public function mahasiswaBerprestasi(): int
    {
        return Prestasi::disetujui()->distinct('nim')->count('nim');
    }

    /** Berita Program Studi yang sudah terbit. */
    public function beritaTerbit(): int
    {
        return Berita::terbit()->count();
    }

    /** Ringkasan "Capaian Prodi" pada halaman publik. */
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

    /** Ringkasan kartu Dashboard Staff Prodi. */
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
