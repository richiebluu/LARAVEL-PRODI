<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Models\RankingBobot;
use App\Services\RankingService;
use Illuminate\Http\Request;

/**
 * Ranking Mahasiswa Berprestasi — metode AHP + SAW.
 *
 *   AHP (Analytic Hierarchy Process)  -> menentukan BOBOT kriteria C1..C4   (method hitungAHP di bawah)
 *   SAW (Simple Additive Weighting)   -> PERANGKINGAN mahasiswa memakai bobot tsb (RankingService)
 *
 * REVISI DOSEN 01-10-2026:
 *  - Perhitungan AHP ditulis lengkap di controller ini (bukan disembunyikan di library)
 *    agar setiap tahap rumusnya dapat diperiksa.
 *  - Bobot hasil AHP dipakai pada proses ranking SAW (method generate).
 *  - Dasar pembobotan setiap kriteria disimpan di ranking_bobot.dasar_pembobotan.
 */
class RankingController extends Controller
{
    public function __construct(private readonly RankingService $ranking) {}

    public function index()
    {
        $bobot = $this->ranking->bobot();

        // Hitung AHP dari matriks perbandingan berpasangan (config/saw.php -> 'ahp').
        $ahp = $this->hitungAHP(
            array_keys(config('saw.kriteria')),
            config('saw.ahp.perbandingan'),
        );

        // Cek apakah bobot yang tersimpan di database sama dengan bobot hasil AHP.
        $bobotSesuaiAhp = collect($ahp['bobot_dibulatkan'])
            ->every(fn ($nilai, $kode) => abs((float) ($bobot->get($kode)->bobot ?? -1) - $nilai) < 0.00001);

        return view('staff-ranking', [
            'bobot' => $bobot,
            'totalBobot' => $this->ranking->totalBobot($bobot),
            'perhitungan' => $this->ranking->hitung($bobot),
            'rankingTersimpan' => Ranking::count(),
            'tahunTersimpan' => $this->ranking->tahunTerakhir(),
            'kriteria' => config('saw.kriteria'),
            'tahun' => (int) date('Y'),
            'ahp' => $ahp,
            'bobotSesuaiAhp' => $bobotSesuaiAhp,
            'dasarAhp' => $this->dasarPerbandingan($ahp),
        ]);
    }

