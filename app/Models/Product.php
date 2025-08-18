<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'import_price', 'selling_price', 'category', 'brand', 'sku',
        'barcode', 'stock', 'image', 'volume', 'concentration', 'origin', 'import_date', 
        'sales_channel', 'tags', 'is_active', 'product_type', 'product_form', 'expiry_date',
        'branch_price', 'customer_group_price', 'created_date'
    ];

    protected $casts = [
        'import_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'import_date' => 'date',
        'expiry_date' => 'date',
        'created_date' => 'date',
        'is_active' => 'boolean',
        'branch_price' => 'json',
        'customer_group_price' => 'json',
    ];

    // Accessor để lấy giá theo chi nhánh
    public function getBranchPriceAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    // Accessor để lấy giá theo nhóm khách hàng
    public function getCustomerGroupPriceAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}
