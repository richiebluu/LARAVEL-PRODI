<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\PengumumanMahasiswaBerprestasi;
use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $kategori = $request->query('kategori');
        if (! in_array($kategori, Pengumuman::daftarKategori(), true)) {
            $kategori = null;
        }

        $pengumuman = Pengumuman::query()
            ->pengumuman() // pesan notifikasi sistem tidak ikut ditampilkan
            ->with(['prestasi', 'mahasiswa', 'staffProdi'])
            ->when($cari !== '', fn ($q) => $q->where('judul', 'like', '%'.$cari.'%'))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('staff-pengumuman', [
            'daftarPengumuman' => $pengumuman,
            // Penerima = mahasiswa berprestasi (punya prestasi disetujui), bukan hasil ranking.
            'daftarMahasiswa' => Mahasiswa::berprestasi()->orderBy('nama')->get(['nim', 'nama']),
            'daftarPrestasi' => Prestasi::disetujui()->with('mahasiswa')->latest('id_prestasi')->get(),
            'cari' => $cari,
            'kategori' => $kategori,
            'jumlah' => $pengumuman->total(),
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

        DB::transaction(function () use ($data, $staffProdiId) {
            // ERD: MAHASISWA (1) -- MENERIMA -- (N) PENGUMUMAN -> penerima disimpan di kolom nim.
            $pengumuman = Pengumuman::create([
                'prestasi_id' => $data['prestasi_id'] ?? null,
                'kategori' => $data['kategori'],
                'staff_prodi_id' => $staffProdiId,
                'nim' => $data['nim'],
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status'],
                'tanggal_dikirim' => $data['status'] === Pengumuman::STATUS_TERKIRIM ? now() : null,
                'notifikasi' => $data['status'] === Pengumuman::STATUS_TERKIRIM ? $data['judul'] : null,
            ]);

            if ($data['status'] === Pengumuman::STATUS_TERKIRIM) {
                $this->kirimEmail($pengumuman);
            }
        });

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $this->validasi($request);
        $sebelumnya = $pengumuman->status;

        DB::transaction(function () use ($data, $pengumuman, $sebelumnya) {
            $baruTerkirim = $sebelumnya !== Pengumuman::STATUS_TERKIRIM
                && $data['status'] === Pengumuman::STATUS_TERKIRIM;
            $gantiPenerima = $pengumuman->nim !== $data['nim'];

            $pengumuman->update([
                'prestasi_id' => $data['prestasi_id'] ?? null,
                'kategori' => $data['kategori'],
                'nim' => $data['nim'],
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status'],
                'tanggal_dikirim' => $data['status'] === Pengumuman::STATUS_TERKIRIM
                    ? ($pengumuman->tanggal_dikirim ?? now())
                    : null,
                'notifikasi' => $data['status'] === Pengumuman::STATUS_TERKIRIM ? $data['judul'] : null,
                // Penerima baru / baru dikirim -> belum dibaca.
                'dibaca_pada' => ($baruTerkirim || $gantiPenerima) ? null : $pengumuman->dibaca_pada,
            ]);

            if ($baruTerkirim) {
                $this->kirimEmail($pengumuman);
            }
        });

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $judul = $pengumuman->judul;
        $pengumuman->delete();

        return redirect()
            ->route('staff-pengumuman')
            ->with('success', 'Pengumuman "'.$judul.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            // Penerima wajib mahasiswa berprestasi (memiliki prestasi berstatus disetujui).
            'nim' => [
                'required',
                Rule::exists('mahasiswa', 'nim')->where(fn ($q) => $q->whereIn('nim',
                    Prestasi::disetujui()->select('nim'))),
            ],
            // Prestasi terkait harus prestasi disetujui milik mahasiswa penerima.
            'prestasi_id' => [
                'nullable',
                Rule::exists('prestasi', 'id_prestasi')->where(fn ($q) => $q
                    ->where('status', Prestasi::STATUS_DISETUJUI)
                    ->where('nim', $request->input('nim'))),
            ],
            'kategori' => ['required', Rule::in(Pengumuman::daftarKategori())],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'status' => ['required', Rule::in([Pengumuman::STATUS_DRAFT, Pengumuman::STATUS_TERKIRIM])],
        ], [
            'nim.required' => 'Silakan pilih mahasiswa berprestasi penerima.',
            'nim.exists' => 'Penerima pengumuman harus mahasiswa berprestasi (memiliki prestasi yang sudah disetujui).',
            'prestasi_id.exists' => 'Prestasi terkait harus prestasi yang sudah disetujui milik mahasiswa penerima.',
            'kategori.required' => 'Silakan pilih kategori prestasi pengumuman.',
            'kategori.in' => 'Kategori pengumuman harus Prestasi Akademik atau Prestasi Non-Akademik.',
        ], [
            'judul' => 'Judul pengumuman',
            'isi' => 'Isi pengumuman',
        ]);

        // Bila pengumuman terkait prestasi tertentu, kategori mengikuti kategori prestasi tsb.
        if (! empty($data['prestasi_id'])) {
            $data['kategori'] = Prestasi::whereKey($data['prestasi_id'])->value('kategori') ?? $data['kategori'];
        }

        return $data;
    }

    /**
     * Pengumuman terkirim -> notifikasi dashboard + EMAIL (Gmail) ke mahasiswa
     * berprestasi penerima (REVISI 26-09-2026). Penerima dipilih karena prestasinya,
     * bukan karena urutan ranking. Kegagalan email tidak membatalkan pengumuman.
     */
    private function kirimEmail(Pengumuman $pengumuman): void
    {
        // Notifikasi dashboard sudah tersimpan pada kolom pengumuman.notifikasi (ERD).
        $mahasiswa = Mahasiswa::with('user')->find($pengumuman->nim);

        $email = $mahasiswa?->user?->email ?? $mahasiswa?->email;

        if (! $mahasiswa || blank($email)) {
            return;
        }

        try {
            Mail::to($email, $mahasiswa->nama)->send(new PengumumanMahasiswaBerprestasi($pengumuman->loadMissing('prestasi'), $mahasiswa));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('email_gagal', 'Pengumuman tersimpan, tetapi email ke '.$email.' gagal dikirim. Periksa konfigurasi MAIL_* (Gmail) pada .env.');
        }
    }
}
