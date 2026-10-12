<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pkl extends Model
{
    protected $table = 'pkl';

    protected $fillable = [
        'kerjasama_id',
        'siswa_id',
        'industri_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun',
        'pembimbing_pkl',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * PKL belongs to Kerjasama
     */
    public function kerjasama()
    {
        return $this->belongsTo(
            Kerjasama::class,
            'kerjasama_id'
        );
    }

    /**
     * PKL belongs to Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(
            Siswa::class,
            'siswa_id'
        );
    }

    /**
     * PKL belongs to Mitra Industri
     */
    public function mitra()
    {
        return $this->belongsTo(
            Mitra::class,
            'industri_id'
        );
    }

    /**
     * Status PKL otomatis berdasarkan tanggal
     */
    public function getStatusAttribute()
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return 'Belum Dimulai';
        }

        $hariIni = Carbon::today();

        if ($hariIni->lt($this->tanggal_mulai)) {
            return 'Belum Dimulai';
        }

        if ($hariIni->gt($this->tanggal_selesai)) {
            return 'Selesai';
        }

        return 'Sedang Berlangsung';
    }

    /**
     * Warna badge status
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'Selesai' => 'success',
            'Sedang Berlangsung' => 'primary',
            'Belum Dimulai' => 'secondary',
            default => 'secondary',
        };
    }
}
