<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'account_number', 'bank_name', 'balance', 'description', 'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function fromVouchers()
    {
        return $this->hasMany(CashVoucher::class, 'from_account_id');
    }

    public function toVouchers()
    {
        return $this->hasMany(CashVoucher::class, 'to_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
