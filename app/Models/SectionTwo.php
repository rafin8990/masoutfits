<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionTwo extends Model
{
    protected $table = "section_two";
    protected $fillable = [
        'title',
        'description',
        'sections',
    ];
    protected $casts = [
        'sections' => 'array', 
    ];
}
