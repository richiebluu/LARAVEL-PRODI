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

    /**
     * Halaman Profil Prodi (Tentang).
     * REVISI 26-09-2026: Profil memuat Akreditasi (atribut profil), Struktur
     * Organisasi, dan Dosen Pengajar dalam satu bagian.
     * REVISI 27-09-2026: Tentang berisi Visi, Misi, dan statistik (mahasiswa, dosen,
     * alumni, prestasi); halaman /visi-misi diarahkan ke /profil#visi-misi.
     */
    public function index()
    {
        $prodi = ProgramStudi::first();

        return view('profil', [
            'prodi' => $prodi,
            // REVISI DOSEN 01-10-2026: akreditasi yang berlaku (Terakreditasi, tanggal terbaru).
            'akreditasi' => Akreditasi::utama(),
            'strukturOrganisasi' => $this->struktur($prodi)->take(4),
            'daftarDosen' => Dosen::query()->urutStatus()->orderBy('nama')->get(),
            'jumlahMahasiswa' => $this->statistik->mahasiswaAktif(),
            'jumlahDosen' => $this->statistik->dosenAktif(),
            'jumlahAlumni' => $this->statistik->alumni(),
            'jumlahPrestasi' => $this->statistik->prestasiDisetujui(),
        ]);
    }

    /** Halaman Struktur Organisasi (bagian dari Profil). */
    public function strukturOrganisasi()
    {
        $prodi = ProgramStudi::first();

        return view('struktur-organisasi', [
            'prodi' => $prodi,
            'strukturOrganisasi' => $this->struktur($prodi),
        ]);
    }

    /** Struktur organisasi berurutan (milik prodi, termasuk baris lama tanpa prodi). */
    private function struktur(?ProgramStudi $prodi)
    {
        return StrukturOrganisasi::with('dosen')
            ->when($prodi, fn ($q) => $q->where(fn ($w) => $w->where('program_studi_id', $prodi->id_program_studi)->orWhereNull('program_studi_id')))
            ->urut()
            ->get();
    }

    /**
     * Halaman Prospek Lulusan.
     * REVISI 28-09-2026: data dari tabel prospek_lulusan (dikelola Staff Prodi),
     * card mengikuti halaman Lowongan Kerja, filter per kategori.
     */
    public function prospekLulusan(\Illuminate\Http\Request $request)
    {
        $semua = ProspekLulusan::aktif()->urut()->get();
        $daftarKategori = $semua->pluck('kategori')->unique()->values();

        $kategori = $request->query('kategori');
        if (! $daftarKategori->contains($kategori)) {
            $kategori = null;
        }

        return view('prospek-lulusan', [
            'daftarProspek' => $kategori ? $semua->where('kategori', $kategori)->values() : $semua,
            'daftarKategori' => $daftarKategori,
            'kategori' => $kategori,
            'jumlahProspek' => $semua->count(),
        ]);
    }

    /**
     * Halaman Sarana & Prasarana (REVISI 28-09-2026 tahap 2, Profil > Sarana & Prasarana).
     * Data dari tabel sarana_prasarana (dikelola Staff Prodi), laboratorium ditampilkan lebih dulu.
     */
    public function saranaPrasarana(\Illuminate\Http\Request $request)
    {
        $semua = SaranaPrasarana::aktif()->urut()->get();
        $daftarJenis = collect(array_keys(SaranaPrasarana::JENIS))
            ->filter(fn ($j) => $semua->contains('jenis', $j))
            ->values();

        $jenis = $request->query('jenis');
        if (! $daftarJenis->contains($jenis)) {
            $jenis = null;
        }

        return view('sarana-prasarana', [
            'daftarSarana' => $jenis ? $semua->where('jenis', $jenis)->values() : $semua,
            'daftarJenis' => $daftarJenis,
            'jenis' => $jenis,
            'jumlahLab' => $semua->where('jenis', SaranaPrasarana::JENIS_LAB)->count(),
            'jumlahSarana' => $semua->count(),
            'totalKapasitasLab' => (int) $semua->where('jenis', SaranaPrasarana::JENIS_LAB)->sum('kapasitas'),
        ]);
    }
}
