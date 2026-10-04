<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Prestasi;
use App\Models\Ranking;
use App\Models\RankingBobot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public const KRITERIA = [
        'Nilai Akademik',
        'Prestasi Akademik',
        'Prestasi Non-Akademik',
        'Keaktifan Organisasi',
    ];

    public const KODE_KE_KOLOM = [
        'C1' => 'c1',
        'C2' => 'c2',
        'C3' => 'c3',
        'C4' => 'c4',
    ];

    public function bobot(): Collection
    {
        $bobot = RankingBobot::orderBy('kode')->get()->whereNotNull('kode')->keyBy('kode');

        if ($bobot->count() < count(config('saw.kriteria'))) {
            $this->sinkronBobot();
            $bobot = RankingBobot::orderBy('kode')->get()->whereNotNull('kode')->keyBy('kode');
        }

        return $bobot;
    }

    public function sinkronBobot(): void
    {
        foreach (config('saw.kriteria') as $kode => $k) {
            $baris = RankingBobot::updateOrCreate(
                ['kode' => $kode],
                ['kriteria' => $k['nama'], 'bobot' => $k['bobot'], 'tipe_bobot' => $k['tipe']]
            );

            if (blank($baris->dasar_pembobotan) && filled($k['dasar'] ?? null)) {
                $baris->update(['dasar_pembobotan' => $k['dasar']]);
            }
        }
    }

    public function terapkanBobot(array $bobotPerKode): void
    {
        $this->bobot();

        foreach ($bobotPerKode as $kode => $nilai) {
            RankingBobot::where('kode', $kode)->update(['bobot' => $nilai]);
        }
    }

    public function totalBobot(?Collection $bobot = null): float
    {
        $bobot ??= $this->bobot();

        return round((float) $bobot->sum(fn ($b) => (float) $b->bobot), 4);
    }

    public function skorPrestasi(Collection $prestasi, string $kategori): float
    {
        $cocok = $prestasi->where('kategori', $kategori);

        if ($cocok->isEmpty()) {
            return 0.0;
        }

        $skema = Prestasi::skema($kategori);
        $dasar = $cocok->max(fn (Prestasi $p) => $p->poin);
        $tambahan = min($cocok->count() - 1, (int) $skema['maks_tambahan']);

        return (float) min(config('saw.skor_maksimal'), $dasar + $tambahan * $skema['bonus_per_tambahan']);
    }

    public function skorOrganisasi(Collection $organisasi): float
    {
        $skor = $organisasi->map(fn ($o) => $o->poin)->sortDesc()->values();

        if ($skor->isEmpty()) {
            return 0.0;
        }

        $pertama = (int) $skor->get(0, 0);
        $kedua = (int) $skor->get(1, 0);
        $bonus = (int) round($kedua * (float) config('saw.organisasi.bonus_organisasi_kedua'));

        return (float) min(config('saw.skor_maksimal'), $pertama + $bonus);
    }

    public function nilaiKriteria(Mahasiswa $m): array
    {
        $prestasi = $m->relationLoaded('prestasiDisetujui') ? $m->prestasiDisetujui : $m->prestasiDisetujui()->get();
        $organisasi = $m->relationLoaded('organisasi') ? $m->organisasi : $m->organisasi()->get();

        return [
            'c1' => (float) ($m->ipk ?? 0),
            'c2' => $this->skorPrestasi($prestasi, Prestasi::KATEGORI_AKADEMIK),
            'c3' => $this->skorPrestasi($prestasi, Prestasi::KATEGORI_NON_AKADEMIK),
            'c4' => $this->skorOrganisasi($organisasi),
        ];
    }

    public function hitung(?Collection $bobot = null): Collection
    {
        $bobot ??= $this->bobot();

        $mahasiswa = Mahasiswa::aktif()
            ->with(['prestasiDisetujui', 'organisasi'])
            ->orderBy('nama')
            ->get();

        if ($mahasiswa->isEmpty()) {
            return collect();
        }

        $baris = $mahasiswa->map(fn (Mahasiswa $m) => ['mahasiswa' => $m] + $this->nilaiKriteria($m));

        $maks = [];
        foreach (self::KODE_KE_KOLOM as $kolom) {
            $maks[$kolom] = (float) $baris->max($kolom);
        }

        $baris = $baris->map(function (array $r) use ($maks, $bobot) {
            $vi = 0.0;
            foreach (self::KODE_KE_KOLOM as $kode => $kolom) {
                $rij = $maks[$kolom] > 0 ? $r[$kolom] / $maks[$kolom] : 0.0;
                $r['r'.substr($kolom, 1)] = $rij;

                $vi += (float) ($bobot->get($kode)->bobot ?? 0) * $rij;
            }
            $r['skor'] = $vi * 100;

            return $r;
        });

        $urut = $baris->sort(function ($a, $b) {
            $selisih = round($b['skor'], 10) <=> round($a['skor'], 10);

            return $selisih !== 0 ? $selisih : strcmp($a['mahasiswa']->nama, $b['mahasiswa']->nama);
        })->values();

        $sebelumnya = null;
        $peringkat = 0;

        return $urut->map(function (array $r, int $i) use (&$sebelumnya, &$peringkat) {
            $nilai = round($r['skor'], 10);
            if ($nilai !== $sebelumnya) {
                $peringkat = $i + 1;
                $sebelumnya = $nilai;
            }

            return $this->barisTampil($r['mahasiswa'], $r, $peringkat);
        });
    }

    public function simpan(int $tahun): int
    {
        $bobot = $this->bobot();
        $baris = $this->hitung($bobot);

        if ($baris->isEmpty()) {
            return 0;
        }

        $bobotId = $bobot->first()?->id_ranking_bobot;

        DB::transaction(function () use ($baris, $tahun, $bobotId) {
            Ranking::where('tahun', $tahun)->delete();

            foreach ($baris as $r) {
                Ranking::create([
                    'nim' => $r['nim'],
                    'ranking_bobot_id' => $bobotId,
                    'nilai_ipk' => $r['c1'],
                    'poin_prestasi_akademik' => $r['c2'],
                    'poin_prestasi_nonakademik' => $r['c3'],
                    'poin_keaktifan_organisasi' => $r['c4'],
                    'normalisasi_nilai_ipk' => round($r['r1'], 6),
                    'normalisasi_prestasi_akademik' => round($r['r2'], 6),
                    'normalisasi_prestasi_nonakademik' => round($r['r3'], 6),
                    'normalisasi_keaktifan_organisasi' => round($r['r4'], 6),
                    'peringkat' => $r['peringkat'],
                    'nilai_akhir' => round($r['skor'], 6),
                    'tahun' => $tahun,
                ]);
            }
        });

        return $baris->count();
    }

    public function untukPublik(?int $tahun = null): Collection
    {
        $tahun ??= Ranking::max('tahun');

        if ($tahun === null) {
            return collect();
        }

        return Ranking::with('mahasiswa')
            ->where('tahun', $tahun)
            ->orderBy('peringkat')
            ->get()
            ->filter(fn (Ranking $r) => $r->mahasiswa !== null)
            ->map(fn (Ranking $r) => $this->barisTampil($r->mahasiswa, [
                'c1' => (float) $r->nilai_ipk,
                'c2' => (float) $r->poin_prestasi_akademik,
                'c3' => (float) $r->poin_prestasi_nonakademik,
                'c4' => (float) $r->poin_keaktifan_organisasi,
                'r1' => (float) $r->normalisasi_nilai_ipk,
                'r2' => (float) $r->normalisasi_prestasi_akademik,
                'r3' => (float) $r->normalisasi_prestasi_nonakademik,
                'r4' => (float) $r->normalisasi_keaktifan_organisasi,
                'skor' => (float) $r->nilai_akhir,
            ], $r->peringkat) + ['tahun' => $r->tahun])
            ->values();
    }

    public function tahunTerakhir(): ?int
    {
        $tahun = Ranking::max('tahun');

        return $tahun === null ? null : (int) $tahun;
    }

    public function peringkatMahasiswa(string $nim): ?int
    {
        $tahun = Ranking::max('tahun');

        if ($tahun === null) {
            return null;
        }

        return Ranking::where('tahun', $tahun)
            ->where('nim', $nim)
            ->value('peringkat');
    }

    private function barisTampil(Mahasiswa $m, array $n, int $peringkat): array
    {
        return [
            'mahasiswa' => $m,
            'nama' => $m->nama,
            'nim' => $m->nim,
            'kelas' => $m->kelas,
            'angkatan' => $m->angkatan,
            'foto' => $m->foto,
            'ipk' => (float) ($m->ipk ?? 0),

            'c1' => $n['c1'],
            'c2' => $n['c2'],
            'c3' => $n['c3'],
            'c4' => $n['c4'],

            'nilai_akademik' => $n['c1'],
            'prestasi_akademik' => $n['c2'],
            'prestasi_non_akademik' => $n['c3'],
            'keaktifan_organisasi' => $n['c4'],

            'r1' => $n['r1'],
            'r2' => $n['r2'],
            'r3' => $n['r3'],
            'r4' => $n['r4'],

            'skor' => $n['skor'],
            'peringkat' => $peringkat,
        ];
    }
}
