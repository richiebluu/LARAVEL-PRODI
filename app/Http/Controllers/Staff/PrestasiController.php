<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PrestasiController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $status = $request->query('status');
        $kategori = $request->query('kategori');

        if (! in_array($status, [Prestasi::STATUS_MENUNGGU, Prestasi::STATUS_DITOLAK], true)) {
            $status = null;
        }
        if (! in_array($kategori, Prestasi::KATEGORI, true)) {
            $kategori = null;
        }

        $prestasi = Prestasi::query()
            ->with('mahasiswa')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('judul', 'like', '%'.$cari.'%')
                        ->orWhereHas('mahasiswa', fn ($m) => $m
                            ->where('nama', 'like', '%'.$cari.'%')
                            ->orWhere('nim', 'like', '%'.$cari.'%'));
                });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('staff-prestasi', [
            'daftarPrestasi' => $prestasi,
            'cari' => $cari,
            'status' => $status,
            'kategori' => $kategori,
            'jumlah' => $prestasi->total(),
        ]);
    }

    public function verifikasi(Request $request, Prestasi $prestasi)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([Prestasi::STATUS_DISETUJUI, Prestasi::STATUS_DITOLAK])],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $disetujui = $data['status'] === Prestasi::STATUS_DISETUJUI;

        $prestasi->update([
            'staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi,
            'status' => $data['status'],
            'catatan' => $data['catatan'] ?? ($disetujui ? null : 'Tidak memenuhi kriteria prestasi.'),
        ]);

        $this->notifikasi->kirim(
            $prestasi->nim,
            $disetujui ? 'Prestasi disetujui' : 'Prestasi ditolak',
            $disetujui
                ? 'Pengajuan "'.$prestasi->judul.'" telah disetujui ('.$prestasi->poin.' poin '.$prestasi->kategori.') dan tampil di halaman Mahasiswa Berprestasi.'
                : 'Pengajuan "'.$prestasi->judul.'" ditolak. Alasan: '.($prestasi->catatan ?: '-'),
            $prestasi->kategori,
            $prestasi->staff_prodi_id
        );

        return redirect()
            ->back()
            ->with('success', $disetujui
                ? 'Prestasi disetujui ('.$prestasi->poin.' poin) dan dihitung pada ranking.'
                : 'Pengajuan prestasi ditolak.');
    }

    public function destroy(Prestasi $prestasi)
    {
        $judul = $prestasi->judul;
        $prestasi->delete();

        return redirect()
            ->back()
            ->with('success', 'Prestasi "'.$judul.'" berhasil dihapus.');
    }
}
