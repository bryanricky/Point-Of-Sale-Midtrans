<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogKasir extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'level',
        'kas_awal',
        'login_at',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
