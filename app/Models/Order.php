<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_name',
        'status',
        'type',
        'total_amount',
        'discount_amount',
        'final_amount',
        'notes',
        'order_date',
        'delivery_date',
        'payment_method',
        'delivery_address',
        'phone',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    // Constants for status
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';

    // Constants for type
    const TYPE_SALE = 'sale';
    const TYPE_RETURN = 'return';
    const TYPE_DRAFT = 'draft';

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UNPAID => 'Chưa thanh toán',
            self::STATUS_PAID => 'Đã thanh toán',
            default => 'Không xác định'
        };
    }

    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SALE => 'Bán hàng',
            self::TYPE_RETURN => 'Trả hàng',
            self::TYPE_DRAFT => 'Đơn nháp',
            default => 'Không xác định'
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_UNPAID => 'badge-unpaid',
            self::STATUS_PAID => 'badge-paid',
            default => 'badge-default'
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SALE => 'badge-sale',
            self::TYPE_RETURN => 'badge-return',
            self::TYPE_DRAFT => 'badge-draft',
            default => 'badge-default'
        };
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSales($query)
    {
        return $query->where('type', self::TYPE_SALE);
    }

    public function scopeReturns($query)
    {
        return $query->where('type', self::TYPE_RETURN);
    }

    public function scopeDrafts($query)
    {
        return $query->where('type', self::TYPE_DRAFT);
    }
}
