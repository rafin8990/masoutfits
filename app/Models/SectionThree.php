<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionThree extends Model
{
    protected $table = "section_three";
    protected $fillable = [
        'title',
        'description',
        'image',
    ];

}
