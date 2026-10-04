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

class PengumumanController extends Controller
{
    private const MAKS_SARAN = 8;

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');
        if (! in_array($kategori, Pengumuman::daftarKategori(), true)) {
            $kategori = null;
        }

        $pengumuman = Pengumuman::query()
            ->pengumuman()
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
            'penerimaLama' => $this->mahasiswaDariNim((array) old('penerima', [])),
            'domainEmail' => User::domainEmail('mahasiswa'),
            'cari' => $cari,
            'kategori' => $kategori,
            'jumlah' => $pengumuman->total(),
        ]);
    }

    public function cariMahasiswa(Request $request)
    {
        $kata = strtolower(trim((string) $request->query('q')));
        $kecuali = array_filter((array) $request->query('kecuali', []), 'is_string');
        $domain = strtolower((string) User::domainEmail('mahasiswa'));

        if (mb_strlen($kata) < 2) {
            return response()->json(['data' => [], 'pesan' => 'Ketik minimal 2 karakter email, nama, atau NIM mahasiswa.']);
        }

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
            ->whereNotIn('nim', $kecuali)
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
                'nim' => null,
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status'],
                'tanggal_dikirim' => $terkirim ? now() : null,
                'notifikasi' => $terkirim ? $data['judul'] : null,
            ]);

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

            $hasil = $pengumuman->penerima()->sync($data['penerima']);

            if ($baruTerkirim) {
                DB::table('pengumuman_penerima')
                    ->where('pengumuman_id', $pengumuman->id_pengumuman)
                    ->update(['dibaca_pada' => null]);
            }

            return $hasil['attached'];
        });

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
        $pengumuman->delete();

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman "'.$judul.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $domain = User::domainEmail('mahasiswa');

        $data = $request->validate([
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

        $valid = Mahasiswa::query()->whereIn('nim', $nim)->emailInstitusi()->berprestasi()->pluck('nim')->all();
        $tidakValid = array_values(array_diff($nim, $valid));

        if ($tidakValid) {
            $nama = Mahasiswa::whereIn('nim', $tidakValid)->pluck('nama')->implode(', ');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'penerima' => 'Penerima harus mahasiswa berprestasi (memiliki prestasi yang sudah disetujui) dengan email @'.$domain.'. Tidak valid: '.$nama.'.',
            ]);
        }

        if (! empty($data['prestasi_id'])) {
            $prestasi = Prestasi::disetujui()->whereKey($data['prestasi_id'])->whereIn('nim', $nim)->first();

            if (! $prestasi) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'prestasi_id' => 'Prestasi terkait harus prestasi yang sudah disetujui milik salah satu mahasiswa penerima.',
                ]);
            }

            $data['kategori'] = $prestasi->kategori;
        }

        $data['penerima'] = $nim;

        return $data;
    }

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
