<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
| Catatan setiap nomor surat keluar yang pernah di-generate.
| - surat_keluar_id NULL  = nomor sudah dibuat TU tapi suratnya belum disimpan
|                           (dipakai lagi oleh TU yang sama di generate berikutnya,
|                            jadi tidak ada nomor yang terbuang)
| - surat_keluar_id terisi = nomor sudah dipakai surat
*/
class NomorSuratReservasi extends Model
{
    protected $table = 'nomor_surat_reservasi';

    protected $fillable = [
        'nomor_surat',
        'nomor_urut',
        'penomoran_surat_id',
        'template_surat_id',
        'user_id',
        'surat_keluar_id',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function suratKeluar()
    {
        return $this->belongsTo(SuratKeluar::class, 'surat_keluar_id');
    }
}
    