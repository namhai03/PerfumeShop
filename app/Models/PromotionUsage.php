<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id', 'customer_id', 'order_code', 'discount_amount', 'context'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'context' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}


