<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'type', 'scope', 'discount_value',
        'max_discount_amount', 'min_order_amount', 'min_items',
        'applicable_product_ids', 'applicable_category_ids', 'applicable_customer_group_ids',
        'applicable_sales_channels', 'is_stackable', 'priority', 'start_at', 'end_at',
        'is_active', 'usage_limit', 'usage_limit_per_customer'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'applicable_product_ids' => 'array',
        'applicable_category_ids' => 'array',
        'applicable_customer_group_ids' => 'array',
        'applicable_sales_channels' => 'array',
        'is_stackable' => 'boolean',
        'is_active' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->start_at && $now->lt($this->start_at)) return false;
        if ($this->end_at && $now->gt($this->end_at)) return false;
        return true;
    }
}


