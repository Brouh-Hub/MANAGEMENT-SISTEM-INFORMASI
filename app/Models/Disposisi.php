<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    protected $table = 'disposisi';

    protected $fillable = [
        'surat_masuk_id',
        'instruksi',
        'catatan',
        'kepala_sekolah_id',
        'tanggal_disposisi',
        'status',
        'sifat',
        'tujuan_user_id',
        'diteruskan_dari_id',
        'dibaca_at',
        'laporan_hasil',
        'file_hasil',
        'selesai_at',
        'alasan',
        'diputuskan_at',
    ];

    protected $casts = [
        'dibaca_at'  => 'datetime',
        'selesai_at' => 'datetime',
        'diputuskan_at' => 'datetime',
    ];

    // Label status untuk ditampilkan ke pengguna
    public const LABEL_STATUS = [
        'pending'    => 'Belum Diterima',
        'diterima'   => 'Diterima',
        'diproses'   => 'Diproses',
        'selesai'    => 'Selesai',
        'diteruskan' => 'Diteruskan',
        'diarsipkan' => 'Diarsipkan',
        'ditolak'    => 'Ditolak',
    ];


    public function getStatusLabelAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getFileHasilUrlAttribute(): ?string
    {
        return $this->file_hasil ? asset('storage/' . $this->file_hasil) : null;
    }

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function diteruskanDari()
    {
        return $this->belongsTo(self::class, 'diteruskan_dari_id');
    }

    public function tujuanUser()
    {
        return $this->belongsTo(User::class, 'tujuan_user_id');
    }

    public function tujuan()
    {
        return $this->belongsTo(User::class, 'tujuan_user_id');
    }
}