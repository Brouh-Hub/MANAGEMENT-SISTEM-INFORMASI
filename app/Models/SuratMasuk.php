<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    public $timestamps = false;   // <-- tambahkan ini, karena tabel cuma punya created_at

    protected $fillable = [
        'nomor_surat', 'perihal', 'pengirim', 'alamat_asal_pengirim',
        'tanggal_surat', 'tanggal_terima', 'no_agenda', 'file_path',
        'status_konfirmasi', 'alasan_konfirmasi', 'dikonfirmasi_oleh', 'dikonfirmasi_at',
    ];

    protected $casts = [
        'dikonfirmasi_at' => 'datetime',
    ];

    /*
     * Format No. Agenda otomatis: 001/SM/X/2026
     * (nomor urut / SM / bulan romawi terima / tahun terima), urut ulang tiap tahun.
     */
    public const FORMAT_AGENDA = '{nomor}/SM/{romawi}/{tahun}';
    public const PANJANG_AGENDA = 3;

    public static function nomorAgendaBerikutnya($tanggalTerima = null): string
    {
        $tgl = \Carbon\Carbon::parse($tanggalTerima ?: now());
        $tahun = $tgl->year;

        // Ambil nomor urut terbesar tahun itu (No. Agenda lama yang formatnya beda diabaikan)
        $terakhir = static::where('no_agenda', 'like', '%/SM/%/' . $tahun)
            ->pluck('no_agenda')
            ->map(fn ($n) => preg_match('#^(\d+)/SM/[IVXLC]+/' . $tahun . '$#', $n, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$tgl->month];

        return strtr(self::FORMAT_AGENDA, [
            '{nomor}'  => str_pad((string) ($terakhir + 1), self::PANJANG_AGENDA, '0', STR_PAD_LEFT),
            '{romawi}' => $romawi,
            '{tahun}'  => $tahun,
        ]);
    }

    // Kepala Tata Usaha yang menyetujui / mengembalikan surat
    public function pengonfirmasi()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }


    public function disposisi() { return $this->hasMany(Disposisi::class, 'surat_masuk_id'); }
}
