<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\StrukturOrganisasi;
use App\Support\Berkas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StrukturOrganisasiController extends Controller
{
    public function index()
    {
        return view('staff-struktur-organisasi', [
            'daftarStruktur' => StrukturOrganisasi::with('dosen')->urut()->get(),
            'daftarDosen' => Dosen::orderBy('nama')->get(['nuptk', 'nama']),
            'daftarJabatan' => StrukturOrganisasi::daftarJabatan(),
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
            'jabatan' => ['required', 'string', Rule::in(StrukturOrganisasi::daftarJabatan())],
            'dosen_id' => ['nullable', Rule::exists('dosen', 'nuptk')],
            'nama' => ['nullable', 'required_without:dosen_id', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'jabatan.in' => 'Pilih Jabatan dari daftar yang tersedia.',
            'nama.required_without' => 'Pilih dari Daftar Dosen atau isi Nama Staff.',
        ], [
            'jabatan' => 'Jabatan',
            'dosen_id' => 'Daftar Dosen',
            'nama' => 'Nama Staff',
            'foto' => 'Foto',
        ]);

        unset($data['foto']);

        $data['dosen_id'] = $data['dosen_id'] ?? null;
        $data['nama'] = $data['nama'] ?? null;

        if (! empty($data['dosen_id'])) {
            $data['nama'] = null;
        }

        return $data;
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('struktur-organisasi', 'public') : null;
    }
}
