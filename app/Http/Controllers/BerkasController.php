<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class BerkasController extends Controller
{
    // Hanya folder surat yang boleh dilayani lewat route ini
    private const FOLDER = ['surat-masuk', 'surat-keluar', 'disposisi-hasil', 'mou-pks', 'sertifikat'];

    public function show(string $path)
    {
        $folder = explode('/', $path)[0];

        abort_unless(
            in_array($folder, self::FOLDER, true) && ! str_contains($path, '..'),
            404
        );
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
