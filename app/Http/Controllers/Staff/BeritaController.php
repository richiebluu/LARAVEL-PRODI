<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $jenis = array_key_exists((string) $request->query('jenis'), Berita::LABEL_JENIS) ? $request->query('jenis') : null;

        $berita = Berita::query()
            ->jenis($jenis)
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('judul', 'like', '%'.$cari.'%')
                ->orWhere('kategori', 'like', '%'.$cari.'%')
                ->orWhere('lokasi', 'like', '%'.$cari.'%')
                ->orWhere('penyelenggara', 'like', '%'.$cari.'%')))
            ->orderByDesc('tanggal')
            ->orderByDesc('id_berita')
            ->paginate(10)
            ->withQueryString();

        return view('staff-berita', [
            'daftarBerita' => $berita,
            'cari' => $cari,
            'jenis' => $jenis,
            'jumlah' => $berita->total(),
            'daftarJenis' => Berita::LABEL_JENIS,
            'saranKategori' => collect(Berita::KATEGORI_KEGIATAN)
                ->merge(Berita::query()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori'))
                ->unique()
                ->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['slug'] = Berita::buatSlug($data['judul']);
        $data['gambar'] = $this->simpanGambar($request);
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        Berita::create($data);

        return redirect()->route('staff-berita')->with('success', ($data['jenis'] === Berita::JENIS_KEGIATAN ? 'Kegiatan mahasiswa' : 'Berita').' berhasil disimpan.');
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
            'jenis' => ['nullable', Rule::in(array_keys(Berita::LABEL_JENIS))],
            'kategori' => ['nullable', 'string', 'max:60'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'penyelenggara' => ['nullable', 'string', 'max:150'],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'isi' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', Rule::in([Berita::STATUS_DRAFT, Berita::STATUS_TERBIT])],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'link_media_sosial' => ['nullable', 'url:http,https', 'max:500'],
        ], [
            'jenis.in' => 'Pilih Jenis Berita: Kegiatan Prodi atau Kegiatan Mahasiswa.',
            'link_media_sosial.url' => 'Link Media Sosial harus berupa URL lengkap (contoh: https://instagram.com/p/contoh).',
        ], [
            'judul' => 'Judul berita',
            'jenis' => 'Jenis berita',
            'lokasi' => 'Lokasi kegiatan',
            'penyelenggara' => 'Penyelenggara',
            'isi' => 'Isi berita',
            'tanggal' => 'Tanggal berita',
            'gambar' => 'Gambar',
            'link_media_sosial' => 'Link Media Sosial',
        ]);

        unset($data['gambar']);

        $data['jenis'] = $data['jenis'] ?? Berita::JENIS_BERITA;

        if ($data['jenis'] !== Berita::JENIS_KEGIATAN) {
            $data['lokasi'] = null;
            $data['penyelenggara'] = null;
        }

        return $data;
    }

    private function simpanGambar(Request $request): ?string
    {
        return $request->hasFile('gambar') ? $request->file('gambar')->store('berita', 'public') : null;
    }
}
