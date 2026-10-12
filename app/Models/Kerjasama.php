<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kerjasama extends Model
{
    protected $table = 'kerjasama';

    public $timestamps = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'judul_kerjasama',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_kerjasama',
        'status',
        'industri_id',
        'humas_id',
        'kepala_sekolah_id',
        'file_mou_path',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'industri_id');
    }

    public function pkl()
    {
        return $this->hasMany(Pkl::class, 'kerjasama_id');
    }
}
