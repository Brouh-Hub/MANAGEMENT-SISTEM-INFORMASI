<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sertifikat extends Model
{
    protected $table = 'sertifikat';

    protected $fillable = [
        'nomor_sertifikat',
        'judul',
        'deskripsi',
        'tanggal_terbit',
        'file_path',
        'kerjasama_id',
        'user_id_penerima',
        'user_id_penerbit',
        'template_id',
        'status',
        'nama_penerima',
        'template_key',

        'nis',
        'tempat_lahir',
        'tanggal_lahir',
        'konsentrasi',
        'mitra',
        'predikat',

        'pimpinan_jabatan',
        'pimpinan_nama',    
        'pimpinan_nip',

        'kepala_sekolah_nama',
        'kepala_sekolah_nip',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date:Y-m-d',
        'tanggal_lahir'  => 'date:Y-m-d',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function kerjasama()
    {
        return $this->belongsTo(Kerjasama::class, 'kerjasama_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'user_id_penerima');
    }

    public function penerbit()
    {
        return $this->belongsTo(User::class, 'user_id_penerbit');
    }

    public function template()
    {
        return $this->belongsTo(TemplateSertifikat::class, 'template_id');
    }
}
