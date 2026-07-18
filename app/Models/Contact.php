<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
        'ip_address',
        'user_agent',
        'ai_analysis',
        'sentiment',
        'meta'
    ];

    protected $casts = [
        'ai_analysis' => 'array',
        'meta' => 'array',
    ];
}
