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

    private function mahasiswa(): ?Mahasiswa
    {
        return Auth::user()?->mahasiswa;
    }

    public function index()
    {
        $mahasiswa = $this->mahasiswa();

        $prestasi = $mahasiswa
            ? $mahasiswa->prestasi()->latest('created_at')->get()
            : collect();

        $semuaPengumuman = $mahasiswa ? $this->pengumumanMahasiswa($mahasiswa) : collect();

        return view('mahasiswa-dashboard', [
            'mahasiswa' => $mahasiswa,
            'prestasiTerbaru' => $prestasi->take(4),
            'jumlahDisetujui' => $prestasi->where('status', Prestasi::STATUS_DISETUJUI)->count(),
            'jumlahMenunggu' => $prestasi->where('status', Prestasi::STATUS_MENUNGGU)->count(),
            'peringkat' => $mahasiswa ? $this->ranking->peringkatMahasiswa($mahasiswa->nim) : null,
            'daftarPengumuman' => $semuaPengumuman->take(4),
            'jumlahBelumDibaca' => $semuaPengumuman->filter(fn (Pengumuman $g) => $g->belum_dibaca)->count(),
            'nilaiKriteria' => $mahasiswa ? $this->ranking->nilaiKriteria($mahasiswa) : null,
            'daftarOrganisasi' => $mahasiswa ? $mahasiswa->organisasi()->get() : collect(),
        ]);
    }

    public function profil()
    {
        $mahasiswa = $this->mahasiswa();
        $organisasi = $mahasiswa ? $mahasiswa->organisasi()->get() : collect();

        return view('mahasiswa-profile', [
            'mahasiswa' => $mahasiswa,
            'daftarOrganisasi' => $organisasi,
            'poinOrganisasi' => $this->ranking->skorOrganisasi($organisasi),
            'daftarJabatan' => Organisasi::daftarJabatan(),
        ]);
    }

    public function simpanPerubahan(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        if (! $mahasiswa) {
            return back()->withErrors(['email' => 'Akun Anda belum terhubung ke data mahasiswa.']);
        }

        $user = Auth::user();

        $data = $request->validate([
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

            $user->update(['email' => $baru['email']]);
        });

        return redirect()
            ->route('mahasiswa-profile')
            ->with('success', 'Perubahan data diri berhasil disimpan.');
    }

    public function simpanOrganisasi(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        if (! $mahasiswa) {
            return back()->withErrors(['organisasi' => 'Akun Anda belum terhubung ke data mahasiswa.']);
        }

        $data = $request->validate([
            'organisasi' => ['required', 'array', 'min:1', 'max:20'],
            'organisasi.*.nama_organisasi' => ['nullable', 'string', 'max:150', 'required_with:organisasi.*.jabatan'],
            'organisasi.*.jabatan' => ['nullable', Rule::in(array_keys(Organisasi::daftarJabatan())), 'required_with:organisasi.*.nama_organisasi'],
        ], [
            'organisasi.required' => 'Isi minimal satu organisasi.',
            'organisasi.max' => 'Maksimal 20 organisasi sekali simpan.',
            'organisasi.*.nama_organisasi.required_with' => 'Nama organisasi wajib diisi bila jabatan dipilih.',
            'organisasi.*.jabatan.required_with' => 'Jabatan organisasi wajib dipilih bila nama organisasi diisi.',
            'organisasi.*.jabatan.in' => 'Jabatan organisasi tidak sesuai skema poin.',
        ]);

        $baru = collect($data['organisasi'])
            ->map(fn ($o) => [
                'nama_organisasi' => trim((string) ($o['nama_organisasi'] ?? '')),
                'jabatan' => (string) ($o['jabatan'] ?? ''),
            ])
            ->filter(fn ($o) => $o['nama_organisasi'] !== '' && $o['jabatan'] !== '')
            ->values();

        if ($baru->isEmpty()) {
            return back()->withErrors(['organisasi' => 'Isi minimal satu organisasi beserta jabatannya.'])->withInput();
        }

        DB::transaction(function () use ($mahasiswa, $baru) {
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
            ->with('success', $baru->count().' organisasi berhasil ditambahkan. Poin dihitung pada perhitungan ranking berikutnya.');
    }

    public function ubahOrganisasi(Request $request, Organisasi $organisasi)
    {
        $this->pastikanMilikSendiri($organisasi);

        $data = $request->validate([
            'nama_organisasi' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', Rule::in(array_keys(Organisasi::daftarJabatan()))],
        ], [
            'jabatan.in' => 'Jabatan organisasi tidak sesuai skema poin.',
        ], [
            'nama_organisasi' => 'Nama organisasi',
            'jabatan' => 'Jabatan organisasi',
        ]);

        $organisasi->update([
            'nama_organisasi' => trim($data['nama_organisasi']),
            'jabatan' => $data['jabatan'],
        ]);

        return redirect()
            ->route('mahasiswa-profile')
            ->with('success', 'Data organisasi berhasil diperbarui. Poin dihitung pada perhitungan ranking berikutnya.');
    }

    public function hapusOrganisasi(Organisasi $organisasi)
    {
        $this->pastikanMilikSendiri($organisasi);

        $nama = $organisasi->nama_organisasi;
        $organisasi->delete();

        return redirect()
            ->route('mahasiswa-profile')
            ->with('success', 'Organisasi "'.$nama.'" berhasil dihapus.');
    }

    private function pastikanMilikSendiri(Organisasi $organisasi): void
    {
        abort_unless($this->mahasiswa() && $organisasi->nim === $this->mahasiswa()->nim, 403);
    }

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
        ], [
            'dokumen.mimes' => 'Sertifikat harus berupa PDF atau gambar (JPG/PNG).',
            'dokumen.max' => 'Ukuran sertifikat maksimal 4 MB.',
        ], [
            'kategori' => 'Kategori prestasi',
            'tingkat' => 'Tingkat prestasi',
            'judul' => 'Nama prestasi',
            'tanggal' => 'Tanggal prestasi',
            'dokumen' => 'Sertifikat',
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

    public function pengumuman(Request $request)
    {
        $mahasiswa = $this->mahasiswa();
        $kategori = $request->query('kategori');
        if (! in_array($kategori, Pengumuman::daftarKategori(), true)) {
            $kategori = null;
        }

        $daftar = $mahasiswa ? $this->pengumumanMahasiswa($mahasiswa, $kategori) : collect();

        if ($mahasiswa) {
            $this->tandaiSemuaDibaca($mahasiswa->nim);
        }

        return view('mahasiswa-pengumuman', [
            'daftarPengumuman' => $daftar,
            'kategori' => $kategori,
        ]);
    }

    private function pengumumanMahasiswa(Mahasiswa $mahasiswa, ?string $kategori = null)
    {
        $staff = $mahasiswa->pengumumanDiterima()
            ->with('prestasi')
            ->terkirim()
            ->when($kategori, fn ($q) => $q->where('pengumuman.kategori', $kategori))
            ->get();

        $sistem = Pengumuman::notifikasiUntuk($mahasiswa->nim)
            ->with('prestasi')
            ->when($kategori, fn ($q) => $q->where('pengumuman.kategori', $kategori))
            ->get();

        return $staff->concat($sistem)
            ->sortByDesc(fn (Pengumuman $n) => sprintf('%s-%010d', optional($n->tanggal_dikirim ?? $n->created_at)->format('YmdHis'), $n->id_pengumuman))
            ->values();
    }

    private function tandaiSemuaDibaca(string $nim): void
    {
        Pengumuman::notifikasiUntuk($nim)
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);

        DB::table('pengumuman_penerima')
            ->where('nim', $nim)
            ->whereNull('dibaca_pada')
            ->whereIn('pengumuman_id', Pengumuman::query()->terkirim()->select('id_pengumuman'))
            ->update(['dibaca_pada' => now(), 'updated_at' => now()]);
    }
}
