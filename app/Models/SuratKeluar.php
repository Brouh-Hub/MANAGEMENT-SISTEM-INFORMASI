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
        'alasan_konfirmasi',
        'dikonfirmasi_oleh',
        'dikonfirmasi_at',
    ];

    protected $casts = [
        'data_tambahan'   => 'array',
        'dikonfirmasi_at' => 'datetime',
    ];

    // Label status untuk tampilan
    public const STATUS = [
        'draft'              => 'Draft',
        'menunggu_kepala_tu' => 'Menunggu Kepala TU',
        'dikembalikan'       => 'Dikembalikan',
        'menunggu_approve'   => 'Menunggu Kepala Sekolah',
        'terbit'             => 'Disetujui',
    ];

    // Kepala Tata Usaha yang menyetujui / mengembalikan surat
    public function pengonfirmasi()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }

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
