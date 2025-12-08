<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $fillable = [
        'name',
        'image',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_sub_category');
    }

    public function sizeGuide()
    {
        return $this->hasMany(SizeGuide::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sub_category');
    }
}
