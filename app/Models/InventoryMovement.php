<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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

    protected $casts = [
        'transaction_date' => 'datetime',
        'unit_cost' => 'decimal:2',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Accessors & Mutators
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'import' => 'Nhập kho',
            'export' => 'Xuất kho',
            'adjust' => 'Điều chỉnh',
            'stocktake' => 'Kiểm kê',
            'return' => 'Trả hàng',
            'damage' => 'Hàng hỏng',
            default => ucfirst($this->type)
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'import' => 'fas fa-arrow-down text-success',
            'export' => 'fas fa-arrow-up text-danger',
            'adjust' => 'fas fa-edit text-warning',
            'stocktake' => 'fas fa-clipboard-check text-info',
            'return' => 'fas fa-undo text-primary',
            'damage' => 'fas fa-exclamation-triangle text-danger',
            default => 'fas fa-circle'
        };
    }

    public function getQuantityChangeFormattedAttribute(): string
    {
        $prefix = $this->quantity_change >= 0 ? '+' : '';
        return $prefix . number_format($this->quantity_change);
    }

    public function getTransactionDateFormattedAttribute(): string
    {
        return $this->transaction_date->format('d/m/Y H:i');
    }

    public function getIsIncreaseAttribute(): bool
    {
        return $this->quantity_change > 0;
    }

    public function getIsDecreaseAttribute(): bool
    {
        return $this->quantity_change < 0;
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeIncreases($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeDecreases($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('transaction_date', '>=', now()->subDays($days));
    }

    // Business Logic Methods
    public static function getMovementTypes(): array
    {
        return [
            'import' => 'Nhập kho',
            'export' => 'Xuất kho',
            'adjust' => 'Điều chỉnh',
            'stocktake' => 'Kiểm kê',
            'return' => 'Trả hàng',
            'damage' => 'Hàng hỏng',
        ];
    }

    public static function getMovementStats($productId = null, $dateRange = null): array
    {
        $query = static::query();
        
        if ($productId) {
            $query->where('product_id', $productId);
        }
        
        if ($dateRange) {
            $query->byDateRange($dateRange['start'], $dateRange['end']);
        }

        $movements = $query->get();

        return [
            'total_movements' => $movements->count(),
            'total_increases' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
            'total_decreases' => abs($movements->where('quantity_change', '<', 0)->sum('quantity_change')),
            'net_change' => $movements->sum('quantity_change'),
            'by_type' => $movements->groupBy('type')->map->count(),
            'by_date' => $movements->groupBy(function($item) {
                return $item->transaction_date->format('Y-m-d');
            })->map->count(),
        ];
    }

    public function getTotalValueAttribute(): float
    {
        if ($this->unit_cost && $this->quantity_change > 0) {
            return $this->unit_cost * abs($this->quantity_change);
        }
        return 0;
    }
}


