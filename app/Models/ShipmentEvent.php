<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id', 'status', 'event_at', 'note', 'meta'
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'meta' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}


