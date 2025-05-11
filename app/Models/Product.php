<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'fit',
        'care',
        'category_id',
        'sub_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function availability()
    {
        return $this->hasMany(Availability::class);
    }
    public function sizeGuide()
    {
        return $this->belongsToMany(SizeGuide::class, 'product_size_guide');
    }

    protected $with = [
        'category',
        'subCategory',
        'tags',
        'productImages.color',
        'availability.color',  
        'availability.size',   
        'sizeGuide'
    ];
}
