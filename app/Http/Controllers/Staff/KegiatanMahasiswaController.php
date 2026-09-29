<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KegiatanMahasiswa;
use App\Support\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * INFORMASI PUBLIK — Kegiatan Mahasiswa (REVISI 28-09-2026 tahap 2).
 * CRUD Staff Prodi (pola sama seperti Testimoni). Tanpa impor CSV karena setiap
 * kegiatan berisi deskripsi panjang dan foto dokumentasi yang diunggah satu per satu.
 */
class KegiatanMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');
        $kategori = array_key_exists((string) $kategori, KegiatanMahasiswa::KATEGORI) ? $kategori : null;

        $kegiatan = KegiatanMahasiswa::query()
            ->cari($cari)
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->latest('tanggal')
            ->latest('id_kegiatan_mahasiswa')
            ->paginate(10)
            ->withQueryString();

        return view('staff-kegiatan-mahasiswa', [
            'daftarKegiatan' => $kegiatan,
            'cari' => $cari,
            'kategori' => $kategori,
            'jumlah' => $kegiatan->total(),
            'daftarKategori' => KegiatanMahasiswa::KATEGORI,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['foto'] = $this->simpanFoto($request);
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        KegiatanMahasiswa::create($data);

        return redirect()->route('staff-kegiatan-mahasiswa')->with('success', 'Kegiatan mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, KegiatanMahasiswa $kegiatan)
    {
        $data = $this->validasi($request);
        $fotoBaru = $this->simpanFoto($request);

        if ($fotoBaru || $request->boolean('hapus_foto')) {
            Berkas::hapus($kegiatan->foto);
        }
        $data['foto'] = $fotoBaru ?? ($request->boolean('hapus_foto') ? null : $kegiatan->foto);

        $kegiatan->update($data);

        return redirect()->route('staff-kegiatan-mahasiswa')->with('success', 'Kegiatan mahasiswa berhasil diperbarui.');
    }

    public function destroy(KegiatanMahasiswa $kegiatan)
    {
        $judul = $kegiatan->judul;
        Berkas::hapus($kegiatan->foto);
        $kegiatan->delete();

        return redirect()->route('staff-kegiatan-mahasiswa')->with('success', 'Kegiatan "'.$judul.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(array_keys(KegiatanMahasiswa::KATEGORI))],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'penyelenggara' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in([KegiatanMahasiswa::STATUS_AKTIF, KegiatanMahasiswa::STATUS_NONAKTIF])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'kategori.in' => 'Pilih kategori kegiatan dari daftar yang tersedia.',
        ], [
            'judul' => 'Nama kegiatan',
            'kategori' => 'Kategori',
            'tanggal' => 'Tanggal kegiatan',
            'lokasi' => 'Lokasi',
            'penyelenggara' => 'Penyelenggara',
            'deskripsi' => 'Deskripsi',
        ]);

        unset($data['foto']);

        return $data;
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('kegiatan-mahasiswa', 'public') : null;
    }
}
