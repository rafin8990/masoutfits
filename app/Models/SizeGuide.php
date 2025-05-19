<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SizeGuide extends Model
{
    protected $table = "size_guide";

    protected $fillable = [
        'product_id',
        'name',
        'chest',
        'body',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
