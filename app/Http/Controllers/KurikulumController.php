<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    /**
     * Halaman Kurikulum (Profil > Kurikulum) — REVISI 28-09-2026.
     * Daftar mata kuliah Program Studi sesuai SIPADU: kode, nama, semester, SKS, jenis.
     * Ditampilkan per semester lengkap dengan total SKS.
     */
    public function index(Request $request)
    {
        $semua = MataKuliah::query()->urut()->get();
        $daftarSemester = $semua->pluck('semester')->unique()->sort()->values();

        $semester = (int) $request->query('semester');
        if (! $daftarSemester->contains($semester)) {
            $semester = null;
        }

        $tampil = $semester ? $semua->where('semester', $semester) : $semua;

        return view('kurikulum', [
            'perSemester' => $tampil->groupBy('semester'),
            'daftarSemester' => $daftarSemester,
            'semester' => $semester,
            'jumlahMataKuliah' => $semua->count(),
            'totalSks' => $semua->sum('sks'),
        ]);
    }
}
