<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    protected $table = 'footer';

    protected $fillable = [
        'logo',
        'icons',
        'addresses',
    ];

    protected $casts = [
        'addresses' => 'array',
        'icons'=> 'array',
    ];
}
