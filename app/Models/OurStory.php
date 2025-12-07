<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurStory extends Model
{
    protected $table = 'ourstory';
    protected $fillable = [
        'title',
        'description',
        'image',
    ];
}
