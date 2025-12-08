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
        return $this->belongsToMany(SubCategory::class, 'category_sub_category');
    }
    public function sizeGuide()
    {
        return $this->hasMany(SizeGuide::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
