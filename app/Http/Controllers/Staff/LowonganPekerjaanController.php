<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LowonganPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LowonganPekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $lowongan = LowonganPekerjaan::query()
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('posisi', 'like', '%'.$cari.'%')
                ->orWhere('perusahaan', 'like', '%'.$cari.'%')))
            ->latest('created_at')
            ->latest('id_lowongan_pekerjaan')
            ->paginate(10)
            ->withQueryString();

        return view('staff-lowongan', [
            'daftarLowongan' => $lowongan,
            'cari' => $cari,
            'jumlah' => $lowongan->total(),
            'daftarTipe' => LowonganPekerjaan::TIPE,
        ]);
    }

    public function store(Request $request)
    {
        LowonganPekerjaan::create($this->validasi($request) + [
            'staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi,
        ]);

        return redirect()->route('staff-lowongan')->with('success', 'Lowongan pekerjaan berhasil ditambahkan.');
    }

    public function update(Request $request, LowonganPekerjaan $lowongan)
    {
        $lowongan->update($this->validasi($request));

        return redirect()->route('staff-lowongan')->with('success', 'Lowongan pekerjaan berhasil diperbarui.');
    }

    public function destroy(LowonganPekerjaan $lowongan)
    {
        $posisi = $lowongan->posisi;
        $lowongan->delete();

        return redirect()->route('staff-lowongan')->with('success', 'Lowongan "'.$posisi.'" berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'posisi' => ['required', 'string', 'max:150'],
            'perusahaan' => ['required', 'string', 'max:150'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tipe' => ['nullable', Rule::in(LowonganPekerjaan::TIPE)],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'link' => ['required', 'url', 'max:255'],
            'batas_lamaran' => ['nullable', 'date'],
        ], [
            'link.url' => 'Link lowongan harus berupa URL lengkap (https://...).',
        ], [
            'posisi' => 'Posisi',
            'perusahaan' => 'Perusahaan',
            'link' => 'Link lowongan',
            'batas_lamaran' => 'Batas lamaran',
        ]);
    }
}
