<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\ProspekLulusan;
use App\Models\SaranaPrasarana;
use App\Models\StrukturOrganisasi;
use App\Services\StatistikService;

class ProgramStudiController extends Controller
{
    public function __construct(private readonly StatistikService $statistik) {}

    public function index()
    {
        $prodi = ProgramStudi::first();

        return view('profil', [
            'prodi' => $prodi,
            'akreditasi' => Akreditasi::utama(),
            'strukturOrganisasi' => $this->struktur($prodi)->take(4),
            'daftarDosen' => Dosen::query()->urutStatus()->orderBy('nama')->get(),
            'jumlahMahasiswa' => $this->statistik->mahasiswaAktif(),
            'jumlahDosen' => $this->statistik->dosenAktif(),
            'jumlahAlumni' => $this->statistik->alumni(),
            'jumlahPrestasi' => $this->statistik->prestasiDisetujui(),
        ]);
    }

    public function strukturOrganisasi()
    {
        $prodi = ProgramStudi::first();

        $struktur = $this->struktur($prodi);

        return view('struktur-organisasi', [
            'prodi' => $prodi,
            'strukturOrganisasi' => $struktur,
            'tingkatStruktur' => $struktur->groupBy(fn ($s) => $s->tingkat)->sortKeys()->values(),
        ]);
    }

    private function struktur(?ProgramStudi $prodi)
    {
        return StrukturOrganisasi::with('dosen')
            ->when($prodi, fn ($q) => $q->where(fn ($w) => $w->where('program_studi_id', $prodi->id_program_studi)->orWhereNull('program_studi_id')))
            ->urut()
            ->get();
    }

    public function prospekLulusan()
    {
        $semua = ProspekLulusan::aktif()->urut()->get();

        return view('prospek-lulusan', [
            'daftarProspek' => $semua,
            'jumlahProspek' => $semua->count(),
        ]);
    }

    public function saranaPrasarana(\Illuminate\Http\Request $request)
    {
        $semua = SaranaPrasarana::aktif()->urut()->get();
        $daftarGedung = collect(SaranaPrasarana::GEDUNG)
            ->filter(fn ($g) => $semua->contains('gedung', $g))
            ->values();

        $gedung = $request->query('gedung');
        if (! $daftarGedung->contains($gedung)) {
            $gedung = null;
        }

        return view('sarana-prasarana', [
            'daftarSarana' => $gedung ? $semua->where('gedung', $gedung)->values() : $semua,
            'daftarGedung' => $daftarGedung,
            'gedung' => $gedung,
            'jumlahSarana' => $semua->count(),
            'jumlahGedung' => $daftarGedung->count(),
            'totalKapasitas' => (int) $semua->sum('kapasitas'),
        ]);
    }
}
