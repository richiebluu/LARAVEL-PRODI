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

/**
 * DATA MASTER — Sarana & Prasarana (REVISI 28-09-2026 tahap 2).
 * CRUD Staff Prodi (pola sama seperti Prospek Lulusan/Testimoni) + impor CSV
 * untuk memasukkan daftar laboratorium & ruang sekaligus.
 */
class SaranaPrasaranaController extends Controller
{
    use MengimporCsv;

    public const KOLOM_CSV = ['nama', 'jenis', 'lokasi', 'kapasitas', 'fasilitas', 'deskripsi', 'status'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $jenis = $request->query('jenis');
        $jenis = array_key_exists((string) $jenis, SaranaPrasarana::JENIS) ? $jenis : null;

        $sarana = SaranaPrasarana::query()
            ->cari($cari)
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->urut()
            ->paginate(10)
            ->withQueryString();

        return view('staff-sarana-prasarana', [
            'daftarSarana' => $sarana,
            'cari' => $cari,
            'jenis' => $jenis,
            'jumlah' => $sarana->total(),
            'jumlahLab' => SaranaPrasarana::where('jenis', SaranaPrasarana::JENIS_LAB)->count(),
            'daftarJenis' => SaranaPrasarana::JENIS,
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

    /**
     * Impor CSV sarana & prasarana. Kolom fasilitas: pisahkan tiap item dengan tanda | (garis tegak).
     * Nama yang sama dianggap data yang sama (dilewati / diperbarui). Foto diisi lewat form Edit.
     */
    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nama', 'jenis'],
            'alias' => ['nama_sarana' => 'nama', 'nama_ruang' => 'nama', 'nama_laboratorium' => 'nama'],
            'label' => 'sarana & prasarana',
            'route' => 'staff-sarana-prasarana',
            'siapkan' => function (array $d) {
                $d['jenis'] = ImporCsv::cocokkan($d['jenis'], array_keys(SaranaPrasarana::JENIS));
                if ($d['jenis'] !== null && in_array(strtolower($d['jenis']), ['lab', 'labor'], true)) {
                    $d['jenis'] = SaranaPrasarana::JENIS_LAB;
                }
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
            'jenis' => ['required', Rule::in(array_keys(SaranaPrasarana::JENIS))],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'fasilitas' => ['nullable', 'string', 'max:2000'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([SaranaPrasarana::STATUS_AKTIF, SaranaPrasarana::STATUS_NONAKTIF])],
        ];
    }

    private function pesan(): array
    {
        return [
            'jenis.in' => 'Jenis harus salah satu dari: '.implode(', ', array_keys(SaranaPrasarana::JENIS)).'.',
            'status.in' => 'Status harus Aktif atau Nonaktif.',
        ];
    }

    private function atribut(): array
    {
        return [
            'nama' => 'Nama sarana/ruang',
            'jenis' => 'Jenis',
            'lokasi' => 'Lokasi',
            'kapasitas' => 'Kapasitas',
            'fasilitas' => 'Fasilitas',
            'deskripsi' => 'Deskripsi',
        ];
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('sarana-prasarana', 'public') : null;
    }
}
