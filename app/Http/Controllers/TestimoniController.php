<?php

namespace App\Http\Controllers;

use App\Models\Testimoni;

class TestimoniController extends Controller
{
    public function index()
    {
        return view('testimoni', [
            'daftarTestimoni' => Testimoni::query()
                ->latest('created_at')
                ->latest('id_testimoni')
                ->get(),
        ]);
    }
}
