<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_group_id',
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
        'ward',
        'city',
        'phone',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    // Constants for status (7-state workflow)
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';

    // Constants for type
    const TYPE_SALE = 'sale';
    const TYPE_RETURN = 'return';
    const TYPE_DRAFT = 'draft';

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Shipments linked by order_number -> shipments.order_code
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'order_code', 'order_number');
    }

    /**
     * Latest shipment by created_at
     */
    public function latestShipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'order_code', 'order_number')->latestOfMany('created_at');
    }

    // Accessors
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Đơn nháp',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_SHIPPING => 'Đang giao',
            self::STATUS_DELIVERED => 'Đã nhận',
            self::STATUS_FAILED => 'Thất bại',
            self::STATUS_RETURNED => 'Trả hàng',
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
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_CONFIRMED => 'badge-primary',
            self::STATUS_PROCESSING => 'badge-warning',
            self::STATUS_SHIPPING => 'badge-info',
            self::STATUS_DELIVERED => 'badge-success',
            self::STATUS_FAILED => 'badge-dark',
            self::STATUS_RETURNED => 'badge-danger',
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

    /**
     * Compute normalized shipping phase based on latest shipment status
     * Phases: preparing, shipping, delivered, returned
     */
    public function getShippingPhaseAttribute(): ?string
    {
        $shipment = $this->relationLoaded('latestShipment') ? $this->latestShipment : $this->latestShipment()->first();
        if (!$shipment) {
            return null;
        }

        return match($shipment->status) {
            'pending_pickup' => 'preparing',
            'picked_up', 'in_transit', 'retry' => 'shipping',
            'returning', 'returned', 'failed' => 'returned',
            'delivered' => 'delivered',
            default => null,
        };
    }

    public function getShippingPhaseTextAttribute(): ?string
    {
        return match($this->shipping_phase) {
            'preparing' => 'Đang chuẩn bị',
            'shipping' => 'Đã vận chuyển',
            'delivered' => 'Đã nhận',
            'returned' => 'Trả hàng',
            default => null,
        };
    }

    public function getShippingPhaseBadgeClassAttribute(): ?string
    {
        return match($this->shipping_phase) {
            'preparing' => 'badge-warning',
            'shipping' => 'badge-info',
            'delivered' => 'badge-success',
            'returned' => 'badge-danger',
            default => null,
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
