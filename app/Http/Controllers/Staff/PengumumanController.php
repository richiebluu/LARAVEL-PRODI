<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\PengumumanMahasiswaBerprestasi;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Pengumuman Mahasiswa Berprestasi.
 *
 * REVISI DOSEN 01-10-2026: satu pengumuman dapat ditujukan ke BANYAK mahasiswa.
 *   PENGUMUMAN (1) --< PENGUMUMAN_PENERIMA >-- (1) MAHASISWA
 * Penerima dipilih seperti "bagikan" Google Drive: Staff mengetik email
 * @mhs.politala.ac.id, sistem mencari mahasiswa di database (cariMahasiswa),
 * lalu mahasiswa terpilih tampil sebagai chip yang dapat dihapus (×).
 */
class PengumumanController extends Controller
{
    /** Batas jumlah saran pada pencarian penerima. */
    private const MAKS_SARAN = 8;

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');
        if (! in_array($kategori, Pengumuman::daftarKategori(), true)) {
            $kategori = null;
        }

        $pengumuman = Pengumuman::query()
            ->pengumuman() // pesan notifikasi sistem tidak ikut ditampilkan
            ->with(['prestasi', 'penerima.user', 'staffProdi'])
            ->withCount('penerima')
            ->when($cari !== '', fn ($q) => $q->where('judul', 'like', '%'.$cari.'%'))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('staff-pengumuman', [
            'daftarPengumuman' => $pengumuman,
            'daftarPrestasi' => Prestasi::disetujui()->with('mahasiswa')->latest('id_prestasi')->get(),
            // Penerima yang sudah dipilih sebelum validasi gagal (agar chip tidak hilang).
            'penerimaLama' => $this->mahasiswaDariNim((array) old('penerima', [])),
            'domainEmail' => User::domainEmail('mahasiswa'),
            'cari' => $cari,
            'kategori' => $kategori,
            'jumlah' => $pengumuman->total(),
        ]);
    }

    /**
     * Pencarian penerima (JSON) untuk input chip — data langsung dari tabel mahasiswa.
     * GET /staff-pengumuman/cari-mahasiswa?q=rizqi@mhs&kecuali[]=NIM
     *
     * Hanya mahasiswa dengan email @mhs.politala.ac.id yang dapat muncul.
     * Mahasiswa yang belum memiliki prestasi disetujui tetap ditampilkan tetapi
     * tidak dapat dipilih (bisa_dipilih = false) beserta alasannya.
     */
    public function cariMahasiswa(Request $request)
    {
        $kata = strtolower(trim((string) $request->query('q')));
        $kecuali = array_filter((array) $request->query('kecuali', []), 'is_string');
        $domain = strtolower((string) User::domainEmail('mahasiswa'));

        if (mb_strlen($kata) < 2) {
            return response()->json(['data' => [], 'pesan' => 'Ketik minimal 2 karakter email, nama, atau NIM mahasiswa.']);
        }

        // Bila Staff mengetik email lengkap dengan domain lain -> tolak dengan pesan jelas.
        if (str_contains($kata, '@')) {
            $domainKetik = substr($kata, strpos($kata, '@') + 1);
            if ($domainKetik !== '' && ! str_starts_with($domain, $domainKetik)) {
                return response()->json(['data' => [], 'pesan' => 'Penerima hanya boleh email mahasiswa @'.$domain.'.']);
            }
        }

        $like = '%'.$kata.'%';

        $hasil = Mahasiswa::query()
            ->with('user')
            ->withCount('prestasiDisetujui')
            ->emailInstitusi()
            ->whereNotIn('nim', $kecuali) // yang sudah dipilih tidak ditampilkan lagi
            ->where(fn ($q) => $q
                ->whereRaw('LOWER(mahasiswa.email) LIKE ?', [$like])
                ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', [$like]))
                ->orWhereRaw('LOWER(mahasiswa.nama) LIKE ?', [$like])
                ->orWhere('mahasiswa.nim', 'like', $like))
            ->orderByDesc('prestasi_disetujui_count')
            ->orderBy('nama')
            ->limit(self::MAKS_SARAN)
            ->get()
            ->map(fn (Mahasiswa $m) => [
                'nim' => $m->nim,
                'nama' => $m->nama,
                'email' => $m->email_kontak,
                'kelas' => $m->kelas,
                'bisa_dipilih' => $m->prestasi_disetujui_count > 0,
                'alasan' => $m->prestasi_disetujui_count > 0 ? null : 'Belum memiliki prestasi yang disetujui',
            ])
            ->values();

        return response()->json([
            'data' => $hasil,
            'pesan' => $hasil->isEmpty() ? 'Mahasiswa dengan email "'.$kata.'" tidak ditemukan.' : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $staffProdiId = Auth::user()?->staffProdi?->id_staff_prodi;

        if (! $staffProdiId) {
            return back()->withErrors([
                'judul' => 'Akun Anda belum terhubung ke data Staff Prodi.',
            ])->withInput();
        }

        $terkirim = $data['status'] === Pengumuman::STATUS_TERKIRIM;

        $pengumuman = DB::transaction(function () use ($data, $staffProdiId, $terkirim) {
            $pengumuman = Pengumuman::create([
                'prestasi_id' => $data['prestasi_id'] ?? null,
                'kategori' => $data['kategori'],
                'staff_prodi_id' => $staffProdiId,
                'nim' => null, // penerima disimpan di tabel pengumuman_penerima
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status'],
                'tanggal_dikirim' => $terkirim ? now() : null,
                // Teks notifikasi yang muncul di dashboard SETIAP mahasiswa penerima.
                'notifikasi' => $terkirim ? $data['judul'] : null,
            ]);

            // Simpan semua penerima (satu baris per mahasiswa, dibaca_pada = null).
            $pengumuman->penerima()->sync($data['penerima']);

            return $pengumuman;
        });

        if ($terkirim) {
            $this->kirimEmail($pengumuman, $data['penerima']);
        }

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman berhasil dibuat untuk '.count($data['penerima']).' mahasiswa.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        // Pesan sistem (status notifikasi) bukan pengumuman Staff -> tidak boleh disunting di sini.
        abort_if($pengumuman->status === Pengumuman::STATUS_NOTIFIKASI, 404);

        $data = $this->validasi($request);
        $sebelumnya = $pengumuman->status;
        $terkirim = $data['status'] === Pengumuman::STATUS_TERKIRIM;
        $baruTerkirim = $sebelumnya !== Pengumuman::STATUS_TERKIRIM && $terkirim;

        $penerimaBaru = DB::transaction(function () use ($data, $pengumuman, $terkirim, $baruTerkirim) {
            $pengumuman->update([
                'prestasi_id' => $data['prestasi_id'] ?? null,
                'kategori' => $data['kategori'],
                'nim' => null,
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status'],
                'tanggal_dikirim' => $terkirim ? ($pengumuman->tanggal_dikirim ?? now()) : null,
                'notifikasi' => $terkirim ? $data['judul'] : null,
                'dibaca_pada' => null,
            ]);

            // sync(): penerima yang dihapus (×) dilepas, penerima baru ditambahkan,
            // penerima lama tetap (status bacanya tidak berubah).
            $hasil = $pengumuman->penerima()->sync($data['penerima']);

            // Baru berubah dari draft -> terkirim: semua penerima belum membaca.
            if ($baruTerkirim) {
                DB::table('pengumuman_penerima')
                    ->where('pengumuman_id', $pengumuman->id_pengumuman)
                    ->update(['dibaca_pada' => null]);
            }

            return $hasil['attached'];
        });

        // Email: semua penerima bila baru dikirim, atau hanya penerima tambahan bila sudah terkirim.
        if ($baruTerkirim) {
            $this->kirimEmail($pengumuman, $data['penerima']);
        } elseif ($terkirim && $penerimaBaru) {
            $this->kirimEmail($pengumuman, $penerimaBaru);
        }

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman berhasil diperbarui ('.count($data['penerima']).' penerima).');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        abort_if($pengumuman->status === Pengumuman::STATUS_NOTIFIKASI, 404);

        $judul = $pengumuman->judul;
        $pengumuman->delete(); // baris pengumuman_penerima ikut terhapus (cascade)

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman "'.$judul.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $domain = User::domainEmail('mahasiswa');

        $data = $request->validate([
            // Banyak penerima: array NIM dari chip; tidak boleh ada yang dobel.
            'penerima' => ['required', 'array', 'min:1', 'max:500'],
            'penerima.*' => ['required', 'string', 'distinct', 'exists:mahasiswa,nim'],
            'kategori' => ['required', Rule::in(Pengumuman::daftarKategori())],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'status' => ['required', Rule::in([Pengumuman::STATUS_DRAFT, Pengumuman::STATUS_TERKIRIM])],
            'prestasi_id' => ['nullable', 'integer'],
        ], [
            'penerima.required' => 'Pilih minimal satu mahasiswa penerima.',
            'penerima.min' => 'Pilih minimal satu mahasiswa penerima.',
            'penerima.*.distinct' => 'Mahasiswa yang sama tidak boleh dipilih dua kali.',
            'penerima.*.exists' => 'Mahasiswa penerima tidak ditemukan di database.',
            'kategori.required' => 'Silakan pilih kategori prestasi pengumuman.',
            'kategori.in' => 'Kategori pengumuman harus Prestasi Akademik atau Prestasi Non-Akademik.',
        ], [
            'judul' => 'Judul pengumuman',
            'isi' => 'Isi pengumuman',
        ]);

        $nim = array_values(array_unique($data['penerima']));

        // Setiap penerima wajib: email @mhs.politala.ac.id DAN mahasiswa berprestasi.
        $valid = Mahasiswa::query()->whereIn('nim', $nim)->emailInstitusi()->berprestasi()->pluck('nim')->all();
        $tidakValid = array_values(array_diff($nim, $valid));

        if ($tidakValid) {
            $nama = Mahasiswa::whereIn('nim', $tidakValid)->pluck('nama')->implode(', ');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'penerima' => 'Penerima harus mahasiswa berprestasi (memiliki prestasi yang sudah disetujui) dengan email @'.$domain.'. Tidak valid: '.$nama.'.',
            ]);
        }

        // Prestasi terkait (opsional) harus prestasi disetujui milik SALAH SATU penerima.
        if (! empty($data['prestasi_id'])) {
            $prestasi = Prestasi::disetujui()->whereKey($data['prestasi_id'])->whereIn('nim', $nim)->first();

            if (! $prestasi) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'prestasi_id' => 'Prestasi terkait harus prestasi yang sudah disetujui milik salah satu mahasiswa penerima.',
                ]);
            }

            // Kategori mengikuti kategori prestasi yang dipilih.
            $data['kategori'] = $prestasi->kategori;
        }

        $data['penerima'] = $nim;

        return $data;
    }

    /**
     * Pengumuman terkirim -> EMAIL (Gmail) ke setiap mahasiswa penerima (REVISI 26-09-2026).
     * Notifikasi dashboard sudah tersimpan per mahasiswa di tabel pengumuman_penerima.
     * Kegagalan email satu mahasiswa tidak membatalkan pengumuman / email lainnya.
     */
    private function kirimEmail(Pengumuman $pengumuman, array $nim): void
    {
        $pengumuman->loadMissing('prestasi');
        $gagal = [];

        foreach ($this->mahasiswaDariNim($nim) as $mahasiswa) {
            $email = $mahasiswa->email_kontak;

            if (blank($email)) {
                continue;
            }

            try {
                Mail::to($email, $mahasiswa->nama)->send(new PengumumanMahasiswaBerprestasi($pengumuman, $mahasiswa));
            } catch (\Throwable $e) {
                report($e);
                $gagal[] = $email;
            }
        }

        if ($gagal) {
            session()->flash('email_gagal', 'Pengumuman tersimpan, tetapi email ke '.implode(', ', $gagal).' gagal dikirim. Periksa konfigurasi MAIL_* (Gmail) pada .env.');
        }
    }

    /** Ambil data mahasiswa (dengan akun) dari daftar NIM, urut sesuai daftar. */
    private function mahasiswaDariNim(array $nim): Collection
    {
        $nim = array_values(array_filter($nim, 'is_string'));

        if (! $nim) {
            return collect();
        }

        $data = Mahasiswa::with('user')->whereIn('nim', $nim)->get()->keyBy('nim');

        return collect($nim)->map(fn ($n) => $data->get($n))->filter()->values();
    }
}
