<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    protected $table = 'surat_keluar';

    protected $fillable = [
        'nomor_surat',
        'jenis_surat',
        'template_surat_id',
        'perihal',
        'tujuan',
        'alamat_tujuan',
        'keterangan',
        'tanggal_surat',
        'file_path',
        'isi_surat',
        'user_id_pembuat',
        'kepala_sekolah_id',
        'status',
        'data_tambahan',
    ];

    protected $casts = [
        'data_tambahan' => 'array',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id_pembuat');
    }

    public function kepalaSekolah()
    {
        return $this->belongsTo(User::class, 'kepala_sekolah_id');
    }

    public function templateSurat()
    {
        return $this->belongsTo(TemplateSurat::class, 'template_surat_id');
    }
}
