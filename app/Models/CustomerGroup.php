<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'discount_rate',
        'description',
        'is_active',
        'is_default',
        'min_order_amount',
        'max_discount_amount',
    ];

    protected $casts = [
        'discount_rate' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}


