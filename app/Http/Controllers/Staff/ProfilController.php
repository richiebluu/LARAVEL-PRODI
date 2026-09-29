<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    public function index()
    {
        return view('staff-profil', [
            'prodi' => ProgramStudi::first(),
        ]);
    }

    /** Simpan profil Program Studi ke tabel program_studi. */
    public function simpan(Request $request)
    {
        $data = $request->validate([
            'nama_prodi' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
            'jumlah_alumni' => ['nullable', 'integer', 'min:0'],
            'jumlah_dosen' => ['nullable', 'integer', 'min:0'],
            // Menu Informasi: AKAMAWA + PDF Kode Etik Mahasiswa sebagai atribut Profil Prodi.
            'link_akamawa' => ['nullable', 'url', 'max:255'],
            'kode_etik' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'hapus_kode_etik' => ['nullable', 'boolean'],
        ], [
            'link_akamawa.url' => 'Link AKAMAWA harus berupa URL lengkap (https://...).',
            'kode_etik.mimes' => 'Berkas Kode Etik harus berformat PDF.',
        ], [
            'nama_prodi' => 'Nama Program Studi',
            'kode_etik' => 'PDF Kode Etik',
        ]);

        $prodi = ProgramStudi::first();

        $data['jumlah_alumni'] = $data['jumlah_alumni'] ?? 0;
        $data['jumlah_dosen'] = $data['jumlah_dosen'] ?? 0;
        $data['staff_prodi_id'] = Auth::user()?->staffProdi?->id_staff_prodi;

        if ($request->hasFile('kode_etik')) {
            \App\Support\Berkas::hapus($prodi?->kode_etik);
            $data['kode_etik'] = $request->file('kode_etik')->store('kode-etik', 'public');
        } elseif ($request->boolean('hapus_kode_etik')) {
            \App\Support\Berkas::hapus($prodi?->kode_etik);
            $data['kode_etik'] = null;
        } else {
            unset($data['kode_etik']);
        }
        unset($data['hapus_kode_etik']);


        if ($prodi) {
            $prodi->update($data);
        } else {
            ProgramStudi::create($data);
        }

        return redirect()
            ->route('staff-profil')
            ->with('success', 'Profil Program Studi berhasil disimpan.');
    }

    /* ================= PROFIL SAYA (Staff Prodi) — REVISI 24-09-2026 =================
     * Halaman profil untuk akun Staff Prodi yang sedang login, dengan tampilan yang
     * sama seperti Profil Saya Dosen & Mahasiswa. Data berasal dari tabel `staff_prodi`
     * (relasi User -> staffProdi). Staff Prodi adalah verifikator, sehingga
     * perubahan datanya sendiri langsung disimpan tanpa pengajuan.
     */

    public function profilSaya()
    {
        return view('staff-profile', [
            'staff' => Auth::user()?->staffProdi,
        ]);
    }

    public function simpanProfilSaya(Request $request)
    {
        $user = Auth::user();
        $staff = $user?->staffProdi;

        if (! $staff) {
            return back()->withErrors(['nama' => 'Akun Anda belum terhubung ke data Staff Prodi.']);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            // Satu email: email profil = email login.
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user->id_user, 'id_user'),
                User::aturanDomainEmail('staff'),
            ],
            'no_hp' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'email.unique' => 'Email tersebut sudah digunakan akun lain.',
            'no_hp.regex' => 'Nomor telepon hanya boleh berisi angka (10–15 digit).',
        ], [
            'nama' => 'Nama',
            'no_hp' => 'Nomor telepon',
        ]);

        DB::transaction(function () use ($data, $request, $user, $staff) {
            $staff->update([
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'] ?? null,
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'foto' => $request->hasFile('foto')
                    ? $request->file('foto')->store('staff', 'public')
                    : $staff->foto,
            ]);

            $user->update([
                'name' => $data['nama'],
                'email' => $data['email'],
            ]);
        });

        return redirect()
            ->route('staff-profile')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
