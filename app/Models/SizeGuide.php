<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SizeGuide extends Model
{
    protected $table = "size_guide";

    protected $fillable = [
        'name',
        'category_id',
        'sub_category_id',
        'chest',
        'body',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_size_guide');
    }
}
