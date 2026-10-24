<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstruksiTersembunyi extends Model
{
    protected $table = 'instruksi_tersembunyi';

    protected $fillable = [
        'user_id',
        'instruksi',
        'kunci',
    ];

    // Rapikan spasi berlebih
    public static function rapikan(?string $teks): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $teks));
    }

    // Kunci pembanding: tidak peduli huruf besar/kecil & spasi berlebih
    public static function kunci(?string $teks): string
    {
        return sha1(mb_strtolower(self::rapikan($teks)));
    }

    // Sembunyikan instruksi dari daftar saran milik user
    public static function sembunyikan(int $userId, string $teks): void
    {
        $teks = self::rapikan($teks);

        static::updateOrCreate(
            ['user_id' => $userId, 'kunci' => self::kunci($teks)],
            ['instruksi' => $teks]
        );
    }

    // Tampilkan lagi (dipakai saat instruksi yang sama digunakan kembali)
    public static function tampilkanLagi(int $userId, string $teks): void
    {
        static::where('user_id', $userId)
            ->where('kunci', self::kunci($teks))
            ->delete();
    }
}
