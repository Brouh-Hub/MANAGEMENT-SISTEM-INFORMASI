<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'jabatan', 'sekolah_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
