<?php

namespace App\Http\Controllers;

use App\Models\Testimoni;

class TestimoniController extends Controller
{
    /**
     * Halaman Testimoni Alumni.
     * REVISI 27-09-2026: menu Testimoni tidak memakai dropdown; langsung menuju
     * halaman Testimoni Alumni. Testimoni Mahasiswa Berprestasi dihapus.
     */
    public function index()
    {
        return view('testimoni', [
            'daftarTestimoni' => Testimoni::query()
                ->latest('created_at')
                ->latest('id_testimoni')
                ->get(),
        ]);
    }
}
