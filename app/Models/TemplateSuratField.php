<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSuratField extends Model
{
    protected $table = 'template_surat_fields';

    protected $fillable = [
        'template_surat_id',
        'key',
        'label',
        'type',
        'is_required',
        'default_value',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function templateSurat()
    {
        return $this->belongsTo(
            TemplateSurat::class,
            'template_surat_id'
        );
    }
}
