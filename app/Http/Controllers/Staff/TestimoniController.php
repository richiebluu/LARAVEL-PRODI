<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Testimoni;
use App\Support\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * INFORMASI PUBLIK — Testimoni Alumni.
 * REVISI 27-09-2026: jenis "Testimoni Mahasiswa Berprestasi" dihapus, sehingga tidak
 * ada lagi pilihan jenis/mahasiswa. Kolom mengikuti ERD TESTIMONI_ALUMNI.
 */
class TestimoniController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $testimoni = Testimoni::query()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('nama', 'like', "%{$q}%")
                ->orWhere('nama_perusahaan', 'like', "%{$q}%")
                ->orWhere('jabatan', 'like', "%{$q}%")))
            ->latest('created_at')
            ->latest('id_testimoni')
            ->paginate(10)
            ->withQueryString();

        return view('staff-testimoni', [
            'daftarTestimoni' => $testimoni,
            'jumlah' => $testimoni->total(),
            'cari' => $q,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['foto'] = $this->simpanFoto($request);
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        Testimoni::create($data);

        return redirect()->route('staff-testimoni')->with('success', 'Testimoni alumni berhasil ditambahkan.');
    }

    public function update(Request $request, Testimoni $testimoni)
    {
        $data = $this->validasi($request);
        $fotoBaru = $this->simpanFoto($request);

        if ($fotoBaru) {
            Berkas::hapus($testimoni->foto);
        }
        $data['foto'] = $fotoBaru ?? $testimoni->foto;

        $testimoni->update($data);

        return redirect()->route('staff-testimoni')->with('success', 'Testimoni alumni berhasil diperbarui.');
    }

    public function destroy(Testimoni $testimoni)
    {
        Berkas::hapus($testimoni->foto);
        $testimoni->delete();

        return redirect()->route('staff-testimoni')->with('success', 'Testimoni '.$testimoni->nama.' berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'tahun_kelulusan' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'nama_perusahaan' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:1000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama.required' => 'Nama alumni wajib diisi.',
        ], [
            'nama' => 'Nama alumni',
            'tahun_kelulusan' => 'Tahun kelulusan',
            'nama_perusahaan' => 'Nama perusahaan',
            'jabatan' => 'Jabatan',
            'isi' => 'Isi testimoni',
        ]);

        unset($data['foto']);

        return $data;
    }

    private function simpanFoto(Request $request): ?string
    {
        return $request->hasFile('foto') ? $request->file('foto')->store('testimoni', 'public') : null;
    }
}
