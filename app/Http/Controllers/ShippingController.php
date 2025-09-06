<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Shipment;

class ShippingController extends Controller
{
    public function overview(Request $request)
    {
        // Bộ lọc thời gian đơn giản: 7 ngày qua mặc định
        $range = $request->get('range', '7d');
        $branch = $request->get('branch');
        $region = $request->get('region');

        $from = match($range){
            'today' => now()->startOfDay(),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        $base = Shipment::query()->where('created_at', '>=', $from);
        if ($branch) { $base->where('branch', $branch); }
        if ($region) { $base->where('region', $region); }

        $summary = [
            'pending_pickup' => (clone $base)->where('status','pending_pickup')->count(),
            'picked_up' => (clone $base)->where('status','picked_up')->count(),
            'in_transit' => (clone $base)->where('status','in_transit')->count(),
            'retry' => (clone $base)->where('status','retry')->count(),
            'returning' => (clone $base)->where('status','returning')->count(),
            'returned' => (clone $base)->where('status','returned')->count(),
            'delivered' => (clone $base)->where('status','delivered')->count(),
            'cod_amount' => (clone $base)->sum('cod_amount'),
        ];

        $charts = [
            'avg_pickup_time' => null,
            'avg_delivery_time' => null,
            'success_rate' => null,
            'weight_distribution' => [],
        ];

        $recentShipments = (clone $base)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','order_code','tracking_code','carrier','status','cod_amount','shipping_fee','created_at']);

        return view('shipping.overview', compact('summary', 'charts', 'range', 'branch', 'region', 'recentShipments'));
    }
}


