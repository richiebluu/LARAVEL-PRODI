<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\SaranaPrasarana;
use App\Support\Berkas;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaranaPrasaranaController extends Controller
{
    use MengimporCsv;

    public const KOLOM_CSV = ['nama', 'gedung', 'kapasitas', 'fasilitas', 'status'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $gedung = $request->query('gedung');
        $gedung = in_array($gedung, SaranaPrasarana::GEDUNG, true) ? $gedung : null;

        $sarana = SaranaPrasarana::query()
            ->cari($cari)
            ->when($gedung, fn ($q) => $q->where('gedung', $gedung))
            ->urut()
            ->paginate(10)
            ->withQueryString();

        return view('staff-sarana-prasarana', [
            'daftarSarana' => $sarana,
            'cari' => $cari,
            'gedung' => $gedung,
            'jumlah' => $sarana->total(),
            'daftarGedung' => SaranaPrasarana::GEDUNG,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['foto'] = $this->simpanFoto($request);
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        SaranaPrasarana::create($data);

        return redirect()->route('staff-sarana-prasarana')->with('success', 'Sarana & prasarana berhasil ditambahkan.');
    }

    public function update(Request $request, SaranaPrasarana $sarana)
    {
        $data = $this->validasi($request);
        $fotoBaru = $this->simpanFoto($request);

        if ($fotoBaru || $request->boolean('hapus_foto')) {
            Berkas::hapus($sarana->foto);
        }
        $data['foto'] = $fotoBaru ?? ($request->boolean('hapus_foto') ? null : $sarana->foto);

        $sarana->update($data);

        return redirect()->route('staff-sarana-prasarana')->with('success', 'Sarana & prasarana berhasil diperbarui.');
    }

    public function destroy(SaranaPrasarana $sarana)
    {
        $nama = $sarana->nama;
        Berkas::hapus($sarana->foto);
        $sarana->delete();

        return redirect()->route('staff-sarana-prasarana')->with('success', '"'.$nama.'" berhasil dihapus.');
    }

    public function template()
    {
        return ImporCsv::template('template-sarana-prasarana.csv', self::KOLOM_CSV);
    }

    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nama', 'gedung'],
            'alias' => ['nama_sarana' => 'nama', 'nama_ruang' => 'nama', 'nama_laboratorium' => 'nama', 'nama_gedung' => 'gedung'],
            'label' => 'sarana & prasarana',
            'route' => 'staff-sarana-prasarana',
            'siapkan' => function (array $d) {
                $d['gedung'] = ImporCsv::cocokkan($d['gedung'], SaranaPrasarana::GEDUNG);
                $d['status'] = ImporCsv::cocokkan($d['status'], ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']) ?? SaranaPrasarana::STATUS_AKTIF;
                if ($d['fasilitas'] !== null) {
                    $d['fasilitas'] = implode("\n", array_filter(array_map('trim', explode('|', $d['fasilitas']))));
                }

                return $d;
            },
            'kunci' => fn (array $d) => $d['nama'] === null ? null : mb_strtolower($d['nama']),
            'cari' => fn (string $nama) => SaranaPrasarana::whereRaw('LOWER(nama) = ?', [$nama])->first(),
            'aturan' => fn () => $this->aturan(),
            'pesan' => $this->pesan(),
            'atribut' => $this->atribut(),
            'simpan' => function (array $data, ?SaranaPrasarana $lama) {
                ($lama ?? new SaranaPrasarana(['staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi]))->fill($data)->save();
            },
        ]);
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate($this->aturan() + [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->pesan(), $this->atribut());

        unset($data['foto']);

        return $data;
    }

    private function aturan(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'gedung' => ['required', Rule::in(SaranaPrasarana::GEDUNG)],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'fasilitas' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in([SaranaPrasarana::STATUS_AKTIF, SaranaPrasarana::STATUS_NONAKTIF])],
        ];
    }

    private function pesan(): array
    {
        return [
            'gedung.in' => 'Gedung harus salah satu dari: '.implode(', ', SaranaPrasarana::GEDUNG).'.',
            'status.in' => 'Status harus Aktif atau Nonaktif.',
        ];
    }

    private function atribut(): array
    {
        return [
            'nama' => 'Nama sarana/ruang',
            'gedung' => 'Gedung',
            'kapasitas' => 'Kapasitas',
            'fasilitas' => 'Fasilitas',
        ];
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('sarana-prasarana', 'public') : null;
    }
}
