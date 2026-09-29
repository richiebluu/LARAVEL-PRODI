<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Services\RankingService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $ranking) {}

    public function index()
    {
        $bobot = $this->ranking->bobot();

        return view('staff-ranking', [
            'bobot' => $bobot,
            'totalBobot' => $this->ranking->totalBobot($bobot),
            'perhitungan' => $this->ranking->hitung($bobot),
            'rankingTersimpan' => Ranking::count(),
            'tahunTersimpan' => $this->ranking->tahunTerakhir(),
            'kriteria' => config('saw.kriteria'),
            'tahun' => (int) date('Y'),
        ]);
    }

    /*
     * CATATAN REVISI DOSEN (22 September 2026):
     * Method simpanBobot() dan route POST /staff-ranking/bobot DIHAPUS.
     * Bobot kriteria SAW bersifat tetap (C1 35%, C2 30%, C3 20%, C4 15%)
     * sesuai Excel acuan dan hanya ditampilkan (read-only) di halaman ini.
     */

    /** Hitung ulang ranking dari database lalu simpan ke tabel ranking. */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
        ]);

        if ($this->ranking->totalBobot() <= 0) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Bobot kriteria belum tersedia. Jalankan php artisan db:seed --class=RankingBobotSeeder.']);
        }

        $jumlah = $this->ranking->simpan((int) $data['tahun']);

        if ($jumlah === 0) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Belum ada mahasiswa aktif yang dapat diberi peringkat.']);
        }

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Ranking berhasil dihitung untuk '.$jumlah.' mahasiswa.');
    }

    /** Kosongkan tabel ranking. */
    public function reset()
    {
        Ranking::query()->delete();

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Data ranking berhasil dikosongkan.');
    }
}
