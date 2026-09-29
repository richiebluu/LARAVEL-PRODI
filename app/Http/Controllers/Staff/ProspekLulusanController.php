<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\ProspekLulusan;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * DATA MASTER — Prospek Lulusan (REVISI 28-09-2026).
 * Dikelola Staff Prodi dengan pola CRUD yang sama seperti Lowongan Kerja.
 * Ikon kategori dipilih dari dropdown (ProspekLulusan::IKON) pada tambah & edit.
 */
class ProspekLulusanController extends Controller
{
    use MengimporCsv;

    /** Kolom CSV impor prospek lulusan (urutan template). REVISI 28-09-2026 tahap 2. */
    public const KOLOM_CSV = ['nama', 'kategori', 'ikon', 'deskripsi', 'status'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $prospek = ProspekLulusan::query()
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', '%'.$cari.'%')
                ->orWhere('kategori', 'like', '%'.$cari.'%')))
            ->urut()
            ->paginate(10)
            ->withQueryString();

        return view('staff-prospek-lulusan', [
            'daftarProspek' => $prospek,
            'cari' => $cari,
            'jumlah' => $prospek->total(),
            'daftarIkon' => ProspekLulusan::IKON,
            // Saran kategori dari data yang sudah ada (datalist).
            'saranKategori' => ProspekLulusan::query()->distinct()->orderBy('kategori')->pluck('kategori'),
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
        $prospek->update($this->validasi($request));

        return redirect()->route('staff-prospek-lulusan')->with('success', 'Prospek lulusan berhasil diperbarui.');
    }

    public function destroy(ProspekLulusan $prospek)
    {
        $nama = $prospek->nama;
        $prospek->delete();

        return redirect()->route('staff-prospek-lulusan')->with('success', 'Prospek lulusan "'.$nama.'" berhasil dihapus.');
    }

    /** Unduh template CSV prospek lulusan. */
    public function template()
    {
        return ImporCsv::template('template-prospek-lulusan.csv', self::KOLOM_CSV);
    }

    /**
     * Impor CSV prospek lulusan (REVISI 28-09-2026 tahap 2).
     * Kolom ikon boleh diisi kode ikon (mis. fa-code) atau label dropdown (mis. "Pemrograman / Software");
     * kosong = ikon umum. Nama yang sama dianggap data yang sama.
     */
    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nama', 'kategori'],
            'alias' => ['nama_prospek' => 'nama', 'prospek_lulusan' => 'nama', 'ikon_kategori' => 'ikon'],
            'label' => 'prospek lulusan',
            'route' => 'staff-prospek-lulusan',
            'siapkan' => function (array $d) {
                $d['ikon'] = ImporCsv::cocokkan($d['ikon'], ProspekLulusan::IKON) ?? 'fa-briefcase';
                if (is_string($d['ikon']) && ! str_starts_with($d['ikon'], 'fa-') && array_key_exists('fa-'.strtolower($d['ikon']), ProspekLulusan::IKON)) {
                    $d['ikon'] = 'fa-'.strtolower($d['ikon']);
                }
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

    private function validasi(Request $request): array
    {
        return $request->validate($this->aturan(), $this->pesan(), $this->atribut());
    }

    private function aturan(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'ikon' => ['required', Rule::in(array_keys(ProspekLulusan::IKON))],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([ProspekLulusan::STATUS_AKTIF, ProspekLulusan::STATUS_NONAKTIF])],
        ];
    }

    private function pesan(): array
    {
        return [
            'ikon.in' => 'Pilih ikon kategori dari daftar yang tersedia.',
            'status.in' => 'Status harus Aktif atau Nonaktif.',
        ];
    }

    private function atribut(): array
    {
        return [
            'nama' => 'Nama prospek',
            'kategori' => 'Kategori',
            'ikon' => 'Ikon kategori',
            'deskripsi' => 'Deskripsi',
        ];
    }
}
