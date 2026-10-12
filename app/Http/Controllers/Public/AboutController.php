<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;

class AboutController extends Controller
{
    /**
     * Tampilkan halaman Tentang Sekolah (publik, read-only)
     */
    public function index()
    {
        $sekolah = Sekolah::first();

        return view('public.about.index', [
            'title'   => 'Tentang Sekolah',
            'sekolah' => $sekolah,
        ]);
    }
}
