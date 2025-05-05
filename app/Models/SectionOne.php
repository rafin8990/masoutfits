<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionOne extends Model
{
    protected $table = 'section_one';

    protected $fillable = [
        'title',
        'description',
        'sections',
    ];

    protected $casts = [
        'sections' => 'array', 
    ];
}
