<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\MengimporCsv;
use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\User;
use App\Services\RankingService;
use App\Support\ImporCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    use MengimporCsv;

    /** Kolom CSV impor mahasiswa (urutan template). REVISI 28-09-2026 tahap 2. */
    public const KOLOM_CSV = ['nim', 'nama', 'email', 'angkatan', 'kelas', 'no_hp', 'ipk', 'status_mahasiswa', 'password'];

    /** Jumlah baris organisasi pada form (Excel acuan memuat 2 organisasi). */
    public const MAKS_ORGANISASI = 2;

    /**
     * REVISI DOSEN 01-10-2026: jumlah data per halaman dapat dipilih Staff Prodi.
     * Default 10 data; pilihan 5 / 10 / 15 / 20 (query parameter ?per_page=).
     */
    public const PILIHAN_PER_HALAMAN = [5, 10, 15, 20];
    public const PER_HALAMAN_BAWAAN = 10;

    public function __construct(private readonly RankingService $ranking) {}

    /** Daftar mahasiswa: search + filter angkatan + jumlah per halaman + pagination Laravel. */
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $angkatan = $request->query('angkatan');

        // Nilai di luar pilihan (mis. ?per_page=1000) dikembalikan ke bawaan 10.
        $perHalaman = (int) $request->query('per_page', self::PER_HALAMAN_BAWAAN);
        if (! in_array($perHalaman, self::PILIHAN_PER_HALAMAN, true)) {
            $perHalaman = self::PER_HALAMAN_BAWAAN;
        }

        $mahasiswa = Mahasiswa::query()
            ->with(['user', 'organisasi', 'prestasiDisetujui'])
            ->withCount([
                'prestasi',
                'prestasi as prestasi_disetujui_count' => fn ($q) => $q->where('status', \App\Models\Prestasi::STATUS_DISETUJUI),
            ])
            ->cari($cari)
            ->when($angkatan, fn ($q) => $q->where('angkatan', $angkatan))
            ->orderBy('nama')
            ->paginate($perHalaman)
            ->withQueryString(); // q, angkatan, per_page ikut terbawa saat pindah halaman

        $daftarAngkatan = Mahasiswa::query()
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderBy('angkatan')
            ->pluck('angkatan');

        return view('staff-mahasiswa', [
            'daftarMahasiswa' => $mahasiswa,
            'daftarAngkatan' => $daftarAngkatan,
            'cari' => $cari,
            'angkatan' => $angkatan,
            'perHalaman' => $perHalaman,
            'pilihanPerHalaman' => self::PILIHAN_PER_HALAMAN,
            'jumlah' => $mahasiswa->total(),
            'daftarJabatan' => Organisasi::daftarJabatan(),
            'maksOrganisasi' => self::MAKS_ORGANISASI,
            'ranking' => $this->ranking,
        ]);
    }

    /** Simpan mahasiswa baru + akun user-nya. */
    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'], // satu email: email login = email profil
                'password' => $data['password'],
                'role' => 'mahasiswa',
            ]);

            $mahasiswa = Mahasiswa::create([
                'nim' => $data['nim'],
                'user_id' => $user->id_user,
                'nama' => $data['nama'],
                'foto' => $this->simpanFoto($request),
                'angkatan' => $data['angkatan'] ?? null,
                'kelas' => $data['kelas'] ?? null,
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'ipk' => $data['ipk'] ?? null,
                'status_mahasiswa' => $data['status_mahasiswa'],
            ]);

            $this->simpanOrganisasi($mahasiswa, $data['organisasi'] ?? []);
        });

        return $this->kembaliKeDaftar()
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /** Perbarui data mahasiswa. */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $data = $this->validasi($request, $mahasiswa);

        DB::transaction(function () use ($data, $request, $mahasiswa) {
            $foto = $this->simpanFoto($request);

            $mahasiswa->update([
                'nim' => $data['nim'],
                'nama' => $data['nama'],
                'foto' => $foto ?? $mahasiswa->foto,
                'angkatan' => $data['angkatan'] ?? null,
                'kelas' => $data['kelas'] ?? null,
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'ipk' => $data['ipk'] ?? null,
                'status_mahasiswa' => $data['status_mahasiswa'],
            ]);

            $this->simpanOrganisasi($mahasiswa, $data['organisasi'] ?? []);

            if ($mahasiswa->user) {
                // Email login selalu disamakan dengan email profil (satu sumber email).
                $mahasiswa->user->update(array_filter([
                    'name' => $data['nama'],
                    'email' => $data['email'],
                    'password' => $data['password'] ?? null,
                ]));
            }
        });

        return $this->kembaliKeDaftar()
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /** Hapus mahasiswa beserta akun user-nya. */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $nama = $mahasiswa->nama;

        DB::transaction(function () use ($mahasiswa) {
            $user = $mahasiswa->user;
            $mahasiswa->delete();
            $user?->delete();
        });

        return $this->kembaliKeDaftar()
            ->with('success', 'Data mahasiswa '.$nama.' berhasil dihapus.');
    }

    /**
     * Kembali ke daftar mahasiswa dengan pencarian, filter, jumlah per halaman, dan
     * halaman yang sama seperti sebelum menyimpan/menghapus (tidak reset ke 10 data).
     */
    private function kembaliKeDaftar()
    {
        $sebelumnya = url()->previous();
        $daftar = route('staff-mahasiswa');

        return str_starts_with($sebelumnya, $daftar)
            ? redirect()->to($sebelumnya)
            : redirect()->route('staff-mahasiswa');
    }

    /** Unduh template CSV mahasiswa. */
    public function template()
    {
        return ImporCsv::template('template-mahasiswa.csv', self::KOLOM_CSV);
    }

    /**
     * Impor CSV mahasiswa + akun login-nya (REVISI 28-09-2026 tahap 2).
     * Aturan validasi sama dengan form Tambah Mahasiswa. NIM yang sudah terdaftar dilewati
     * (bawaan) atau diperbarui. Password kosong -> password awal = NIM (min. 8 karakter);
     * mahasiswa juga dapat masuk dengan Google memakai email @mhs.politala.ac.id.
     * Foto dan Keaktifan Organisasi tetap diisi lewat form (tidak diimpor).
     */
    public function impor(Request $request)
    {
        return $this->prosesImporCsv($request, [
            'kolom' => self::KOLOM_CSV,
            'wajib' => ['nim', 'nama', 'email'],
            'alias' => ['status' => 'status_mahasiswa', 'nama_mahasiswa' => 'nama', 'nomor_hp' => 'no_hp', 'no_telepon' => 'no_hp', 'telepon' => 'no_hp', 'tahun_angkatan' => 'angkatan'],
            'label' => 'mahasiswa',
            'route' => 'staff-mahasiswa',
            'unik_file' => ['email'],
            'siapkan' => function (array $d) {
                $d['status_mahasiswa'] = ImporCsv::cocokkan($d['status_mahasiswa'], Mahasiswa::LABEL_STATUS) ?? Mahasiswa::STATUS_AKTIF;
                $d['ipk'] = ImporCsv::desimal($d['ipk']);
                $d['email'] = $d['email'] === null ? null : strtolower($d['email']);
                $d['no_hp'] = $d['no_hp'] === null ? null : preg_replace('/[\s\-]/', '', $d['no_hp']);

                return $d;
            },
            'kunci' => fn (array $d) => $d['nim'],
            'cari' => fn (string $nim) => Mahasiswa::with('user')->where('nim', $nim)->first(),
            'aturan' => function (?Mahasiswa $lama, array $d) {
                $aturan = $this->aturanData($lama);
                // Password boleh kosong: dipakai NIM sebagai password awal (hanya data baru).
                $aturan['password'] = ['nullable', 'string', 'min:8'];
                if (! $lama && blank($d['password'] ?? null)) {
                    $aturan['nim'][] = 'min:8';
                }

                return $aturan;
            },
            'pesan' => $this->pesanValidasi() + ['nim.min' => 'Kolom password kosong, sedangkan NIM kurang dari 8 karakter untuk dijadikan password awal.'],
            'atribut' => $this->atributValidasi(),
            'simpan' => function (array $data, ?Mahasiswa $lama) {
                $profil = [
                    'nim' => $data['nim'],
                    'nama' => $data['nama'],
                    'angkatan' => $data['angkatan'] ?? null,
                    'kelas' => $data['kelas'] ?? null,
                    'email' => $data['email'],
                    'no_hp' => $data['no_hp'] ?? null,
                    'ipk' => $data['ipk'] ?? null,
                    'status_mahasiswa' => $data['status_mahasiswa'],
                ];

                if ($lama) {
                    $lama->update($profil);
                    $lama->user?->update(array_filter([
                        'name' => $data['nama'],
                        'email' => $data['email'],
                        'password' => $data['password'] ?? null,
                    ]));

                    return;
                }

                $user = User::create([
                    'name' => $data['nama'],
                    'email' => $data['email'],
                    'password' => filled($data['password'] ?? null) ? $data['password'] : $data['nim'],
                    'role' => 'mahasiswa',
                ]);
                Mahasiswa::create($profil + ['user_id' => $user->id_user]);
            },
        ]);
    }

    private function validasi(Request $request, ?Mahasiswa $mahasiswa = null): array
    {
        return $request->validate($this->aturanData($mahasiswa) + [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => [$mahasiswa ? 'nullable' : 'required', 'string', 'min:8'],

            // Keaktifan Organisasi: nama organisasi + jabatan (skema poin Excel).
            'organisasi' => ['nullable', 'array', 'max:'.self::MAKS_ORGANISASI],
            'organisasi.*.nama_organisasi' => ['nullable', 'string', 'max:150', 'required_with:organisasi.*.jabatan'],
            'organisasi.*.jabatan' => ['nullable', Rule::in(array_keys(Organisasi::daftarJabatan())), 'required_with:organisasi.*.nama_organisasi'],
        ], $this->pesanValidasi() + [
            'organisasi.*.nama_organisasi.required_with' => 'Nama organisasi wajib diisi bila jabatan dipilih.',
            'organisasi.*.jabatan.required_with' => 'Jabatan organisasi wajib dipilih bila nama organisasi diisi.',
            'organisasi.*.jabatan.in' => 'Jabatan organisasi tidak sesuai skema poin.',
        ], $this->atributValidasi());
    }

    /** Aturan data profil mahasiswa (dipakai form dan impor CSV). */
    private function aturanData(?Mahasiswa $mahasiswa = null): array
    {
        return [
            'nim' => [
                'required', 'string', 'max:30', 'regex:/^[0-9A-Za-z]+$/',
                Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa?->nim, 'nim'),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'angkatan' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'kelas' => ['nullable', 'string', 'max:30'],
            // REVISI 24-09-2026: satu field email saja (email login = email profil)
            // dan wajib memakai domain institusi mahasiswa.
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($mahasiswa?->user_id, 'id_user'),
                User::aturanDomainEmail('mahasiswa'),
            ],
            // Validasi input (revisi 26-09-2026): nomor telepon hanya angka.
            'no_hp' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'ipk' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'status_mahasiswa' => ['required', Rule::in(Mahasiswa::STATUS)],
        ];
    }

    private function pesanValidasi(): array
    {
        return [
            'nim.unique' => 'NIM tersebut sudah terdaftar.',
            'nim.regex' => 'NIM hanya boleh berisi huruf dan angka tanpa spasi.',
            'no_hp.regex' => 'Nomor telepon hanya boleh berisi angka (10–15 digit).',
            'email.unique' => 'Email tersebut sudah digunakan akun lain.',
            'status_mahasiswa.in' => 'Status mahasiswa tidak dikenali (Aktif, Alumni, Cuti, Nonaktif, DO, DISPEN).',
        ];
    }

    private function atributValidasi(): array
    {
        return [
            'nim' => 'NIM',
            'nama' => 'Nama',
            'email' => 'Email',
            'password' => 'Password',
            'no_hp' => 'Nomor telepon',
            'ipk' => 'IPK',
            'angkatan' => 'Angkatan',
            'status_mahasiswa' => 'Status mahasiswa',
        ];
    }

    /**
     * Sinkronkan data organisasi mahasiswa (hapus lama, simpan yang diisi).
     * Baris kosong diabaikan.
     */
    private function simpanOrganisasi(Mahasiswa $mahasiswa, array $baris): void
    {
        $mahasiswa->organisasi()->delete();

        foreach ($baris as $o) {
            $nama = trim((string) ($o['nama_organisasi'] ?? ''));
            $jabatan = $o['jabatan'] ?? null;

            if ($nama === '' || blank($jabatan)) {
                continue;
            }

            Organisasi::create([
                'nim' => $mahasiswa->nim,
                'nama_organisasi' => $nama,
                'jabatan' => $jabatan,
            ]);
        }
    }

    private function simpanFoto(Request $request): ?string
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        return $request->file('foto')->store('mahasiswa', 'public');
    }
}
