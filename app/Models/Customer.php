<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'gender', 'birthday', 'address', 'city', 'district', 'ward',
        'customer_type', 'customer_group_id', 'source', 'tax_number', 'company', 'note',
        'total_spent', 'total_orders', 'is_active'
    ];

    protected $casts = [
        'birthday' => 'date',
        'is_active' => 'boolean',
        'total_spent' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}


