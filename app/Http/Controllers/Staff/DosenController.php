<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * DATA MASTER DOSEN — dikelola Staff Prodi.
 *
 * REVISI 26-09-2026: Dosen BUKAN lagi role/akun pengguna. Tidak ada akun login,
 * password, maupun dashboard Dosen. Data dosen ditampilkan pada Profil Program
 * Studi (Dosen Pengajar) dan dapat dipakai pada Struktur Organisasi.
 */
class DosenController extends Controller
{
    use MengimporCsv;

    /** Kolom CSV impor dosen (urutan template). REVISI 28-09-2026 tahap 2. */
    public const KOLOM_CSV = ['nuptk', 'nama', 'pendidikan_terakhir', 'email', 'google_scholar', 'alamat', 'tanggal_lahir', 'status'];

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $dosen = Dosen::query()
            ->cari($cari)
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('staff-dosen', [
            'daftarDosen' => $dosen,
            'cari' => $cari,
            'jumlah' => $dosen->total(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['foto'] = $this->simpanFoto($request);

        Dosen::create($data);

        return redirect()
            ->route('staff-dosen')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $data = $this->validasi($request, $dosen);
        $data['foto'] = $this->simpanFoto($request) ?? $dosen->foto;

        $dosen->update($data);

        return redirect()
            ->route('staff-dosen')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        $nama = $dosen->nama;
        $dosen->delete(); // struktur_organisasi.dosen_id (-> dosen.nuptk) menjadi null (nullOnDelete)

        return redirect()
            ->route('staff-dosen')
            ->with('success', 'Data dosen '.$nama.' berhasil dihapus.');
    }

    /** Unduh template CSV dosen. */
    public function template()
    {
        return ImporCsv::template('template-dosen.csv', self::KOLOM_CSV);
    }

    /**
     * Impor CSV Data Master Dosen (REVISI 28-09-2026 tahap 2). Aturan validasi sama dengan form.
     * NUPTK yang sudah terdaftar dilewati (bawaan) atau diperbarui. Foto diisi lewat form Edit.
     */
    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nuptk', 'nama'],
            'alias' => ['nama_dosen' => 'nama', 'pendidikan' => 'pendidikan_terakhir', 'scholar' => 'google_scholar'],
            'label' => 'dosen',
            'route' => 'staff-dosen',
            'unik_file' => ['email'],
            'siapkan' => function (array $d) {
                $d['status'] = ImporCsv::cocokkan($d['status'], Dosen::LABEL_STATUS) ?? Dosen::STATUS_AKTIF;
                $d['tanggal_lahir'] = ImporCsv::tanggal($d['tanggal_lahir']);
                $d['email'] = $d['email'] === null ? null : strtolower($d['email']);

                return $d;
            },
            'kunci' => fn (array $d) => $d['nuptk'],
            'cari' => fn (string $nuptk) => Dosen::where('nuptk', $nuptk)->first(),
            'aturan' => fn (?Dosen $lama) => $this->aturan($lama),
            'pesan' => $this->pesan(),
            'atribut' => $this->atribut(),
            'simpan' => function (array $data, ?Dosen $lama) {
                ($lama ?? new Dosen)->fill($data)->save();
            },
        ]);
    }

    private function validasi(Request $request, ?Dosen $dosen = null): array
    {
        $data = $request->validate($this->aturan($dosen) + [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->pesan(), $this->atribut());

        unset($data['foto']);

        return $data;
    }

    /** Aturan data dosen (dipakai form dan impor CSV). */
    private function aturan(?Dosen $dosen = null): array
    {
        return [
            'nuptk' => [
                'required', 'string', 'max:30', 'regex:/^[0-9]+$/',
                Rule::unique('dosen', 'nuptk')->ignore($dosen?->nuptk, 'nuptk'),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'google_scholar' => ['nullable', 'url', 'max:255'],
            // Satu field email (data kontak dosen, bukan akun login), domain @politala.ac.id.
            'email' => [
                'nullable', 'email', 'max:150',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && \App\Models\User::domainDari($value) !== strtolower((string) config('auth.domain_email.dosen'))) {
                        $fail('Email dosen wajib memakai domain @'.config('auth.domain_email.dosen').'.');
                    }
                },
                Rule::unique('dosen', 'email')->ignore($dosen?->nuptk, 'nuptk'),
            ],
            'alamat' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            // REVISI 28-09-2026: Aktif / Pendidikan (studi lanjut) / Nonaktif.
            'status' => ['required', Rule::in(array_keys(Dosen::LABEL_STATUS))],
        ];
    }

    private function pesan(): array
    {
        return [
            'nuptk.unique' => 'NUPTK tersebut sudah terdaftar.',
            'nuptk.regex' => 'NUPTK hanya boleh berisi angka.',
            'email.unique' => 'Email tersebut sudah dipakai dosen lain.',
            'google_scholar.url' => 'Link Google Scholar harus berupa URL lengkap (https://...).',
            'status.in' => 'Status dosen harus Aktif, Pendidikan, atau Nonaktif.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid (gunakan format 1980-12-31 atau 31/12/1980).',
        ];
    }

    private function atribut(): array
    {
        return [
            'nuptk' => 'NUPTK',
            'nama' => 'Nama',
            'email' => 'Email',
            'pendidikan_terakhir' => 'Pendidikan terakhir',
            'google_scholar' => 'Link Google Scholar',
            'tanggal_lahir' => 'Tanggal lahir',
        ];
    }

    private function simpanFoto(Request $request): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        return $request->file('foto')->store('dosen', 'public');
    }
}
