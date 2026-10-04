<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;

class AkreditasiController extends Controller
{
    public function index()
    {
        $riwayat = Akreditasi::with('programStudi')->terbaru()->get();

        return view('akreditasi', [
            'akreditasi' => Akreditasi::utama(),
            'riwayat' => $riwayat,
        ]);
    }
}
