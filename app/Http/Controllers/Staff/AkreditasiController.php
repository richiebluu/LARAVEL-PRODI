<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Akreditasi;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AkreditasiController extends Controller
{
    public function index()
    {
        return view('staff-akreditasi', [
            'prodi' => ProgramStudi::first(),
            'daftarAkreditasi' => Akreditasi::orderByDesc('tanggal_mulai')->orderByDesc('id_akreditasi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $prodi = ProgramStudi::first();

        if (! $prodi) {
            $prodi = ProgramStudi::create([
                'nama_prodi' => 'Program Studi Teknologi Informasi',
                'staff_prodi_id' => Auth::user()?->staffProdi?->id_staff_prodi,
            ]);
        }

        $data['program_studi_id'] = $prodi->id_program_studi;
        $data['dokumen'] = $this->simpanDokumen($request);

        Akreditasi::create(array_filter($data, fn ($v) => $v !== null));

        return redirect()
            ->route('staff-akreditasi')
            ->with('success', 'Data akreditasi berhasil ditambahkan.');
    }

    public function update(Request $request, Akreditasi $akreditasi)
    {
        $data = $this->validasi($request);
        $dokumen = $this->simpanDokumen($request);

        $akreditasi->update([
            'peringkat' => $data['peringkat'],
            'lembaga' => $data['lembaga'] ?? null,
            'nomor_sk' => $data['nomor_sk'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
            'tanggal_berakhir' => $data['tanggal_berakhir'] ?? null,
            'dokumen' => $dokumen ?? $akreditasi->dokumen,
        ]);

        return redirect()
            ->route('staff-akreditasi')
            ->with('success', 'Data akreditasi berhasil diperbarui.');
    }

    public function destroy(Akreditasi $akreditasi)
    {
        $akreditasi->delete();

        return redirect()
            ->route('staff-akreditasi')
            ->with('success', 'Data akreditasi berhasil dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'peringkat' => ['required', 'string', 'max:100'],
            'lembaga' => ['nullable', 'string', 'max:150'],
            'nomor_sk' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_berakhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [], [
            'peringkat' => 'Peringkat akreditasi',
            'nomor_sk' => 'Nomor SK',
        ]);
    }

    private function simpanDokumen(Request $request): ?string
    {
        if (! $request->hasFile('dokumen')) {
            return null;
        }

        return $request->file('dokumen')->store('akreditasi', 'public');
    }
}
