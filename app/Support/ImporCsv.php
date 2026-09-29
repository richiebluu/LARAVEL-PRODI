<?php

namespace App\Support;

/**
 * Pembaca file CSV untuk fitur Impor CSV Staff Prodi (REVISI 28-09-2026 tahap 2).
 *
 * - Baris pertama = judul kolom (tidak peka huruf besar/kecil, spasi -> garis bawah).
 * - Pemisah koma (,) atau titik koma (;) — Excel versi Indonesia menyimpan dengan titik koma.
 * - BOM UTF-8 dari Excel dibuang; teks non-UTF-8 (ANSI Windows) dikonversi ke UTF-8.
 * - Baris kosong dilewati. Nomor baris mengikuti nomor baris di file (judul = baris 1).
 */
class ImporCsv
{
    /** Batas jumlah baris data per sekali impor (menjaga server tetap ringan). */
    public const MAKS_BARIS = 1000;

    /**
     * @param  array<int, string>  $kolom  Nama kolom yang dikenali (urutan template).
     * @return array{judul: array<int, string>, baris: array<int, array<string, ?string>>}
     */
    public static function baca(string $path, array $kolom): array
    {
        $isi = (string) file_get_contents($path);
        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi);

        if (! mb_check_encoding($isi, 'UTF-8')) {
            $isi = mb_convert_encoding($isi, 'UTF-8', 'Windows-1252');
        }

        $baris = preg_split('/\r\n|\r|\n/', trim($isi));
        if (! $baris || trim($baris[0]) === '') {
            return ['judul' => [], 'baris' => []];
        }

        $pemisah = substr_count($baris[0], ';') > substr_count($baris[0], ',') ? ';' : ',';
        $judul = array_map([self::class, 'normalJudul'], str_getcsv($baris[0], $pemisah, '"', '\\'));
        $hasil = [];

        foreach (array_slice($baris, 1, null, true) as $i => $b) {
            if (trim(str_replace([$pemisah, '"'], '', $b)) === '') {
                continue; // baris kosong (termasuk ";;;;" dari Excel)
            }

            $sel = str_getcsv($b, $pemisah, '"', '\\');
            $data = [];
            foreach ($kolom as $k) {
                $posisi = array_search($k, $judul, true);
                $nilai = $posisi === false ? null : trim((string) ($sel[$posisi] ?? ''));
                $data[$k] = $nilai === '' ? null : $nilai;
            }
            $hasil[$i + 1] = $data;
        }

        return ['judul' => $judul, 'baris' => $hasil];
    }

    /** "Nama Mata Kuliah " -> "nama_mata_kuliah". */
    public static function normalJudul(string $judul): string
    {
        $judul = strtolower(trim($judul, " \t\"'"));

        return trim((string) preg_replace('/[^a-z0-9]+/', '_', $judul), '_');
    }

    /** Respons unduhan template CSV (hanya baris judul, dengan BOM agar Excel membaca UTF-8). */
    public static function template(string $namaFile, array $kolom)
    {
        return response("\xEF\xBB\xBF".implode(',', $kolom)."\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** Cocokkan nilai dengan daftar pilihan tanpa peka huruf besar/kecil ("wajib" -> "Wajib"). */
    public static function cocokkan(?string $nilai, array $pilihan): ?string
    {
        if ($nilai === null) {
            return null;
        }

        foreach ($pilihan as $kunci => $label) {
            if (strcasecmp((string) $label, $nilai) === 0 || (is_string($kunci) && strcasecmp($kunci, $nilai) === 0)) {
                return is_string($kunci) ? $kunci : $label;
            }
        }

        return $nilai;
    }

    /** Tanggal dari Excel: 2001-12-31, 31/12/2001, 31-12-2001, atau 31.12.2001 -> Y-m-d. */
    public static function tanggal(?string $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $nilai, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]) : $nilai;
        }

        if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{4})$#', $nilai, $m)) {
            return checkdate((int) $m[2], (int) $m[1], (int) $m[3]) ? sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]) : $nilai;
        }

        return $nilai;
    }

    /** Angka desimal gaya Indonesia "3,75" -> "3.75". */
    public static function desimal(?string $nilai): ?string
    {
        return $nilai === null ? null : str_replace(',', '.', $nilai);
    }
}
