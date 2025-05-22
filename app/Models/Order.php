<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_status',
        'notes'
    ];
    protected $with = [
        'orderUserInfo',
        'itemsInfo.product',
        'itemsInfo.size',
        'itemsInfo.color',
        'address',
        'payment'
    ];
    protected $table = 'orders';
    public function orderUserInfo()
    {
        return $this->hasOne(OrderUserInfo::class);
    }
    public function itemsInfo()
    {
        return $this->hasMany(ItemsInfo::class);
    }
    public function address()
    {
        return $this->hasOne(Address::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }


}
