<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderUserInfo extends Model
{
    protected $table = 'order_user_info';
    protected $fillable = [
        'order_id',
        'user_id',
        'guest_id',
        'full_name',
        'email',
        'phone_number'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