    /* ======================================================================
     *  PERHITUNGAN AHP (ANALYTIC HIERARCHY PROCESS)
     * ======================================================================
     *
     * Masukan:
     *   $kode         = daftar kriteria, mis. ['C1', 'C2', 'C3', 'C4']
     *   $perbandingan = nilai segitiga atas matriks (skala Saaty 1-9),
     *                   mis. ['C1' => ['C2' => 2, 'C3' => 3, 'C4' => 5], ...]
     *
     * Keluaran: seluruh hasil antara (matriks, jumlah kolom, normalisasi, priority
     * vector, weighted sum, consistency vector, lambda max, CI, RI, CR) supaya
     * dapat ditampilkan kepada Staff Prodi sebagai bahan laporan.
     */
    public function hitungAHP(array $kode, array $perbandingan): array
    {
        $n = count($kode);

        // 1. Membentuk matriks perbandingan berpasangan A (n x n)
        //    a_ii = 1 (kriteria dibandingkan dengan dirinya sendiri)
        //    a_ij = nilai skala Saaty bila diisi pada segitiga atas
        //    a_ji = 1 / a_ij (nilai resiprokal / kebalikan)
        $matriks = [];
        foreach ($kode as $baris) {
            foreach ($kode as $kolom) {
                if ($baris === $kolom) {
                    $matriks[$baris][$kolom] = 1.0;
                } elseif (isset($perbandingan[$baris][$kolom])) {
                    $matriks[$baris][$kolom] = (float) $perbandingan[$baris][$kolom];
                } elseif (isset($perbandingan[$kolom][$baris])) {
                    $matriks[$baris][$kolom] = 1 / (float) $perbandingan[$kolom][$baris];
                } else {
                    $matriks[$baris][$kolom] = 1.0; // belum diisi -> dianggap sama penting
                }
            }
        }

        // 2. Menghitung jumlah setiap kolom
        //    jumlahKolom_j = Σ_i a_ij
        $jumlahKolom = [];
        foreach ($kode as $kolom) {
            $jumlahKolom[$kolom] = 0.0;
            foreach ($kode as $baris) {
                $jumlahKolom[$kolom] += $matriks[$baris][$kolom];
            }
        }

        // 3. Normalisasi matriks
        //    n_ij = a_ij / jumlahKolom_j   (setiap kolom hasil normalisasi berjumlah 1)
        $normalisasi = [];
        foreach ($kode as $baris) {
            foreach ($kode as $kolom) {
                $normalisasi[$baris][$kolom] = $matriks[$baris][$kolom] / $jumlahKolom[$kolom];
            }
        }

        // 4. Menghitung priority vector (eigen vector) = BOBOT kriteria
        //    w_i = (Σ_j n_ij) / n   (rata-rata setiap baris matriks normalisasi)
        $jumlahBarisNormalisasi = [];
        $priorityVector = [];
        foreach ($kode as $baris) {
            $jumlahBarisNormalisasi[$baris] = array_sum($normalisasi[$baris]);
            $priorityVector[$baris] = $jumlahBarisNormalisasi[$baris] / $n;
        }

        // 5. Menghitung weighted sum vector
        //    WSV_i = Σ_j (a_ij x w_j)   (matriks awal dikali priority vector)
        $weightedSum = [];
        foreach ($kode as $baris) {
            $weightedSum[$baris] = 0.0;
            foreach ($kode as $kolom) {
                $weightedSum[$baris] += $matriks[$baris][$kolom] * $priorityVector[$kolom];
            }
        }

        // 6. Menghitung consistency vector
        //    CV_i = WSV_i / w_i
        $consistencyVector = [];
        foreach ($kode as $baris) {
            $consistencyVector[$baris] = $priorityVector[$baris] > 0
                ? $weightedSum[$baris] / $priorityVector[$baris]
                : 0.0;
        }

        // 7. Menghitung lambda max (nilai eigen maksimum)
        //    λmax = (Σ CV_i) / n
        $lambdaMax = array_sum($consistencyVector) / $n;

        // 8. Menghitung Consistency Index (CI)
        //    CI = (λmax - n) / (n - 1)
        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0.0;

        // 9. Menghitung Consistency Ratio (CR)
        //    CR = CI / RI   (RI = Random Index Saaty sesuai ukuran matriks n; n=4 -> 0.90)
        $ri = (float) (config('saw.ahp.indeks_random')[$n] ?? 0);
        $cr = $ri > 0 ? $ci / $ri : 0.0;

        // 10. Menentukan apakah matriks konsisten
        //     Konsisten bila CR <= 0.1 (10%). Bila tidak, perbandingan harus diperbaiki
        //     dan bobot TIDAK boleh dipakai untuk ranking.
        $batasCr = (float) config('saw.ahp.batas_cr', 0.1);
        $konsisten = $cr <= $batasCr;

        // Bobot yang disimpan ke ranking_bobot.bobot (decimal(5,2)) -> dibulatkan 2 desimal.
        $presisi = (int) config('saw.ahp.presisi_bobot', 2);
        $bobotDibulatkan = array_map(fn ($w) => round($w, $presisi), $priorityVector);

        return [
            'kode' => $kode,
            'n' => $n,
            'matriks' => $matriks,
            'jumlah_kolom' => $jumlahKolom,
            'normalisasi' => $normalisasi,
            'jumlah_baris_normalisasi' => $jumlahBarisNormalisasi,
            'priority_vector' => $priorityVector,
            'weighted_sum' => $weightedSum,
            'consistency_vector' => $consistencyVector,
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'ri' => $ri,
            'cr' => $cr,
            'batas_cr' => $batasCr,
            'konsisten' => $konsisten,
            'bobot_dibulatkan' => $bobotDibulatkan,
        ];
    }

