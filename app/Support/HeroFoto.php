<?php

namespace App\Support;

/**
 * REVISI DOSEN 01-10-2026 — foto/visual GTI (Teknologi Informasi) pada HERO halaman publik.
 *
 * Urutan sumber foto untuk satu halaman:
 *  1. Foto khusus halaman di public/images/hero/{halaman}.(webp|jpg|jpeg|png) — bila Staff/tim
 *     menaruh foto GTI sendiri dengan nama tersebut, foto itu yang dipakai.
 *  2. Foto konten dari database (mis. foto berita, foto laboratorium, foto kegiatan) bila dikirim.
 *  3. Foto GTI yang SUDAH dipakai project pada hero slider Beranda (FOTO_GTI), dengan
 *     posisi potong berbeda per halaman agar tidak monoton.
 *
 * Desain hero tidak berubah: Blade hanya menambah kelas `page-hero--foto` dan variabel CSS
 * `--hero-foto`/`--hero-posisi`; overlay warna diatur di public/css/app.css.
 */
class HeroFoto
{
    /** Foto GTI yang sudah ada di project (hero slider Beranda). */
    public const FOTO_GTI = [
        'kampus' => 'https://cdn.corenexis.com/f/YVG0IKiag9T.jpg',     // slide 1: Program Studi TI
        'prestasi' => 'https://cdn.corenexis.com/f/mxRMXHq0qkL.png',   // slide 2: Prestasi Mahasiswa
        'mahasiswa' => 'https://cdn.corenexis.com/f/vuQbJbgT58Y.jpg',  // slide 3: Bergabunglah
    ];

    /** Halaman publik -> [foto GTI bawaan, posisi background]. */
    public const HALAMAN = [
        'beranda-1' => ['kampus', 'center'],
        'beranda-2' => ['prestasi', 'center'],
        'beranda-3' => ['mahasiswa', 'center'],
        'profil' => ['kampus', 'center 35%'],
        'akreditasi' => ['kampus', 'center 20%'],
        'struktur-organisasi' => ['mahasiswa', 'center 30%'],
        'dosen' => ['mahasiswa', 'center 60%'],
        'kurikulum' => ['kampus', 'center 70%'],
        'sarana-prasarana' => ['kampus', 'center 50%'],
        'prospek-lulusan' => ['mahasiswa', 'center 40%'],
        'mahasiswa-berprestasi' => ['prestasi', 'center 40%'],
        'ranking' => ['prestasi', 'center 65%'],
        'kegiatan-mahasiswa' => ['mahasiswa', 'center 50%'],
        'testimoni' => ['mahasiswa', 'center 75%'],
        'berita' => ['kampus', 'center 60%'],
        'berita-detail' => ['kampus', 'center 45%'],
        'lowongan-pekerjaan' => ['mahasiswa', 'center 20%'],
        'akamawa' => ['prestasi', 'center 25%'],
        'kode-etik' => ['kampus', 'center 80%'],
        'pengumuman' => ['prestasi', 'center 55%'],
    ];

    /** Ekstensi foto lokal yang dicari di public/images/hero. */
    private const EKSTENSI = ['webp', 'jpg', 'jpeg', 'png'];

    /** URL foto hero untuk sebuah halaman. */
    public static function url(string $halaman, ?string $fotoKonten = null): string
    {
        foreach (self::EKSTENSI as $ext) {
            $relatif = 'images/hero/'.$halaman.'.'.$ext;
            if (is_file(public_path($relatif))) {
                return asset($relatif);
            }
        }

        if (filled($fotoKonten)) {
            return $fotoKonten;
        }

        $kunci = self::HALAMAN[$halaman][0] ?? 'kampus';

        return self::FOTO_GTI[$kunci];
    }

    /**
     * Isi atribut style untuk <section class="page-hero page-hero--foto">.
     * Contoh: --hero-foto:url('...');--hero-posisi:center 35%
     */
    public static function style(string $halaman, ?string $fotoKonten = null): string
    {
        $url = str_replace(["'", '"', '(', ')'], ['%27', '%22', '%28', '%29'], self::url($halaman, $fotoKonten));
        $posisi = self::HALAMAN[$halaman][1] ?? 'center';

        return "--hero-foto:url('".$url."');--hero-posisi:".$posisi;
    }
}
