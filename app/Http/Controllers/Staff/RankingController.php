<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ranking;
use App\Models\RankingBobot;
use App\Services\RankingService;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $ranking) {}

    public function index()
    {
        $bobot = $this->ranking->bobot();

        $ahp = $this->hitungAHP(
            array_keys(config('saw.kriteria')),
            config('saw.ahp.perbandingan'),
        );

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

    public function hitungAHP(array $kode, array $perbandingan): array
    {
        $n = count($kode);

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
                    $matriks[$baris][$kolom] = 1.0;
                }
            }
        }

        $jumlahKolom = [];
        foreach ($kode as $kolom) {
            $jumlahKolom[$kolom] = 0.0;
            foreach ($kode as $baris) {
                $jumlahKolom[$kolom] += $matriks[$baris][$kolom];
            }
        }

        $normalisasi = [];
        foreach ($kode as $baris) {
            foreach ($kode as $kolom) {
                $normalisasi[$baris][$kolom] = $matriks[$baris][$kolom] / $jumlahKolom[$kolom];
            }
        }

        $jumlahBarisNormalisasi = [];
        $priorityVector = [];
        foreach ($kode as $baris) {
            $jumlahBarisNormalisasi[$baris] = array_sum($normalisasi[$baris]);
            $priorityVector[$baris] = $jumlahBarisNormalisasi[$baris] / $n;
        }

        $weightedSum = [];
        foreach ($kode as $baris) {
            $weightedSum[$baris] = 0.0;
            foreach ($kode as $kolom) {
                $weightedSum[$baris] += $matriks[$baris][$kolom] * $priorityVector[$kolom];
            }
        }

        $consistencyVector = [];
        foreach ($kode as $baris) {
            $consistencyVector[$baris] = $priorityVector[$baris] > 0
                ? $weightedSum[$baris] / $priorityVector[$baris]
                : 0.0;
        }

        $lambdaMax = array_sum($consistencyVector) / $n;

        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0.0;

        $ri = (float) (config('saw.ahp.indeks_random')[$n] ?? 0);
        $cr = $ri > 0 ? $ci / $ri : 0.0;

        $batasCr = (float) config('saw.ahp.batas_cr', 0.1);
        $konsisten = $cr <= $batasCr;

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

    public function generate(Request $request)
    {
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
        ]);

        $ahp = $this->hitungAHP(array_keys(config('saw.kriteria')), config('saw.ahp.perbandingan'));

        if (! $ahp['konsisten']) {
            return redirect()
                ->route('staff-ranking')
                ->withErrors(['bobot' => 'Matriks perbandingan AHP tidak konsisten (CR = '.number_format($ahp['cr'], 4).' > '.$ahp['batas_cr'].'). Perbaiki nilai perbandingan pada config/saw.php.']);
        }

        $this->ranking->terapkanBobot($ahp['bobot_dibulatkan']);

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
            ->with('success', 'Ranking berhasil dihitung untuk '.$jumlah.' mahasiswa (bobot AHP, CR = '.number_format($ahp['cr'], 4).').');
    }

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

        $this->ranking->bobot();

        foreach ($kode as $k) {
            RankingBobot::where('kode', $k)->update(['dasar_pembobotan' => trim($data['dasar'][$k])]);
        }

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Dasar pembobotan kriteria berhasil disimpan.');
    }

    public function reset()
    {
        Ranking::query()->delete();

        return redirect()
            ->route('staff-ranking')
            ->with('success', 'Data ranking berhasil dikosongkan.');
    }

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
