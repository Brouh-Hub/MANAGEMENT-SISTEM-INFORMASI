<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSertifikat extends Model
{
    protected $table = 'template_sertifikat';

    protected $fillable = [
        'nama_template', 'file_path', 'kategori', 'struktur_template', 'created_by',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sertifikat()
    {
        return $this->hasMany(Sertifikat::class, 'template_id');
    }
}
