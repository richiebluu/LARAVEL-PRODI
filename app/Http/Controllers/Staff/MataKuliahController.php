<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MataKuliahController extends Controller
{
    use MengimporCsv;

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

        return view('staff-mata-kuliah', [
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
        $data = $this->validasi($request->all());
        $data['program_studi_id'] = $this->programStudiId();

        MataKuliah::create($data);

        return redirect()->route('staff-mata-kuliah')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $data = $this->validasi($request->all(), $mataKuliah);
        $data['program_studi_id'] = $mataKuliah->program_studi_id ?? $this->programStudiId();

        $mataKuliah->update($data);

        return redirect()->route('staff-mata-kuliah')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $nama = $mataKuliah->nama;
        $mataKuliah->delete();

        return redirect()->route('staff-mata-kuliah')->with('success', 'Mata kuliah "'.$nama.'" berhasil dihapus.');
    }

    public function template()
    {
        return ImporCsv::template('template-mata-kuliah.csv', self::KOLOM_CSV);
    }

    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => self::KOLOM_CSV,
            'alias' => ['kode_mk' => 'kode', 'kode_matakuliah' => 'kode', 'kode_mata_kuliah' => 'kode',
                'nama_mk' => 'nama', 'nama_matakuliah' => 'nama', 'nama_mata_kuliah' => 'nama',
                'jenis_mk' => 'jenis', 'jenis_mata_kuliah' => 'jenis', 'smt' => 'semester'],
            'label' => 'mata kuliah',
            'route' => 'staff-mata-kuliah',
            'siapkan' => function (array $d) {
                $d['jenis'] = ImporCsv::cocokkan($d['jenis'], MataKuliah::JENIS);
                $d['kode'] = $d['kode'] === null ? null : strtoupper($d['kode']);

                return $d;
            },
            'kunci' => fn (array $d) => $d['kode'],
            'cari' => fn (string $kode) => MataKuliah::find($kode),
            'aturan' => fn (?MataKuliah $lama) => $this->aturan($lama),
            'pesan' => ['kode.regex' => 'Kode mata kuliah hanya boleh berisi huruf, angka, titik, atau tanda hubung.'],
            'atribut' => $this->atribut(),
            'simpan' => function (array $data, ?MataKuliah $lama) {
                $data['program_studi_id'] = $lama?->program_studi_id ?? $this->programStudiId();
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

    private function programStudiId(): ?int
    {
        return ProgramStudi::query()->orderBy('id_program_studi')->value('id_program_studi');
    }

    private function aturan(?MataKuliah $mk = null): array
    {
        $kode = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9.\-]+$/', Rule::unique('mata_kuliah', 'kode_mata_kuliah')->ignore($mk?->kode_mata_kuliah, 'kode_mata_kuliah')];

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
        $data['kode_mata_kuliah'] = strtoupper(trim($data['kode']));
        $data['nama'] = trim($data['nama']);
        unset($data['kode']);

        return $data;
    }
}
