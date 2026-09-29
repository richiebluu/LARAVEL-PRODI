<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $ranking) {}

    /** Halaman ranking publik (metode SAW). Sumber data: tabel ranking + ranking_bobot. */
    public function index(Request $request)
    {
        $semua = $this->ranking->untukPublik();
        $cari = trim((string) $request->query('q'));

        $daftar = $semua;
        if ($cari !== '') {
            $daftar = $semua->filter(function (array $r) use ($cari) {
                return str_contains(mb_strtolower($r['nama']), mb_strtolower($cari))
                    || str_contains((string) $r['nim'], $cari);
            })->values();
        }

        return view('ranking', [
            'podium' => $semua->take(3),
            'daftarRanking' => $daftar->skip($cari === '' ? 3 : 0)->values(),
            'adaRanking' => $semua->isNotEmpty(),
            'cari' => $cari,
            'kriteria' => config('saw.kriteria'),
            'bobot' => $this->ranking->bobot(),
            'tahun' => $this->ranking->tahunTerakhir(),
        ]);
    }
}
