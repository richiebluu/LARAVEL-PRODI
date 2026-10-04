<?php

namespace App\Http\Controllers;

use App\Models\LowonganPekerjaan;
use Illuminate\Http\Request;

class LowonganPekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('q'));
        $tipe = $request->query('tipe');

        if (! in_array($tipe, LowonganPekerjaan::TIPE, true)) {
            $tipe = null;
        }

        $lowongan = LowonganPekerjaan::tampil()
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->when($cari !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('posisi', 'like', '%'.$cari.'%')
                ->orWhere('perusahaan', 'like', '%'.$cari.'%')
                ->orWhere('lokasi', 'like', '%'.$cari.'%')))
            ->latest('created_at')
            ->latest('id_lowongan_pekerjaan')
            ->paginate(9)
            ->withQueryString();

        return view('lowongan-pekerjaan', [
            'daftarLowongan' => $lowongan,
            'cari' => $cari,
            'tipe' => $tipe,
        ]);
    }
}
