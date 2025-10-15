<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id', 'file_path', 'file_name', 'file_type', 'file_size'
    ];

    public function voucher()
    {
        return $this->belongsTo(CashVoucher::class, 'voucher_id');
    }
}
