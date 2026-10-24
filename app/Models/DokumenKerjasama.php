<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/*
| Dokumen MoU / PKS dengan mitra industri.
| Status dihitung otomatis dari tanggal:
|   belum_berlaku  : tanggal mulai masih di depan
|   aktif          : sedang berlaku
|   akan_berakhir  : berakhir dalam X hari (config('mou.hari_peringatan'), bawaan 60)
|   berakhir       : sudah lewat tanggal berakhir
*/
class DokumenKerjasama extends Model
{
    protected $table = 'dokumen_kerjasama';

    protected $fillable = [
        'industri_id',
        'jenis',
        'nomor_dokumen',
        'nomor_dokumen_mitra',
        'judul',
        'ruang_lingkup',
        'tanggal_mulai',
        'tanggal_berakhir',
        'penandatangan_sekolah',
        'penandatangan_mitra',
        'file_path',
        'keterangan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    public const LABEL_STATUS = [
        'belum_berlaku' => ['Belum Berlaku', 'secondary'],
        'aktif'         => ['Aktif', 'success'],
        'akan_berakhir' => ['Akan Berakhir', 'warning'],
        'berakhir'      => ['Berakhir', 'danger'],
    ];

    public static function hariPeringatan(): int
    {
        return (int) config('mou.hari_peringatan', 60);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'industri_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function getStatusAttribute(): string
    {
        $hariIni = Carbon::today();

        if ($this->tanggal_mulai && $this->tanggal_mulai->gt($hariIni)) {
            return 'belum_berlaku';
        }
        if ($this->tanggal_berakhir && $this->tanggal_berakhir->lt($hariIni)) {
            return 'berakhir';
        }
        if ($this->tanggal_berakhir && $this->tanggal_berakhir->lte($hariIni->copy()->addDays(self::hariPeringatan()))) {
            return 'akan_berakhir';
        }

        return 'aktif';
    }

    // Sisa hari sampai berakhir (null = tanpa batas, negatif = sudah lewat)
    public function getSisaHariAttribute(): ?int
    {
        return $this->tanggal_berakhir
            ? (int) Carbon::today()->diffInDays($this->tanggal_berakhir, false)
            : null;
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /* ---------- Filter status di query (dipakai tabel & dashboard) ---------- */
    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        $hariIni = Carbon::today()->toDateString();
        $batas = Carbon::today()->addDays(self::hariPeringatan())->toDateString();

        return match ($status) {
            'belum_berlaku' => $q->whereDate('tanggal_mulai', '>', $hariIni),
            'berakhir'      => $q->whereNotNull('tanggal_berakhir')->whereDate('tanggal_berakhir', '<', $hariIni),
            'akan_berakhir' => $q->whereDate('tanggal_mulai', '<=', $hariIni)
                ->whereNotNull('tanggal_berakhir')
                ->whereDate('tanggal_berakhir', '>=', $hariIni)
                ->whereDate('tanggal_berakhir', '<=', $batas),
            'aktif'         => $q->whereDate('tanggal_mulai', '<=', $hariIni)
                ->where(fn ($w) => $w->whereNull('tanggal_berakhir')->orWhereDate('tanggal_berakhir', '>', $batas)),
            default         => $q,
        };
    }
}
