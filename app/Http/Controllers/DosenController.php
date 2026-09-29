<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    /**
     * Halaman dosen publik.
     * REVISI 24-09-2026: tampilan berupa TABEL + tombol "Lihat Detail" (modal detail
     * dosen yang sudah ada). Bagian publikasi diganti link "Publikasi Google Scholar".
     */
    public function index(Request $request)
    {
        // REVISI 28-09-2026: semua dosen ditampilkan beserta Status
        // (Aktif / Pendidikan / Nonaktif), diurutkan Aktif lebih dulu.
        // REVISI 28-09-2026 tahap 2: fitur search di atas tabel dosen (server-side lewat ?q=,
        // ditambah penyaringan realtime di browser) + ringkasan jumlah per status.
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
