<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ImporCsv;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * IMPOR CSV (REVISI 28-09-2026 tahap 2 — "semua halaman yang memiliki CRUD ditambah fitur
 * import csv seperti di Kurikulum, sesuaikan halaman mana yang butuh").
 *
 * Satu alur yang sama untuk semua halaman Data Master yang memakai impor CSV:
 *   1. Validasi berkas: wajib, .csv/.txt, maks 2 MB.
 *   2. Validasi judul kolom (kolom wajib harus ada — gunakan template).
 *   3. Validasi SETIAP baris memakai aturan yang sama dengan form tambah/edit.
 *   4. Data ganda di dalam file ditolak; data yang sudah ada di database
 *      DILEWATI (bawaan, data lama aman) atau DIPERBARUI sesuai pilihan Staff Prodi.
 *   5. Bila ada satu saja baris bermasalah, impor DIBATALKAN seluruhnya
 *      (tidak ada data setengah tersimpan) dan Staff Prodi melihat daftar baris yang salah.
 *   6. Semua penyimpanan dalam satu transaksi database.
 */
trait MengimporCsv
{
    /**
     * @param  array{
     *   kolom: array<int,string>, wajib: array<int,string>, label: string, route: string,
     *   kunci: callable, cari: callable, aturan: callable, simpan: callable,
     *   siapkan?: callable, alias?: array<string,string>, pesan?: array, atribut?: array,
     *   unik_file?: array<int,string>
     * }  $cfg
     */
    protected function prosesImporCsv(Request $request, array $cfg): RedirectResponse
    {
        $request->validate([
            'berkas' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'duplikat' => ['nullable', 'in:lewati,perbarui'],
        ], [
            'berkas.required' => 'Pilih file CSV yang akan diimpor.',
            'berkas.file' => 'Berkas gagal diunggah. Coba pilih ulang file CSV.',
            'berkas.mimes' => 'File harus berformat CSV (.csv). Dari Excel: Simpan Sebagai > CSV (Comma delimited).',
            'berkas.max' => 'Ukuran file CSV maksimal 2 MB.',
        ]);

        $perbarui = $request->input('duplikat') === 'perbarui';
        $alias = $cfg['alias'] ?? [];
        $kolomBaca = array_values(array_unique(array_merge($cfg['kolom'], array_keys($alias))));
        $csv = ImporCsv::baca($request->file('berkas')->getRealPath(), $kolomBaca);

        // Judul kolom alternatif (mis. "kode_mk" dari SIPADU) diarahkan ke kolom resmi.
        $judul = array_map(fn ($j) => $alias[$j] ?? $j, $csv['judul']);
        $hilang = array_values(array_diff($cfg['wajib'], $judul));
        if ($csv['judul'] === [] || $hilang) {
            return $this->gagalImpor([
                'Judul kolom tidak sesuai template. Kolom wajib yang tidak ditemukan: '
                .implode(', ', $hilang ?: $cfg['wajib']).'. Unduh template CSV lalu salin data ke kolom yang sesuai.',
            ]);
        }

        $baris = $csv['baris'];
        if ($baris === []) {
            return $this->gagalImpor(['File CSV tidak memiliki baris data (hanya judul kolom).']);
        }
        if (count($baris) > ImporCsv::MAKS_BARIS) {
            return $this->gagalImpor(['Maksimal '.ImporCsv::MAKS_BARIS.' baris data per sekali impor. Bagi file menjadi beberapa bagian.']);
        }

        $siap = [];
        $kesalahan = [];
        $dilewati = 0;
        $kunciDipakai = [];
        $unikFile = [];

        foreach ($baris as $nomor => $data) {
            foreach ($alias as $dari => $ke) {
                if (($data[$ke] ?? null) === null && ($data[$dari] ?? null) !== null) {
                    $data[$ke] = $data[$dari];
                }
                unset($data[$dari]);
            }

            if (isset($cfg['siapkan'])) {
                $data = ($cfg['siapkan'])($data);
            }

            $kunci = ($cfg['kunci'])($data);
            if ($kunci !== null && $kunci !== '' && isset($kunciDipakai[$kunci])) {
                $kesalahan[] = 'Baris '.$nomor.': data "'.$kunci.'" muncul lebih dari sekali di file (baris '.$kunciDipakai[$kunci].').';

                continue;
            }
            if ($kunci !== null && $kunci !== '') {
                $kunciDipakai[$kunci] = $nomor;
            }

            $lama = ($kunci !== null && $kunci !== '') ? ($cfg['cari'])($kunci) : null;
            if ($lama && ! $perbarui) {
                $dilewati++;

                continue;
            }

            foreach ($cfg['unik_file'] ?? [] as $kolom) {
                $nilai = strtolower((string) ($data[$kolom] ?? ''));
                if ($nilai === '') {
                    continue;
                }
                if (isset($unikFile[$kolom][$nilai])) {
                    $kesalahan[] = 'Baris '.$nomor.': '.($cfg['atribut'][$kolom] ?? $kolom).' "'.$data[$kolom].'" sudah dipakai baris '.$unikFile[$kolom][$nilai].'.';

                    continue 2;
                }
                $unikFile[$kolom][$nilai] = $nomor;
            }

            $v = Validator::make($data, ($cfg['aturan'])($lama, $data), $cfg['pesan'] ?? [], $cfg['atribut'] ?? []);
            if ($v->fails()) {
                $kesalahan[] = 'Baris '.$nomor.': '.implode(' ', $v->errors()->all());

                continue;
            }

            $siap[] = [$v->validated(), $lama, $data];
        }

        if ($kesalahan) {
            $jumlah = count($kesalahan);
            $tampil = array_slice($kesalahan, 0, 10);
            if ($jumlah > 10) {
                $tampil[] = '... dan '.($jumlah - 10).' baris bermasalah lainnya.';
            }

            return $this->gagalImpor(array_merge(
                ['Impor dibatalkan, tidak ada data yang disimpan: '.$jumlah.' dari '.count($baris).' baris bermasalah. Perbaiki baris berikut lalu impor ulang.'],
                $tampil,
            ));
        }

        [$baru, $diperbarui] = DB::transaction(function () use ($siap, $cfg) {
            $baru = 0;
            $diperbarui = 0;
            foreach ($siap as [$valid, $lama, $mentah]) {
                ($cfg['simpan'])($valid, $lama, $mentah);
                $lama ? $diperbarui++ : $baru++;
            }

            return [$baru, $diperbarui];
        });

        $bagian = [$baru.' '.$cfg['label'].' ditambahkan'];
        if ($diperbarui > 0) {
            $bagian[] = $diperbarui.' diperbarui';
        }
        if ($dilewati > 0) {
            $bagian[] = $dilewati.' dilewati (sudah terdaftar)';
        }

        return redirect()->route($cfg['route'])->with('success', 'Impor selesai: '.implode(', ', $bagian).'.');
    }

    private function gagalImpor(array $pesan): RedirectResponse
    {
        return back()->withErrors(['berkas' => $pesan])->with('impor_gagal', true);
    }
}
