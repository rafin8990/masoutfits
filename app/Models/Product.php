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
        'discount_price',
        'discount_amount',
        'price',
        'isNew',
        'size_column_name_one',
        'size_column_name_two'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class, 'product_sub_category');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

   public function productImages()
{
    return $this->hasMany(ProductImage::class)->with('color');
}

    public function availability()
    {
        return $this->hasMany(Availability::class)->with(['color', 'size']);
    }
    public function sizeGuide()
    {
        return $this->hasMany(SizeGuide::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
   

    // protected $with = [
    //     'category',
    //     'subCategory',
    //     'tags',
    //     'productImages.color',
    //     'availability.color',
    //     'availability.size',
    //     'sizeGuides'
    // ];
}
