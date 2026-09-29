<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** Berita Program Studi — dikelola Staff Prodi (REVISI 26-09-2026). */
class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $berita = Berita::query()
            ->when($cari !== '', fn ($q) => $q->where('judul', 'like', '%'.$cari.'%'))
            ->orderByDesc('tanggal')
            ->orderByDesc('id_berita')
            ->paginate(10)
            ->withQueryString();

        return view('staff-berita', [
            'daftarBerita' => $berita,
            'cari' => $cari,
            'jumlah' => $berita->total(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['slug'] = Berita::buatSlug($data['judul']);
        $data['gambar'] = $this->simpanGambar($request);
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        Berita::create($data);

        return redirect()->route('staff-berita')->with('success', 'Berita berhasil disimpan.');
    }

    public function update(Request $request, Berita $berita)
    {
        $data = $this->validasi($request);

        if ($data['judul'] !== $berita->judul) {
            $data['slug'] = Berita::buatSlug($data['judul'], $berita->id_berita);
        }

        $data['gambar'] = $this->simpanGambar($request) ?? $berita->gambar;

        $berita->update($data);

        return redirect()->route('staff-berita')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $berita)
    {
        $judul = $berita->judul;
        $berita->delete();

        return redirect()->route('staff-berita')->with('success', 'Berita "'.$judul.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'kategori' => ['nullable', 'string', 'max:60'],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'isi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', Rule::in([Berita::STATUS_DRAFT, Berita::STATUS_TERBIT])],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [], [
            'judul' => 'Judul berita',
            'isi' => 'Isi berita',
            'tanggal' => 'Tanggal berita',
            'gambar' => 'Gambar',
        ]);

        unset($data['gambar']);

        return $data;
    }

    private function simpanGambar(Request $request): ?string
    {
        return $request->hasFile('gambar') ? $request->file('gambar')->store('berita', 'public') : null;
    }
}
