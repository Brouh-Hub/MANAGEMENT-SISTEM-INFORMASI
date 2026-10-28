<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    protected $fillable = ['user_id', 'email', 'ip', 'user_agent', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
