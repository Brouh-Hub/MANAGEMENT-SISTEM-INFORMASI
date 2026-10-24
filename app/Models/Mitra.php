<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $table = 'industri';

    public $timestamps = false;

    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'contact_person',
        'email',
        'website',
        'bidang',
        'humas_id',
        'foto',
        'sumber',
    ];

    protected $appends = [
        'foto_url',
    ];

    /**
     * URL foto mitra
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : null;
    }

    /**
     * Satu mitra dapat memiliki banyak kerja sama.
     *
     * industri.id
     *      ↓
     * kerjasama.industri_id
     */
    /**
     * Dokumen MoU / PKS dengan mitra ini.
     */
    public function dokumenKerjasama()
    {
        return $this->hasMany(DokumenKerjasama::class, 'industri_id');
    }

    public function kerjasama()
    {
        return $this->hasMany(
            Kerjasama::class,
            'industri_id'
        );
    }
}
