<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;

/**
 * INFORMASI (REVISI 27-09-2026, sebelumnya "Layanan"):
 *  - Berita               -> BeritaController
 *  - AKAMAWA              -> akamawa()
 *  - Kode Etik Mahasiswa  -> kodeEtik() (PDF ditampilkan interaktif seperti buku)
 * Link & berkas disimpan sebagai atribut Profil Program Studi (tabel program_studi).
 */
class InformasiController extends Controller
{
    public function akamawa()
    {
        $prodi = ProgramStudi::first();

        return view('akamawa', [
            'prodi' => $prodi,
            'linkAkamawa' => $prodi?->url_akamawa ?? ProgramStudi::LINK_AKAMAWA_BAWAAN,
        ]);
    }

    public function kodeEtik()
    {
        return view('kode-etik', [
            'prodi' => ProgramStudi::first(),
        ]);
    }
}
