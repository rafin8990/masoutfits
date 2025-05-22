<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'order_id',
        'country',
        'city',
        'town',
        'police_station',
        'post_code',
        'area_details',
        'address_type',
        'apartment_address'
    ];
    protected $table = 'addresses';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