    /**
     * Hitung ulang ranking dari database lalu simpan ke tabel ranking.
     *
     * Alur AHP + SAW:
     *  1. Hitung bobot dengan AHP (hitungAHP).
     *  2. Bila CR > 0.1 -> proses dihentikan (perbandingan tidak konsisten).
     *  3. Simpan bobot hasil AHP ke tabel ranking_bobot.
     *  4. Jalankan SAW (RankingService::simpan) memakai bobot tersebut.
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
        ]);

        // 1. AHP -> bobot kriteria
        $ahp = $this->hitungAHP(array_keys(config('saw.kriteria')), config('saw.ahp.perbandingan'));

        // 2. Pengecekan konsistensi
        if (! $ahp['konsisten']) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Matriks perbandingan AHP tidak konsisten (CR = '.number_format($ahp['cr'], 4).' > '.$ahp['batas_cr'].'). Perbaiki nilai perbandingan pada config/saw.php.']);
        }

        // 3. Bobot hasil AHP dipakai sebagai bobot SAW
        $this->ranking->terapkanBobot($ahp['bobot_dibulatkan']);

        if ($this->ranking->totalBobot() <= 0) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Bobot kriteria belum tersedia. Jalankan php artisan db:seed --class=RankingBobotSeeder.']);
        }

        // 4. SAW -> ranking mahasiswa
        $jumlah = $this->ranking->simpan((int) $data['tahun']);

        if ($jumlah === 0) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Belum ada mahasiswa aktif yang dapat diberi peringkat.']);
        }

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Ranking berhasil dihitung untuk '.$jumlah.' mahasiswa (bobot AHP, CR = '.number_format($ahp['cr'], 4).').');
    }

    /**
     * Simpan DASAR PEMBOBOTAN (alasan bobot) setiap kriteria — REVISI DOSEN 01-10-2026.
     * Nilai bobot tetap read-only (hasil AHP); yang disunting hanya teks alasannya.
     */
    public function simpanDasar(Request $request)
    {
        $kode = array_keys(config('saw.kriteria'));

        $aturan = ['dasar' => ['required', 'array']];
        foreach ($kode as $k) {
            $aturan['dasar.'.$k] = ['required', 'string', 'min:20', 'max:2000'];
        }

        $data = $request->validate($aturan, [
            'dasar.*.required' => 'Dasar pembobotan setiap kriteria wajib diisi.',
            'dasar.*.min' => 'Dasar pembobotan minimal 20 karakter agar alasan bobot jelas.',
            'dasar.*.max' => 'Dasar pembobotan maksimal 2000 karakter.',
        ]);

        $this->ranking->bobot(); // pastikan baris kriteria ada

        foreach ($kode as $k) {
            RankingBobot::where('kode', $k)->update(['dasar_pembobotan' => trim($data['dasar'][$k])]);
        }

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Dasar pembobotan kriteria berhasil disimpan.');
    }

    /** Kosongkan tabel ranking. */
    public function reset()
    {
        Ranking::query()->delete();

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Data ranking berhasil dikosongkan.');
    }

    /**
     * Kalimat dasar perbandingan AHP per kriteria, dibentuk dari matriks berpasangan.
     * Contoh: "C1 dinilai 2x lebih penting dari C2 (sedikit lebih penting ...), ..."
     */
    private function dasarPerbandingan(array $ahp): array
    {
        $kriteria = config('saw.kriteria');
        $skala = config('saw.ahp.skala');
        $hasil = [];

        foreach ($ahp['kode'] as $baris) {
            $kalimat = [];
            foreach ($ahp['kode'] as $kolom) {
                $nilai = $ahp['matriks'][$baris][$kolom];
                if ($baris === $kolom || $nilai <= 1) {
                    continue;
                }
                $bulat = (int) round($nilai);
                $kalimat[] = $bulat.'x terhadap '.$kriteria[$kolom]['nama'].' ('.($skala[$bulat] ?? 'lebih penting').')';
            }

            $hasil[$baris] = $kalimat
                ? 'Pada matriks AHP, '.$kriteria[$baris]['nama'].' dinilai lebih penting '.implode('; ', $kalimat).'.'
                : 'Pada matriks AHP, '.$kriteria[$baris]['nama'].' tidak dinilai lebih penting daripada kriteria lain, sehingga bobotnya paling kecil.';
        }

        return $hasil;
    }
}
