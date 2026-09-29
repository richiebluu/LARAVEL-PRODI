<?php

namespace App\Http\Controllers;

use App\Models\KegiatanMahasiswa;
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

    /**
     * Halaman mahasiswa berprestasi.
     * Alur: Prestasi (disetujui) -> Tingkat -> Poin -> Ranking.
     *
     * Mahasiswa berprestasi = mahasiswa yang memiliki prestasi berstatus "disetujui".
     *
     * REVISI 27-09-2026 (catatan no. 3, 8, 24):
     *  - Tab "Semua"   -> TABEL seperti tabel Data Dosen: satu baris = satu mahasiswa dengan
     *    PRESTASI TERBARU saja; prestasi lain dilihat lewat aksi "Lihat Prestasi Lainnya".
     *    Baris diurutkan dari prestasi yang paling baru diperoleh. Kolom "Poin Prestasi
     *    Akademik/Non-Akademik" dan angka ranking dihapus (sudah ada di halaman Ranking Mahasiswa).
     *
     * REVISI 24-09-2026:
     *  - Tab kriteria  -> CARD, satu tab untuk setiap kriteria SAW:
     *      Nilai Akademik (IPK), Prestasi Akademik, Prestasi Non-Akademik, Keaktifan Organisasi.
     *    Card diurutkan dari nilai kriteria tertinggi (nilai dihitung RankingService).
     */
    public function berprestasi(Request $request)
    {
        // Kode kriteria (C1..C4) => nama kriteria resmi dari config/saw.php.
        $daftarKriteria = collect(config('saw.kriteria'))->map(fn ($k) => $k['nama']);

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

            // Prestasi terbaru lebih dulu (tanggal perolehan, lalu data terakhir diinput).
            $prestasi = $m->prestasiDisetujui
                ->sortBy(fn ($p) => [-(optional($p->tanggal)->timestamp ?? 0), -$p->id_prestasi])
                ->values();

            return [
                'mahasiswa' => $m,
                'prestasi' => $prestasi,
                'terbaru' => $prestasi->first(),
                'nilai' => $nilai,
                // Hanya dipakai untuk urutan card yang nilainya sama; angka tidak ditampilkan.
                'peringkat' => $peringkat->get($m->nim)['peringkat'] ?? null,
            ];
        })->sortBy(fn ($r) => [
            -(optional($r['terbaru']?->tanggal)->timestamp ?? 0),
            -($r['terbaru']?->id_prestasi ?? 0),
            $r['mahasiswa']->nama,
        ])->values();

        // Tab kriteria: hanya mahasiswa yang memiliki nilai pada kriteria tsb, urut nilai tertinggi.
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

    /**
     * Halaman Kegiatan Mahasiswa (REVISI 28-09-2026 tahap 2, Mahasiswa > Kegiatan Mahasiswa).
     * Data dari tabel kegiatan_mahasiswa (dikelola Staff Prodi): filter kategori + pencarian + halaman.
     */
    public function kegiatan(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');
        $kategori = array_key_exists((string) $kategori, KegiatanMahasiswa::KATEGORI) ? $kategori : null;

        $kegiatan = KegiatanMahasiswa::aktif()
            ->cari($cari)
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->latest('tanggal')
            ->latest('id_kegiatan_mahasiswa')
            ->paginate(9)
            ->withQueryString();

        $ringkasan = KegiatanMahasiswa::aktif()
            ->selectRaw('kategori, COUNT(*) as jumlah')
            ->groupBy('kategori')
            ->pluck('jumlah', 'kategori');

        return view('kegiatan-mahasiswa', [
            'daftarKegiatan' => $kegiatan,
            'cari' => $cari,
            'kategori' => $kategori,
            // Hanya kategori yang punya data yang tampil di filter.
            'daftarKategori' => collect(KegiatanMahasiswa::KATEGORI)->filter(fn ($ikon, $k) => $ringkasan->has($k)),
            'totalKegiatan' => (int) $ringkasan->sum(),
            'kegiatanTahunIni' => KegiatanMahasiswa::aktif()->whereYear('tanggal', now()->year)->count(),
        ]);
    }
}
