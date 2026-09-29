<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;

class AkreditasiController extends Controller
{
    /** Halaman akreditasi publik. */
    public function index()
    {
        $riwayat = Akreditasi::with('programStudi')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id_akreditasi')
            ->get();

        return view('akreditasi', [
            'akreditasi' => $riwayat->first(),
            'riwayat' => $riwayat,
        ]);
    }
}
