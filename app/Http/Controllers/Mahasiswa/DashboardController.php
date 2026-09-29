<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use App\Models\User;
use App\Services\NotifikasiService;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __construct(
        private readonly RankingService $ranking,
        private readonly NotifikasiService $notifikasi,
    ) {}

    /** Data mahasiswa milik user yang sedang login. */
    private function mahasiswa(): ?Mahasiswa
    {
        return Auth::user()?->mahasiswa;
    }

    /* ---------------- Dashboard ---------------- */

    public function index()
    {
        $mahasiswa = $this->mahasiswa();

        $prestasi = $mahasiswa
            ? $mahasiswa->prestasi()->latest('created_at')->get()
            : collect();

        $pengumuman = $mahasiswa
            ? $mahasiswa->pengumuman()
                ->terkirim()
                ->latest('created_at')
                ->take(4)
                ->get()
            : collect();

        return view('mahasiswa-dashboard', [
            'mahasiswa' => $mahasiswa,
            'prestasiTerbaru' => $prestasi->take(4),
            'jumlahDisetujui' => $prestasi->where('status', Prestasi::STATUS_DISETUJUI)->count(),
            'jumlahMenunggu' => $prestasi->where('status', Prestasi::STATUS_MENUNGGU)->count(),
            'peringkat' => $mahasiswa ? $this->ranking->peringkatMahasiswa($mahasiswa->nim) : null,
            'notifikasi' => $this->notifikasiUser()->take(3),
            'daftarPengumuman' => $pengumuman,
            // Nilai empat kriteria penilaian (dihitung langsung dari database).
            'nilaiKriteria' => $mahasiswa ? $this->ranking->nilaiKriteria($mahasiswa) : null,
            'daftarOrganisasi' => $mahasiswa ? $mahasiswa->organisasi()->get() : collect(),
        ]);
    }

    /* ---------------- Profil ---------------- */

    public function profil()
    {
        $mahasiswa = $this->mahasiswa();
        $organisasi = $mahasiswa ? $mahasiswa->organisasi()->get() : collect();

        return view('mahasiswa-profile', [
            'mahasiswa' => $mahasiswa,
            'daftarOrganisasi' => $organisasi,
            // Poin C4 dihitung sistem (RankingService), hanya ditampilkan (read-only).
            'poinOrganisasi' => $this->ranking->skorOrganisasi($organisasi),
            'daftarJabatan' => Organisasi::daftarJabatan(),
            'maksOrganisasi' => \App\Http\Controllers\Staff\MahasiswaController::MAKS_ORGANISASI,
        ]);
    }

    /**
     * REVISI 26-09-2026 — pengajuan perubahan TANPA verifikasi/persetujuan.
     * Alur: Mahasiswa -> Form -> VALIDASI -> data langsung tersimpan.
     * (Tabel riwayat pengajuan_perubahan dihapus karena tidak terdapat pada ERD.)
     */
    public function simpanPerubahan(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        if (! $mahasiswa) {
            return back()->withErrors(['email' => 'Akun Anda belum terhubung ke data mahasiswa.']);
        }

        $user = Auth::user();

        $data = $request->validate([
            // Satu email: email profil = email login (wajib email institusi dan belum dipakai akun lain).
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user->id_user, 'id_user'),
                User::aturanDomainEmail('mahasiswa'),
            ],
            'no_hp' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'kelas' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9 \-]+$/'],
        ], [
            'email.unique' => 'Email tersebut sudah digunakan akun lain.',
            'no_hp.regex' => 'Nomor telepon hanya boleh berisi angka (10–15 digit).',
            'kelas.regex' => 'Kelas hanya boleh berisi huruf, angka, spasi, atau tanda hubung.',
        ], [
            'email' => 'Email',
            'no_hp' => 'Nomor telepon',
            'kelas' => 'Kelas',
        ]);

        $lama = [
            'email' => (string) $user->email,
            'no_hp' => (string) $mahasiswa->no_hp,
            'kelas' => (string) $mahasiswa->kelas,
        ];
        $baru = [
            'email' => (string) $data['email'],
            'no_hp' => (string) ($data['no_hp'] ?? ''),
            'kelas' => (string) ($data['kelas'] ?? ''),
        ];

        if ($lama === $baru) {
            return back()->withErrors(['email' => 'Tidak ada data yang berubah.']);
        }

        DB::transaction(function () use ($mahasiswa, $user, $baru) {
            $mahasiswa->update([
                'email' => $baru['email'],
                'no_hp' => $baru['no_hp'] !== '' ? $baru['no_hp'] : null,
                'kelas' => $baru['kelas'] !== '' ? $baru['kelas'] : null,
            ]);

            // Email login selalu sama dengan email profil.
            $user->update(['email' => $baru['email']]);
        });

        return redirect()
            ->route('mahasiswa-profile')
            ->with('success', 'Perubahan data diri berhasil disimpan.');
    }

    /**
     * Keaktifan Organisasi (kriteria C4) diinput mahasiswa dari Profil Saya.
     * REVISI 26-09-2026: tanpa verifikasi — cukup validasi, lalu langsung tersimpan
     * ke tabel `organisasi` dan dihitung pada perhitungan ranking SAW berikutnya.
     */
    public function simpanOrganisasi(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        if (! $mahasiswa) {
            return back()->withErrors(['organisasi' => 'Akun Anda belum terhubung ke data mahasiswa.']);
        }

        $maks = \App\Http\Controllers\Staff\MahasiswaController::MAKS_ORGANISASI;

        $data = $request->validate([
            'organisasi' => ['nullable', 'array', 'max:'.$maks],
            'organisasi.*.nama_organisasi' => ['nullable', 'string', 'max:150', 'required_with:organisasi.*.jabatan'],
            'organisasi.*.jabatan' => ['nullable', Rule::in(array_keys(Organisasi::daftarJabatan())), 'required_with:organisasi.*.nama_organisasi'],
        ], [
            'organisasi.*.nama_organisasi.required_with' => 'Nama organisasi wajib diisi bila jabatan dipilih.',
            'organisasi.*.jabatan.required_with' => 'Jabatan organisasi wajib dipilih bila nama organisasi diisi.',
            'organisasi.*.jabatan.in' => 'Jabatan organisasi tidak sesuai skema poin.',
        ]);

        $bentuk = fn ($daftar) => collect($daftar)
            ->map(fn ($o) => [
                'nama_organisasi' => trim((string) ($o['nama_organisasi'] ?? '')),
                'jabatan' => (string) ($o['jabatan'] ?? ''),
            ])
            ->filter(fn ($o) => $o['nama_organisasi'] !== '' && $o['jabatan'] !== '')
            ->values()
            ->all();

        $lama = $bentuk($mahasiswa->organisasi()->get(['nama_organisasi', 'jabatan'])->toArray());
        $baru = $bentuk($data['organisasi'] ?? []);

        if ($lama === $baru) {
            return back()->withErrors(['organisasi' => 'Tidak ada data organisasi yang berubah.']);
        }

        DB::transaction(function () use ($mahasiswa, $baru) {
            $mahasiswa->organisasi()->delete();

            foreach ($baru as $o) {
                Organisasi::create([
                    'nim' => $mahasiswa->nim,
                    'nama_organisasi' => $o['nama_organisasi'],
                    'jabatan' => $o['jabatan'],
                ]);
            }
        });

        return redirect()
            ->route('mahasiswa-profile')
            ->with('success', 'Data Keaktifan Organisasi berhasil disimpan. Poin dihitung pada perhitungan ranking berikutnya.');
    }

    /* ---------------- Prestasi ---------------- */

    public function prestasi(Request $request)
    {
        $mahasiswa = $this->mahasiswa();
        $status = $request->query('status');

        $daftar = $mahasiswa
            ? $mahasiswa->prestasi()
                ->when($status, fn ($q) => $q->where('status', $status))
                ->latest('created_at')
                ->get()
            : collect();

        return view('mahasiswa-prestasi', [
            'daftarPrestasi' => $daftar,
            'status' => $status,
        ]);
    }

    public function formPrestasi()
    {
        return view('mahasiswa-ajukan-prestasi', [
            'mahasiswa' => $this->mahasiswa(),
        ]);
    }

    /** Simpan pengajuan prestasi ke database dengan status "menunggu". */
    public function simpanPrestasi(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        if (! $mahasiswa) {
            return back()->withErrors(['judul' => 'Akun Anda belum terhubung ke data mahasiswa.']);
        }

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Prestasi::KATEGORI)],
            'tingkat' => ['required', Rule::in(Prestasi::daftarTingkat())],
            'penyelenggara' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [], [
            'kategori' => 'Kategori prestasi',
            'tingkat' => 'Tingkat prestasi',
            'judul' => 'Nama prestasi',
            'tanggal' => 'Tanggal prestasi',
            'dokumen' => 'Berkas bukti',
        ]);

        $prestasi = Prestasi::create([
            'nim' => $mahasiswa->nim,
            'judul' => $data['judul'],
            'kategori' => $data['kategori'],
            'tingkat' => $data['tingkat'],
            'penyelenggara' => $data['penyelenggara'] ?? null,
            'tanggal' => $data['tanggal'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'dokumen' => $request->hasFile('dokumen')
                ? $request->file('dokumen')->store('prestasi', 'public')
                : null,
            'status' => Prestasi::STATUS_MENUNGGU,
        ]);

        $this->notifikasi->kirim(
            $mahasiswa->nim,
            'Prestasi sedang diverifikasi',
            'Pengajuan "'.$prestasi->judul.'" menunggu verifikasi Staff Prodi.',
            $prestasi->kategori
        );

        return redirect()
            ->route('mahasiswa-ajukan-prestasi')
            ->with('success', 'Pengajuan prestasi berhasil dikirim. Status: menunggu.');
    }

    /* ---------------- Pengumuman ---------------- */

    public function pengumuman(Request $request)
    {
        $mahasiswa = $this->mahasiswa();
        $kategori = $request->query('kategori');
        if (! in_array($kategori, Pengumuman::daftarKategori(), true)) {
            $kategori = null;
        }

        $daftar = $mahasiswa
            ? $mahasiswa->pengumuman()
                ->with('prestasi')
                ->terkirim()
                ->when($kategori, fn ($k) => $k->where('kategori', $kategori))
                ->latest('created_at')
                ->get()
            : collect();

        // Tandai sudah dibaca (kolom pengumuman.dibaca_pada sesuai ERD).
        if ($mahasiswa) {
            $mahasiswa->pengumuman()
                ->terkirim()
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);
        }

        return view('mahasiswa-pengumuman', [
            'daftarPengumuman' => $daftar,
            'kategori' => $kategori,
        ]);
    }

    /* ---------------- Notifikasi ---------------- */

    public function notifikasi()
    {
        return view('mahasiswa-notifikasi', [
            'daftarNotifikasi' => $this->notifikasiUser(),
        ]);
    }

    public function bacaNotifikasi()
    {
        $nim = $this->mahasiswa()?->nim;

        if ($nim) {
            Pengumuman::notifikasiUntuk($nim)
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);
        }

        return redirect()
            ->route('mahasiswa-notifikasi')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function notifikasiUser()
    {
        // ERD: notifikasi = atribut PENGUMUMAN milik mahasiswa (pengumuman.nim).
        $nim = $this->mahasiswa()?->nim;

        return $nim
            ? Pengumuman::notifikasiUntuk($nim)->latest('created_at')->latest('id_pengumuman')->get()
            : collect();
    }
}
