<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'secret',
        'hash',
        'activated_at',
        'expired_at',
        'status',
    ];
    protected $casts = [
        'expired_at' => 'datetime:Y-m-d',
    ];
}
