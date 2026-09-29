<?php

namespace App\Services;

use App\Models\Pengumuman;

/**
 * Notifikasi pribadi mahasiswa.
 *
 * Sesuai ERD terbaru tidak ada tabel `notifikasi` terpisah: atribut `notifikasi`
 * dan `dibaca_pada` berada pada entitas PENGUMUMAN, dan penerimanya ditentukan
 * lewat relasi MAHASISWA (1) -- MENERIMA -- (N) PENGUMUMAN (kolom pengumuman.nim).
 *
 * Pesan sistem (mis. status verifikasi prestasi) disimpan sebagai baris
 * pengumuman berstatus "notifikasi" sehingga hanya tampil di menu Notifikasi,
 * tidak di daftar Pengumuman. Tidak pernah memakai localStorage.
 */
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
