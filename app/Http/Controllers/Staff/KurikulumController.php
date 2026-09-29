<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * DATA MASTER — Kurikulum / Mata Kuliah (REVISI 28-09-2026).
 * Data disesuaikan dengan mata kuliah di SIPADU: Staff Prodi dapat menginput satu per satu
 * atau mengimpor file CSV (ekspor/salinan data SIPADU) agar tidak mengetik ulang.
 */
class KurikulumController extends Controller
{
    use MengimporCsv;

    /** Kolom CSV yang diharapkan (urutan sama dengan template). */
    private const KOLOM_CSV = ['kode', 'nama', 'semester', 'sks', 'jenis'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $semester = (int) $request->query('semester');

        $mataKuliah = MataKuliah::query()
            ->cari($cari)
            ->when($semester > 0, fn ($q) => $q->where('semester', $semester))
            ->urut()
            ->paginate(20)
            ->withQueryString();

        return view('staff-kurikulum', [
            'daftarMataKuliah' => $mataKuliah,
            'cari' => $cari,
            'semester' => $semester ?: null,
            'jumlah' => $mataKuliah->total(),
            'totalSks' => (int) MataKuliah::sum('sks'),
            'daftarJenis' => MataKuliah::JENIS,
            'semesterMaks' => MataKuliah::SEMESTER_MAKS,
        ]);
    }

    public function store(Request $request)
    {
        MataKuliah::create($this->validasi($request->all()));

        return redirect()->route('staff-kurikulum')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $mataKuliah->update($this->validasi($request->all(), $mataKuliah));

        return redirect()->route('staff-kurikulum')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $nama = $mataKuliah->nama;
        $mataKuliah->delete();

        return redirect()->route('staff-kurikulum')->with('success', 'Mata kuliah "'.$nama.'" berhasil dihapus.');
    }

    /** Unduh template CSV (kolom: kode, nama, semester, sks, jenis). */
    public function template()
    {
        return ImporCsv::template('template-kurikulum.csv', self::KOLOM_CSV);
    }

    /**
     * Impor CSV mata kuliah (alur bersama: App\Http\Controllers\Concerns\MengimporCsv).
     * Kode yang sudah ada dilewati atau diperbarui sesuai pilihan Staff Prodi; kode baru ditambahkan.
     * Seluruh baris divalidasi lebih dulu; bila ada yang salah, tidak ada data yang disimpan.
     */
    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => self::KOLOM_CSV,
            // Judul kolom alternatif yang umum pada salinan data SIPADU.
            'alias' => ['kode_mk' => 'kode', 'kode_matakuliah' => 'kode', 'kode_mata_kuliah' => 'kode',
                'nama_mk' => 'nama', 'nama_matakuliah' => 'nama', 'nama_mata_kuliah' => 'nama',
                'jenis_mk' => 'jenis', 'jenis_mata_kuliah' => 'jenis', 'smt' => 'semester'],
            'label' => 'mata kuliah',
            'route' => 'staff-kurikulum',
            'siapkan' => function (array $d) {
                // Jenis tidak peka huruf besar/kecil ("wajib" -> "Wajib").
                $d['jenis'] = ImporCsv::cocokkan($d['jenis'], MataKuliah::JENIS);
                $d['kode'] = $d['kode'] === null ? null : strtoupper($d['kode']);

                return $d;
            },
            'kunci' => fn (array $d) => $d['kode'],
            'cari' => fn (string $kode) => MataKuliah::where('kode', $kode)->first(),
            'aturan' => fn (?MataKuliah $lama) => $this->aturan($lama),
            'pesan' => ['kode.regex' => 'Kode mata kuliah hanya boleh berisi huruf, angka, titik, atau tanda hubung.'],
            'atribut' => $this->atribut(),
            'simpan' => function (array $data, ?MataKuliah $lama) {
                ($lama ?? new MataKuliah)->fill($this->rapikan($data))->save();
            },
        ]);
    }

    private function validasi(array $input, ?MataKuliah $mk = null): array
    {
        return $this->rapikan(Validator::make($input, $this->aturan($mk), [
            'kode.unique' => 'Kode mata kuliah tersebut sudah terdaftar.',
            'kode.regex' => 'Kode mata kuliah hanya boleh berisi huruf, angka, titik, atau tanda hubung.',
        ], $this->atribut())->validate());
    }

    private function aturan(?MataKuliah $mk = null): array
    {
        $kode = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9.\-]+$/', Rule::unique('mata_kuliah', 'kode')->ignore($mk?->id)];

        return [
            'kode' => $kode,
            'nama' => ['required', 'string', 'max:150'],
            'semester' => ['required', 'integer', 'min:1', 'max:'.MataKuliah::SEMESTER_MAKS],
            'sks' => ['required', 'integer', 'min:1', 'max:24'],
            'jenis' => ['required', Rule::in(MataKuliah::JENIS)],
        ];
    }

    private function atribut(): array
    {
        return [
            'kode' => 'Kode mata kuliah',
            'nama' => 'Nama mata kuliah',
            'semester' => 'Semester',
            'sks' => 'SKS',
            'jenis' => 'Jenis mata kuliah',
        ];
    }

    private function rapikan(array $data): array
    {
        $data['kode'] = strtoupper(trim($data['kode']));
        $data['nama'] = trim($data['nama']);

        return $data;
    }
}
