<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\StrukturOrganisasi;
use App\Support\Berkas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** DATA MASTER — Struktur Organisasi Program Studi (REVISI 26-09-2026). */
class StrukturOrganisasiController extends Controller
{
    public function index()
    {
        return view('staff-struktur-organisasi', [
            'daftarStruktur' => StrukturOrganisasi::with('dosen')->urut()->get(),
            'daftarDosen' => Dosen::orderBy('nama')->get(['nuptk', 'nama']),
            'saranJabatan' => StrukturOrganisasi::SARAN_JABATAN,
        ]);
    }

    public function store(Request $request)
    {
        StrukturOrganisasi::create($this->validasi($request) + [
            'program_studi_id' => ProgramStudi::query()->value('id_program_studi'),
            'foto' => $this->simpanFoto($request),
        ]);

        return redirect()->route('staff-struktur-organisasi')->with('success', 'Jabatan struktur organisasi berhasil ditambahkan.');
    }

    public function update(Request $request, StrukturOrganisasi $struktur)
    {
        $data = $this->validasi($request);
        $fotoBaru = $this->simpanFoto($request);

        if ($fotoBaru || $request->boolean('hapus_foto')) {
            Berkas::hapus($struktur->foto);
            $data['foto'] = $fotoBaru;
        }

        $struktur->update($data + [
            'program_studi_id' => $struktur->program_studi_id ?? ProgramStudi::query()->value('id_program_studi'),
        ]);

        return redirect()->route('staff-struktur-organisasi')->with('success', 'Struktur organisasi berhasil diperbarui.');
    }

    public function destroy(StrukturOrganisasi $struktur)
    {
        $jabatan = $struktur->jabatan;
        Berkas::hapus($struktur->foto);
        $struktur->delete();

        return redirect()->route('staff-struktur-organisasi')->with('success', 'Jabatan "'.$jabatan.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'jabatan' => ['required', 'string', 'max:150'],
            // ERD: DOSEN (1) -- MENJABAT -- (N) STRUKTUR_ORGANISASI, dosen_id berisi NUPTK.
            'dosen_id' => ['nullable', Rule::exists('dosen', 'nuptk')],
            // Nama wajib bila pejabat tidak dipilih dari data dosen (mis. Staff Prodi).
            'nama' => ['nullable', 'required_without:dosen_id', 'string', 'max:150'],
            // REVISI 27-09-2026: upload foto pejabat (opsional).
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama.required_without' => 'Pilih dosen atau isi nama pejabat.',
        ], [
            'dosen_id' => 'Dosen',
            'foto' => 'Foto',
        ]);

        unset($data['foto']);

        $data['dosen_id'] = $data['dosen_id'] ?? null;
        $data['nama'] = $data['nama'] ?? null;

        if (! empty($data['dosen_id'])) {
            $data['nama'] = null; // nama diambil dari data dosen
        }

        return $data;
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('struktur-organisasi', 'public') : null;
    }
}
