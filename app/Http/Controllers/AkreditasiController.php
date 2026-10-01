<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;

class AkreditasiController extends Controller
{
    /**
     * Halaman akreditasi publik.
     *
     * REVISI DOSEN (01-10-2026): akreditasi utama = data berstatus "Terakreditasi"
     * dengan tanggal penetapan PALING BARU (Akreditasi::utama()), bukan data dengan
     * ID terakhir. Riwayat tetap ditampilkan, diurutkan berdasarkan tanggal.
     */
    public function index()
    {
        $riwayat = Akreditasi::with('programStudi')->terbaru()->get();

        return view('akreditasi', [
            'akreditasi' => Akreditasi::utama(),
            'riwayat' => $riwayat,
        ]);
    }
}
