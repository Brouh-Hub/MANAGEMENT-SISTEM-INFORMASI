<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
| Halaman admin tidak boleh disimpan browser (cache).
| Tujuannya: setelah logout, tombol "Back" tidak bisa menampilkan
| halaman lama — user harus login ulang.
*/
class TanpaCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // File unduhan (PDF, Excel, gambar) tidak perlu diubah
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
