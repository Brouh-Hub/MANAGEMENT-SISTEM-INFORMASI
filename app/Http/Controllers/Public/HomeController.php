<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Berita;
use App\Models\Mitra;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman landing page
     */
    public function index()
    {
        $sekolah = Sekolah::first();

        // Jika data sekolah belum tersedia, gunakan object kosong
        // agar halaman public tetap dapat ditampilkan tanpa error.
        if (!$sekolah) {
            $sekolah = new Sekolah();
        }

        $beritaTerbaru = Berita::latest()->take(3)->get();
        $mitraUnggulan = Mitra::latest()->take(6)->get();
        $jurusan = collect();

        return view('public.home.index', [
            'title'         => 'Beranda',
            'sekolah'       => $sekolah,
            'beritaTerbaru' => $beritaTerbaru,
            'mitraUnggulan' => $mitraUnggulan,
            'jurusan'      => $jurusan,
        ]);
    }
}
