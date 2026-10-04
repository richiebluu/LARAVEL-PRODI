<?php

namespace App\Services;

use App\Models\Pengumuman;

class NotifikasiService
{
    public function kirim(?string $nim, string $judul, string $pesan, ?string $kategori = null, ?int $staffProdiId = null): ?Pengumuman
    {
        if (blank($nim)) {
            return null;
        }

        return Pengumuman::create([
            'nim' => $nim,
            'staff_prodi_id' => $staffProdiId,
            'kategori' => $kategori,
            'judul' => $judul,
            'isi' => $pesan,
            'notifikasi' => $pesan,
            'status' => Pengumuman::STATUS_NOTIFIKASI,
            'tanggal_dikirim' => now(),
        ]);
    }
}
