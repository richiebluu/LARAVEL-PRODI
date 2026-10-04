<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;

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
