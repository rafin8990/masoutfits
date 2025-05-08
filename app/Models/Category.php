<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'image',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function sizeGuide()
    {
        return $this->hasMany(SizeGuide::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
