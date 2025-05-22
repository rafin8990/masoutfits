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
        'sub_category_id',
        'price'
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
        return $this->hasMany(Tag::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function availability()
    {
        return $this->hasMany(Availability::class);
    }
    public function sizeGuides()
    {
        return $this->hasMany(SizeGuide::class,);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
   

    protected $with = [
        'category',
        'subCategory',
        'tags',
        'productImages.color',
        'availability.color',
        'availability.size',
        'sizeGuides'
    ];
}
