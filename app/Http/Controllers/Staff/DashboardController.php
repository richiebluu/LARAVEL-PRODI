<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Prestasi;
use App\Services\StatistikService;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(private readonly StatistikService $statistik) {}

    public function index()
    {
        return view('staff-dashboard', [
            'ringkasan' => $this->statistik->ringkasanStaff(),
            'aktivitas' => $this->aktivitasTerbaru(),
        ]);
    }

    private function aktivitasTerbaru(int $limit = 6): Collection
    {
        $item = collect();

        Prestasi::with('mahasiswa')->latest('created_at')->take($limit)->get()
            ->each(function (Prestasi $p) use ($item) {
                $item->push([
                    'icon' => 'fa-star',
                    'text' => ($p->mahasiswa->nama ?? 'Mahasiswa').' mengajukan prestasi "'.$p->judul.'".',
                    'waktu' => $p->created_at,
                ]);
            });

        Organisasi::with('mahasiswa')->latest('updated_at')->take($limit * 2)->get()->unique('nim')
            ->each(function (Organisasi $o) use ($item) {
                $item->push([
                    'icon' => 'fa-user-pen',
                    'text' => ($o->mahasiswa->nama ?? 'Mahasiswa').' memperbarui data Keaktifan Organisasi.',
                    'waktu' => $o->updated_at,
                ]);
            });

        Pengumuman::pengumuman()->latest('created_at')->take($limit)->get()
            ->each(function (Pengumuman $g) use ($item) {
                $item->push([
                    'icon' => 'fa-bullhorn',
                    'text' => 'Pengumuman "'.$g->judul.'" dibuat.',
                    'waktu' => $g->created_at,
                ]);
            });

        Berita::latest('created_at')->take($limit)->get()
            ->each(function (Berita $b) use ($item) {
                $item->push([
                    'icon' => 'fa-newspaper',
                    'text' => 'Berita "'.$b->judul.'" '.($b->status === Berita::STATUS_TERBIT ? 'diterbitkan' : 'disimpan sebagai draft').'.',
                    'waktu' => $b->created_at,
                ]);
            });

        return $item
            ->filter(fn ($a) => $a['waktu'] !== null)
            ->sortByDesc('waktu')
            ->take($limit)
            ->values();
    }
}
