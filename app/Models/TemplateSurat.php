<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    protected $table = 'template_surat';

    protected $fillable = [
        'nama_template',
        'jenis_surat',
        'isi_template',
        'variabel',
        'is_active',
        'user_id_pembuat',
    ];

    protected $casts = [
        'variabel' => 'array',
        'is_active' => 'boolean',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id_pembuat');
    }

    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class, 'template_surat_id');
    }

    public function fields()
    {
        return $this->hasMany(
            TemplateSuratField::class,
            'template_surat_id'
        )->orderBy('urutan')->orderBy('id');
    }
}
