<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';

    protected $fillable = [
        'name',
        'image',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
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
