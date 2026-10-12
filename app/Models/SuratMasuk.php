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
    ];


    public function disposisi() { return $this->hasMany(Disposisi::class, 'surat_masuk_id'); }
}
