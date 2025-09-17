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

        $base = Shipment::query();
        if ($province) { $base->where('province', $province); }

        // Danh sách tỉnh hiện có trong vận đơn
        $provinces = Shipment::query()
            ->whereNotNull('province')
            ->select('province')
            ->distinct()->orderBy('province')
            ->pluck('province');

        $summary = [];
        foreach (['pending_pickup','picked_up','in_transit','returning','delivered','failed','returned','cancelled'] as $st) {
            [$col] = $this->statusDateColumn($st);
            $q = (clone $base)->where('status', $st);
            if ($col) { $q->where($col, '>=', $from); }
            else { $q->where('created_at', '>=', $from); }
            $summary[$st] = [
                'count' => $q->count(),
                'cod' => $q->sum('cod_amount'),
            ];
        }

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

        $base = Shipment::query();
        if ($province) { $base->where('province', $province); }

        // Success rate
        $delivered = (clone $base)->where('status', 'delivered')->where('delivered_at','>=',$from)->count();
        $failed = (clone $base)->where('status', 'failed')->where('failed_at','>=',$from)->count();
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
        $seriesStatuses = ['pending_pickup','picked_up','in_transit','returning','delivered','failed','returned','cancelled'];
        $startDate = $from->copy()->startOfDay();
        $dates = [];
        for ($d = $startDate->copy(); $d <= now(); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }
        $daily = array_fill_keys($dates, array_fill_keys($seriesStatuses, 0));
        // Tính theo cột thời gian phù hợp từng trạng thái
        foreach ($dates as $dStr) {
            foreach ($seriesStatuses as $st) {
                [$col] = $this->statusDateColumn($st);
                $col = $col ?: 'created_at';
                $count = (clone $base)
                    ->where('status', $st)
                    ->whereDate($col, $dStr)
                    ->count();
                $daily[$dStr][$st] = $count;
            }
        }

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

        // Status distribution (for pie chart)
        $statusDistribution = [];
        foreach (['pending_pickup', 'picked_up', 'in_transit', 'returning', 'delivered', 'failed', 'returned', 'cancelled'] as $status) {
            $count = (clone $base)->where('status', $status)->count();
            if ($count > 0) {
                $statusDistribution[] = [
                    'label' => $this->getStatusLabel($status),
                    'value' => $count
                ];
            }
        }

        // Top provinces (for horizontal bar chart)
        $topProvinces = (clone $base)
            ->whereNotNull('province')
            ->select('province', DB::raw('count(*) as count'))
            ->groupBy('province')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'province' => $item->province,
                    'count' => $item->count
                ];
            });

        // Success rate trend (daily success rate for line chart)
        $successRateTrend = [];
        foreach ($dates as $date) {
            $delivered = (clone $base)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $date)
                ->count();
            $failed = (clone $base)
                ->where('status', 'failed')
                ->whereDate('failed_at', $date)
                ->count();
            $total = $delivered + $failed;
            $rate = $total > 0 ? round($delivered / $total * 100, 1) : 0;
            $successRateTrend[] = [
                'date' => $date,
                'rate' => $rate
            ];
        }

        // Delivered revenue per day (sum COD for delivered)
        $deliveredRevenue = [];
        foreach ($dates as $date) {
            $sum = (clone $base)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $date)
                ->sum('cod_amount');
            $deliveredRevenue[] = [
                'date' => $date,
                'amount' => (float)$sum,
            ];
        }

        // Shipments by hour of day (0-23)
        $hourlyCounts = array_fill(0, 24, 0);
        $hourlyRows = (clone $base)
            ->where('created_at', '>=', $from)
            ->get(['created_at'])
            ->map(function($s){ return (int)$s->created_at->format('G'); });
        foreach ($hourlyRows as $h) { $hourlyCounts[$h]++; }

        // Average delivery hours by carrier (delivered only) top 5 slowest
        $byCarrier = (clone $base)
            ->whereNotNull('delivered_at')
            ->get(['carrier','created_at','picked_up_at','delivered_at'])
            ->groupBy('carrier')
            ->map(function($items, $carrier){
                $hours = $items->map(function($s){
                    $start = $s->picked_up_at ?: $s->created_at;
                    return $start->diffInMinutes($s->delivered_at) / 60;
                });
                return [
                    'carrier' => $carrier ?: 'Không xác định',
                    'avg_hours' => $hours->count() ? round($hours->avg(), 2) : 0,
                ];
            })->sortByDesc('avg_hours')->values()->take(5)->all();

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
            'status_distribution' => $statusDistribution,
            'top_provinces' => $topProvinces,
            'success_rate_trend' => $successRateTrend,
            'delivered_revenue' => $deliveredRevenue,
            'shipments_by_hour' => $hourlyCounts,
            'delivery_time_by_carrier' => $byCarrier,
        ]);
    }

    /**
     * Xác định cột thời gian phù hợp cho từng trạng thái
     * trả về [columnName]
     */
    private function statusDateColumn(string $status): array
    {
        return match($status) {
            'picked_up' => ['picked_up_at'],
            'in_transit' => ['picked_up_at'],
            'returning' => ['returning_at'],
            'delivered' => ['delivered_at'],
            'failed' => ['failed_at'],
            'returned' => ['returned_at'],
            default => ['created_at'],
        };
    }

    /**
     * Chuyển đổi status thành label tiếng Việt
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending_pickup' => 'Chờ lấy hàng',
            'picked_up' => 'Đã lấy hàng',
            'in_transit' => 'Đang giao hàng',
            'returning' => 'Đang hoàn hàng',
            'delivered' => 'Đã giao',
            'failed' => 'Thất bại',
            'returned' => 'Đã hoàn',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }
}


