<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_code', 'type', 'amount', 'description', 'reason', 'payer_group',
        'payer_name', 'payer_id', 'payer_type', 'from_account_id', 'to_account_id',
        'branch_id', 'transaction_date', 'reference', 'note', 'status',
        'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(CashAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(CashAccount::class, 'to_account_id');
    }

    public function attachments()
    {
        return $this->hasMany(VoucherAttachment::class, 'voucher_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function getTypeTextAttribute()
    {
        return match($this->type) {
            'receipt' => 'Phiếu thu',
            'payment' => 'Phiếu chi',
            'transfer' => 'Chuyển quỹ nội bộ',
            default => $this->type
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'cancelled' => 'Đã hủy',
            default => $this->status
        };
    }
}
