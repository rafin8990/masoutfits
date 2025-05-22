<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemsInfo extends Model
{
    protected $table = 'items_info';
    protected $fillable = [
        'order_id',
        'product_id',
        'size_id',
        'color_id',
        'quantity'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}
