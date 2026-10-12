<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluarField extends Model
{
    protected $table = 'surat_keluar_fields';

    protected $fillable = ['key', 'label', 'type', 'is_default', 'is_active', 'urutan'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];
}
