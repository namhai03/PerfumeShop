<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code','tracking_code','carrier','branch','region',
        'recipient_name','recipient_phone','address_line','province','district','ward',
        'status','cod_amount','shipping_fee',
        'picked_up_at','delivered_at','failed_at','returned_at'
    ];
}


