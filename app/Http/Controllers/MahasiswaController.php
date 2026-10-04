<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Services\RankingService;
use App\Services\StatistikService;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function __construct(
        private readonly StatistikService $statistik,
        private readonly RankingService $ranking,
    ) {}

    public function berprestasi(Request $request)
    {
        $daftarKriteria = collect(config('saw.kriteria'))->map(fn ($k) => $k['nama'])->except('C4');

        $kategori = $request->query('kategori');
        $kodeKategori = $daftarKriteria->search($kategori, true);
        if ($kodeKategori === false) {
            $kategori = null;
            $kodeKategori = null;
        }

        $mahasiswa = Mahasiswa::query()
            ->berprestasi()
            ->with(['prestasiDisetujui', 'organisasi'])
            ->get();

        $peringkat = $this->ranking->untukPublik()->keyBy('nim');

        $daftar = $mahasiswa->map(function (Mahasiswa $m) use ($peringkat) {
            $nilai = $this->ranking->nilaiKriteria($m);

            $prestasi = $m->prestasiDisetujui
                ->sortBy(fn ($p) => [-(optional($p->tanggal)->timestamp ?? 0), -$p->id_prestasi])
                ->values();

            return [
                'mahasiswa' => $m,
                'prestasi' => $prestasi,
                'terbaru' => $prestasi->first(),
                'nilai' => $nilai,
                'peringkat' => $peringkat->get($m->nim)['peringkat'] ?? null,
            ];
        })->sortBy(fn ($r) => [
            -(optional($r['terbaru']?->tanggal)->timestamp ?? 0),
            -($r['terbaru']?->id_prestasi ?? 0),
            $r['mahasiswa']->nama,
        ])->values();

        if ($kodeKategori !== null) {
            $kolom = strtolower($kodeKategori);
            $daftar = $daftar
                ->filter(fn ($r) => $r['nilai'][$kolom] > 0)
                ->sortBy(fn ($r) => [-$r['nilai'][$kolom], $r['peringkat'] ?? PHP_INT_MAX, $r['mahasiswa']->nama])
                ->values();
        }

        return view('mahasiswa-berprestasi', [
            'daftarBerprestasi' => $daftar,
            'jumlahMahasiswa' => $daftar->count(),
            'kategori' => $kategori,
            'kodeKategori' => $kodeKategori,
            'daftarKriteria' => $daftarKriteria,
        ]);
    }
}
