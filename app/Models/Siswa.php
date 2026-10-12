<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'kelas',
        'jurusan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Deteksi jurusan:
     * cek kolom jurusan dulu, kalau gagal cek dari kelas.
     */
    public static function deteksiJurusan(
        ?string $jurusan,
        ?string $kelas
    ): ?string {
        foreach ([$jurusan, $kelas] as $teks) {
            $teks = strtolower(trim((string) $teks));

            if ($teks === '') {
                continue;
            }

            foreach (config('jurusan') as $baku => $alias) {
                foreach (
                    array_merge([strtolower($baku)], $alias)
                    as $a
                ) {
                    if (
                        preg_match(
                            '/(?<![a-z0-9])'
                            . preg_quote($a, '/')
                            . '(?![a-z0-9])/',
                            $teks
                        )
                    ) {
                        return $baku;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Satu siswa dapat memiliki banyak data PKL.
     */
public function pkl()
{
    return $this->hasMany(
        Pkl::class,
        'siswa_id'
    );
}
}
