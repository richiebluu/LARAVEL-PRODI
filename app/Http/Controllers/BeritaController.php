<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $slugJenis = (string) $request->query('jenis');
        $jenis = Berita::SLUG_JENIS[$slugJenis] ?? null;

        $berita = Berita::terbit()
            ->jenis($jenis)
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('judul', 'like', '%'.$cari.'%')
                ->orWhere('kategori', 'like', '%'.$cari.'%')
                ->orWhere('lokasi', 'like', '%'.$cari.'%')))
            ->orderByDesc('tanggal')
            ->orderByDesc('id_berita')
            ->paginate(9)
            ->withQueryString();

        return view('berita', [
            'daftarBerita' => $berita,
            'cari' => $cari,
            'jenis' => $jenis ? $slugJenis : null,
        ]);
    }

    public function show(Berita $berita)
    {
        abort_unless($berita->status === Berita::STATUS_TERBIT, 404);

        return view('berita-detail', [
            'berita' => $berita,
            'beritaLain' => Berita::terbit()
                ->whereKeyNot($berita->id_berita)
                ->orderByDesc('tanggal')
                ->orderByDesc('id_berita')
                ->take(3)
                ->get(),
        ]);
    }
}
