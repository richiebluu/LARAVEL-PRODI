<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $semua = MataKuliah::query()->urut()->get();
        $daftarSemester = $semua->pluck('semester')->unique()->sort()->values();

        $semester = (int) $request->query('semester');
        if (! $daftarSemester->contains($semester)) {
            $semester = null;
        }

        $tampil = $semester ? $semua->where('semester', $semester) : $semua;

        return view('mata-kuliah', [
            'perSemester' => $tampil->groupBy('semester'),
            'daftarSemester' => $daftarSemester,
            'semester' => $semester,
            'jumlahMataKuliah' => $semua->count(),
            'totalSks' => $semua->sum('sks'),
        ]);
    }
}
