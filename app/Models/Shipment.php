<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code','tracking_code','carrier','branch','region',
        'recipient_name','recipient_phone','address_line','province','ward',
        'status','cod_amount','shipping_fee','weight_grams',
        'picked_up_at','delivered_at','failed_at','returned_at'
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'returned_at' => 'datetime',
        'cod_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'weight_grams' => 'integer',
    ];

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_code', 'order_number');
    }

    // Quan hệ nhiều-nhiều đã loại bỏ theo yêu cầu
}


