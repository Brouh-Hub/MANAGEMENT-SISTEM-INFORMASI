<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';   // <-- WAJIB ditambah ini

    protected $fillable = [
        'judul', 'slug', 'konten', 'thumbnail', 'kategori_berita_id', 'user_id', 'status',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_berita_id');
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
