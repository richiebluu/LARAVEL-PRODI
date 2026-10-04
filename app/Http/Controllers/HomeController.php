<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;
use App\Models\Berita;
use App\Models\Dosen;
use App\Models\LowonganPekerjaan;
use App\Models\Prestasi;
use App\Models\Testimoni;
use App\Services\RankingService;
use App\Services\StatistikService;

class HomeController extends Controller
{
    public function __construct(
        private readonly StatistikService $statistik,
        private readonly RankingService $ranking,
    ) {}

    public function index()
    {
        $capaian = $this->statistik->capaianProdi();

        $berprestasi = Prestasi::disetujui()
            ->with('mahasiswa')
            ->whereHas('mahasiswa')
            ->latest('tanggal')
            ->latest('id_prestasi')
            ->take(4)
            ->get();

        $dosen = Dosen::aktif()->orderBy('nama')->take(4)->get();

        $akreditasi = Akreditasi::utama();

        $ranking = $this->ranking->untukPublik()->take(5);

        return view('home', [
            'jumlahMahasiswa' => $capaian['mahasiswa'],
            'jumlahAlumni' => $capaian['alumni'],
            'jumlahPrestasi' => $capaian['prestasi'],
            'jumlahDosen' => $capaian['dosen'],
            'daftarBerprestasi' => $berprestasi,
            'daftarDosen' => $dosen,
            'akreditasi' => $akreditasi,
            'daftarRanking' => $ranking,
            'daftarTestimoni' => Testimoni::latest('created_at')->latest('id_testimoni')->take(3)->get(),
            'daftarBerita' => Berita::terbit()->orderByDesc('tanggal')->orderByDesc('id_berita')->take(3)->get(),
            'daftarLowongan' => LowonganPekerjaan::tampil()->latest('created_at')->latest('id_lowongan_pekerjaan')->take(3)->get(),
        ]);
    }
}
