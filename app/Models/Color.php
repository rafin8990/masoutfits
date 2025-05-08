<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = [
        'name',
        'code',
        'image',
    ];
    protected $table = 'colors';

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
}
