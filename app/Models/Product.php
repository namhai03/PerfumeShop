<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'import_price', 'selling_price', 'category', 'brand', 'sku',
        'barcode', 'stock', 'low_stock_threshold', 'image', 'volume', 'concentration', 'origin', 'import_date', 
        'sales_channel', 'tags', 'ingredients', 'is_active', 'product_type', 'product_form', 'expiry_date',
        'branch_price', 'customer_group_price', 'created_date',
        // Thuộc tính mùi hương
        'fragrance_family', 'top_notes', 'heart_notes', 'base_notes', 'gender', 'style', 'season'
    ];

    protected $casts = [
        'import_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
