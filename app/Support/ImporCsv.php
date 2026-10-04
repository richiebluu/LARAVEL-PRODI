<?php

namespace App\Support;

class ImporCsv
{
    public const MAKS_BARIS = 1000;

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
                continue;
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

    public static function normalJudul(string $judul): string
    {
        $judul = strtolower(trim($judul, " \t\"'"));

        return trim((string) preg_replace('/[^a-z0-9]+/', '_', $judul), '_');
    }

    public static function template(string $namaFile, array $kolom)
    {
        return response("\xEF\xBB\xBF".implode(',', $kolom)."\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

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

    public static function desimal(?string $nilai): ?string
    {
        return $nilai === null ? null : str_replace(',', '.', $nilai);
    }
}
