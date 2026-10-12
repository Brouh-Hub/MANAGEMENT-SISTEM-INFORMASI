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
    ];

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