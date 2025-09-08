<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'volume_ml', 'import_price', 'selling_price', 'stock', 'barcode', 'image', 'is_active'
    ];

    protected $casts = [
        'import_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}


