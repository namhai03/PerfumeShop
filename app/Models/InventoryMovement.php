<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity_change',
        'before_stock',
        'after_stock',
        'performed_by',
        'note',
        'transaction_date',
        'unit_cost',
        'supplier',
        'reference_id',
        'order_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


