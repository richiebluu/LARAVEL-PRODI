<?php

namespace App\Support;

class HeroFoto
{
    public const FOTO_GTI = [
        'kampus' => 'https://cdn.corenexis.com/f/YVG0IKiag9T.jpg',
        'prestasi' => 'https://cdn.corenexis.com/f/mxRMXHq0qkL.png',
        'mahasiswa' => 'https://cdn.corenexis.com/f/vuQbJbgT58Y.jpg',
    ];

    public const HALAMAN = [
        'beranda-1' => ['kampus', 'center'],
        'beranda-2' => ['prestasi', 'center'],
        'beranda-3' => ['mahasiswa', 'center'],
        'profil' => ['kampus', 'center 35%'],
        'akreditasi' => ['kampus', 'center 20%'],
        'struktur-organisasi' => ['mahasiswa', 'center 30%'],
        'dosen' => ['mahasiswa', 'center 60%'],
        'mata-kuliah' => ['kampus', 'center 70%'],
        'sarana-prasarana' => ['kampus', 'center 50%'],
        'prospek-lulusan' => ['mahasiswa', 'center 40%'],
        'mahasiswa-berprestasi' => ['prestasi', 'center 40%'],
        'ranking' => ['prestasi', 'center 65%'],
        'testimoni' => ['mahasiswa', 'center 75%'],
        'berita' => ['kampus', 'center 60%'],
        'berita-detail' => ['kampus', 'center 45%'],
        'lowongan-pekerjaan' => ['mahasiswa', 'center 20%'],
        'akamawa' => ['prestasi', 'center 25%'],
        'kode-etik' => ['kampus', 'center 80%'],
        'pengumuman' => ['prestasi', 'center 55%'],
    ];

    private const EKSTENSI = ['webp', 'jpg', 'jpeg', 'png'];

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

    public static function style(string $halaman, ?string $fotoKonten = null): string
    {
        $url = str_replace(["'", '"', '(', ')'], ['%27', '%22', '%28', '%29'], self::url($halaman, $fotoKonten));
        $posisi = self::HALAMAN[$halaman][1] ?? 'center';

        return "--hero-foto:url('".$url."');--hero-posisi:".$posisi;
    }
}
