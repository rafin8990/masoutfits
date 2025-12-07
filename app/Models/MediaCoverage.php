<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaCoverage extends Model
{
    protected $table = 'media_coverage';

    protected $fillable = [
        'title',
        'description',
    ];
}

