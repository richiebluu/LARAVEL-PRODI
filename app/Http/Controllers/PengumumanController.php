<?php

namespace App\Http\Controllers;

class PengumumanController extends Controller
{
    /**
     * Halaman pengumuman publik.
     *
     * Sesuai konsep sistem, pengumuman bersifat pribadi untuk mahasiswa
     * penerima, sehingga isinya tidak ditampilkan di halaman publik.
     * Mahasiswa melihat pengumumannya sendiri setelah login.
     */
    public function index()
    {
        return view('pengumuman');
    }
}
