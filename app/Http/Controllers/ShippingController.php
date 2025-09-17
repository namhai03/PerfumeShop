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
        // Bộ lọc thời gian: hôm nay, 3/7/14/30/90 ngày qua
        $range = $request->get('range', '7d');
        $province = $request->get('province');

        $from = match($range){
            'today' => now()->startOfDay(),
            '3d' => now()->subDays(3),
            '7d' => now()->subDays(7),
            '14d' => now()->subDays(14),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(7),
        };

        $base = Shipment::query()->where('created_at', '>=', $from);
        if ($province) { $base->where('province', $province); }

        // Danh sách tỉnh hiện có trong vận đơn
        $provinces = Shipment::query()
            ->whereNotNull('province')
            ->select('province')
            ->distinct()->orderBy('province')
            ->pluck('province');

        $summary = [
            'pending_pickup' => [
                'count' => (clone $base)->where('status','pending_pickup')->count(),
                'cod' => (clone $base)->where('status','pending_pickup')->sum('cod_amount'),
            ],
            'picked_up' => [
                'count' => (clone $base)->where('status','picked_up')->count(),
                'cod' => (clone $base)->where('status','picked_up')->sum('cod_amount'),
            ],
            'in_transit' => [
                'count' => (clone $base)->where('status','in_transit')->count(),
                'cod' => (clone $base)->where('status','in_transit')->sum('cod_amount'),
            ],
            'retry' => [
                'count' => (clone $base)->where('status','retry')->count(),
                'cod' => (clone $base)->where('status','retry')->sum('cod_amount'),
            ],
            'returning' => [
                'count' => (clone $base)->where('status','returning')->count(),
                'cod' => (clone $base)->where('status','returning')->sum('cod_amount'),
            ],
            'returned' => [
                'count' => (clone $base)->where('status','returned')->count(),
                'cod' => (clone $base)->where('status','returned')->sum('cod_amount'),
            ],
            'delivered' => [
                'count' => (clone $base)->where('status','delivered')->count(),
                'cod' => (clone $base)->where('status','delivered')->sum('cod_amount'),
            ],
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

        return view('shipping.overview', compact('summary', 'charts', 'range', 'province', 'provinces', 'recentShipments'));
    }

    public function overviewData(Request $request)
    {
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

        // Success rate
        $delivered = (clone $base)->where('status', 'delivered')->count();
        $failed = (clone $base)->whereIn('status', ['failed','returned'])->count();
        $attempted = $delivered + $failed;
        $successRate = $attempted > 0 ? round($delivered / $attempted * 100, 2) : 0;

        // Average times (in hours)
        $pickupDurations = (clone $base)
            ->whereNotNull('picked_up_at')
            ->get(['created_at','picked_up_at'])
            ->map(function($s){ return $s->created_at->diffInMinutes($s->picked_up_at) / 60; });
        $avgPickupHours = $pickupDurations->count() ? round($pickupDurations->avg(), 2) : 0;

        $deliveryDurations = (clone $base)
            ->whereNotNull('delivered_at')
            ->get(['created_at','picked_up_at','delivered_at'])
            ->map(function($s){
                $start = $s->picked_up_at ?: $s->created_at;
                return $start->diffInMinutes($s->delivered_at) / 60;
            });
        $avgDeliveryHours = $deliveryDurations->count() ? round($deliveryDurations->avg(), 2) : 0;

        // Daily series by status
        $seriesStatuses = ['delivered','in_transit','retry','returned','failed'];
        $startDate = $from->copy()->startOfDay();
        $dates = [];
        for ($d = $startDate->copy(); $d <= now(); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }
        $daily = array_fill_keys($dates, array_fill_keys($seriesStatuses, 0));

        (clone $base)
            ->selectRaw('DATE(created_at) as d, status, COUNT(*) as c')
            ->whereIn('status', $seriesStatuses)
            ->groupBy('d','status')
            ->orderBy('d')
            ->get()
            ->each(function($row) use (&$daily){
                $date = $row->d;
                if (!isset($daily[$date])) { return; }
                $daily[$date][$row->status] = (int)$row->c;
            });

        // Weight distribution (grams buckets)
        $buckets = [
            ['label' => '0-500g', 'min' => 0, 'max' => 500],
            ['label' => '500g-1kg', 'min' => 500, 'max' => 1000],
            ['label' => '1-2kg', 'min' => 1000, 'max' => 2000],
            ['label' => '2-5kg', 'min' => 2000, 'max' => 5000],
            ['label' => '>5kg', 'min' => 5000, 'max' => null],
        ];
        $weightCounts = [];
        foreach ($buckets as $b) {
            $q = (clone $base);
            if (is_null($b['max'])) {
                $q->where('weight_grams', '>', $b['min']);
            } else {
                $q->whereBetween('weight_grams', [$b['min'], $b['max']]);
            }
            $weightCounts[] = $q->count();
        }

        return response()->json([
            'success_rate' => $successRate,
            'avg_pickup_hours' => $avgPickupHours,
            'avg_delivery_hours' => $avgDeliveryHours,
            'daily_series' => [
                'labels' => array_keys($daily),
                'datasets' => $seriesStatuses,
                'data' => array_values($daily),
            ],
            'weight_distribution' => [
                'labels' => array_column($buckets, 'label'),
                'data' => $weightCounts,
            ],
        ]);
    }
}


