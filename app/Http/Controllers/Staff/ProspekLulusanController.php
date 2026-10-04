<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\ProspekLulusan;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProspekLulusanController extends Controller
{
    use MengimporCsv;

    public const KOLOM_CSV = ['nama', 'ikon', 'deskripsi', 'status'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $prospek = ProspekLulusan::query()
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', '%'.$cari.'%')
                ->orWhere('deskripsi', 'like', '%'.$cari.'%')))
            ->urut()
            ->paginate(10)
            ->withQueryString();

        return view('staff-prospek-lulusan', [
            'daftarProspek' => $prospek,
            'cari' => $cari,
            'jumlah' => $prospek->total(),
            'daftarIkon' => ProspekLulusan::IKON,
            'deskripsiIkon' => ProspekLulusan::IKON_DESKRIPSI,
        ]);
    }

    public function store(Request $request)
    {
        ProspekLulusan::create($this->validasi($request) + [
            'staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi,
        ]);

        return redirect()->route('staff-prospek-lulusan')->with('success', 'Prospek lulusan berhasil ditambahkan.');
    }

    public function update(Request $request, ProspekLulusan $prospek)
    {
        $prospek->update($this->validasi($request, $prospek));

        return redirect()->route('staff-prospek-lulusan')->with('success', 'Prospek lulusan berhasil diperbarui.');
    }

    public function destroy(ProspekLulusan $prospek)
    {
        $nama = $prospek->nama;
        $prospek->delete();

        return redirect()->route('staff-prospek-lulusan')->with('success', 'Prospek lulusan "'.$nama.'" berhasil dihapus.');
    }

    public function template()
    {
        return ImporCsv::template('template-prospek-lulusan.csv', self::KOLOM_CSV);
    }

    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nama'],
            'alias' => ['nama_prospek' => 'nama', 'prospek_lulusan' => 'nama', 'nama_prospek_lulusan' => 'nama', 'ikon_kategori' => 'ikon'],
            'label' => 'prospek lulusan',
            'route' => 'staff-prospek-lulusan',
            'siapkan' => function (array $d) {
                $d['ikon'] = blank($d['ikon'])
                    ? ProspekLulusan::IKON_BAWAAN
                    : (ProspekLulusan::normalisasiIkon($d['ikon']) ?? $d['ikon']);
                $d['status'] = ImporCsv::cocokkan($d['status'], ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']) ?? ProspekLulusan::STATUS_AKTIF;

                return $d;
            },
            'kunci' => fn (array $d) => $d['nama'] === null ? null : mb_strtolower($d['nama']),
            'cari' => fn (string $nama) => ProspekLulusan::whereRaw('LOWER(nama) = ?', [$nama])->first(),
            'aturan' => fn () => $this->aturan(),
            'pesan' => $this->pesan(),
            'atribut' => $this->atribut(),
            'simpan' => function (array $data, ?ProspekLulusan $lama) {
                ($lama ?? new ProspekLulusan(['staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi]))->fill($data)->save();
            },
        ]);
    }

    private function validasi(Request $request, ?ProspekLulusan $lama = null): array
    {
        if ($request->filled('ikon')) {
            $request->merge(['ikon' => ProspekLulusan::normalisasiIkon($request->input('ikon')) ?? $request->input('ikon')]);
        }

        return $request->validate($this->aturan($lama), $this->pesan(), $this->atribut());
    }

    private function aturan(?ProspekLulusan $lama = null): array
    {
        $pilihan = array_keys(ProspekLulusan::IKON);
        if ($lama && filled($lama->ikon)) {
            $pilihan[] = $lama->ikon;
        }

        return [
            'nama' => ['required', 'string', 'max:150'],
            'ikon' => ['required', 'string', 'max:50', Rule::in($pilihan)],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([ProspekLulusan::STATUS_AKTIF, ProspekLulusan::STATUS_NONAKTIF])],
        ];
    }

    private function pesan(): array
    {
        return [
            'ikon.in' => 'Pilih salah satu ikon yang tersedia.',
            'status.in' => 'Status harus Aktif atau Nonaktif.',
        ];
    }

    private function atribut(): array
    {
        return [
            'nama' => 'Nama Prospek Lulusan',
            'ikon' => 'Ikon',
            'deskripsi' => 'Deskripsi Singkat',
        ];
    }
}
