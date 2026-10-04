<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $dosen = Dosen::query()
            ->cari($cari)
            ->urutStatus()
            ->orderBy('nama')
            ->get();

        $ringkasan = Dosen::query()->selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('dosen', [
            'daftarDosen' => $dosen,
            'cari' => $cari,
            'ringkasanStatus' => collect(Dosen::LABEL_STATUS)->map(fn ($label, $kode) => (int) ($ringkasan[$kode] ?? 0)),
            'totalDosen' => (int) $ringkasan->sum(),
        ]);
    }
}
