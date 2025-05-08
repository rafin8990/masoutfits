<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = 'sizes';

    protected $fillable = [
        'name',
    ];

    public function availabilities()
{
    return $this->hasMany(Availability::class);
}
}
