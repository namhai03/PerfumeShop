<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Hiển thị trang tổng quan báo cáo
     */
    public function overview()
    {
        return view('reports.overview');
    }

    /**
     * Hiển thị trang danh sách báo cáo
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * API: Lấy dữ liệu phân tích doanh thu
     */
    public function revenueAnalysis(Request $request)
    {
        $period = $request->get('period', '30d'); // 7d, 30d, 90d, 1y
        $from = $this->getDateFromPeriod($period);

        // Doanh thu theo ngày (tích lũy)
        $dailyRevenue = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%Y-%m-%d", created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Tính doanh thu tích lũy
        $cumulativeRevenue = 0;
        $dailyRevenue = $dailyRevenue->map(function($item) use (&$cumulativeRevenue) {
            $cumulativeRevenue += $item->revenue;
            return [
                'date' => $item->date,
                'revenue' => $cumulativeRevenue,
                'orders_count' => $item->orders_count
            ];
        });

        // Doanh thu theo tháng (cho period dài)
        $monthlyRevenue = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%Y", created_at) as year'),
                DB::raw('strftime("%m", created_at) as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Top sản phẩm bán chạy
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', $from)
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Doanh thu theo kênh bán hàng
        $revenueByChannel = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                'sales_channel',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('sales_channel')
            ->get();

        // Tính toán các chỉ số kinh tế quan trọng
        $completedOrders = Order::whereIn('status', ['delivered'])->where('created_at', '>=', $from);
        $totalRevenue = $completedOrders->sum('total_amount');
        $totalOrders = $completedOrders->count();
        $avgOrderValue = $completedOrders->avg('total_amount');
        
        // Doanh thu theo tuần (để tính tốc độ tăng trưởng)
        $weeklyRevenue = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%Y-%W", created_at) as week'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        
        // Tính tốc độ tăng trưởng doanh thu tuần (so sánh với giai đoạn trước)
        $revenueGrowthRate = $this->calculateGrowthRate('revenue', $period);
        
        // Doanh thu theo tháng (cho period dài)
        $monthlyRevenue = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%Y-%m", created_at) as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Tính tốc độ tăng trưởng doanh thu tháng
        $monthlyGrowthRate = $this->calculateMonthlyGrowthRate($monthlyRevenue);
        
        // Phân tích theo kênh bán hàng chi tiết
        $revenueByChannel = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                'sales_channel',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('sales_channel')
            ->orderByDesc('revenue')
            ->get();
        
        // Top sản phẩm bán chạy với doanh thu
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', $from)
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('AVG(order_items.unit_price) as avg_price'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
        
        // Phân tích theo ngày trong tuần
        $revenueByDayOfWeek = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%w", created_at) as day_of_week'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('day_of_week')
            ->get()
            ->map(function($item) {
                $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                return [
                    'day' => $days[$item->day_of_week],
                    'revenue' => $item->revenue,
                    'orders_count' => $item->orders_count,
                    'avg_order_value' => $item->avg_order_value
                ];
            });
        
        // Phân tích theo giờ trong ngày
        $revenueByHour = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%H", created_at) as hour'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('hour')
            ->get();
        
        // Tổng quan doanh thu với các chỉ số kinh tế
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_order_value' => round($avgOrderValue, 0),
            'revenue_growth_rate' => $revenueGrowthRate,
            'monthly_growth_rate' => $monthlyGrowthRate,
            'revenue_per_day' => $totalOrders > 0 ? round($totalRevenue / max(1, $from->diffInDays(now())), 0) : 0,
            'orders_per_day' => $from->diffInDays(now()) > 0 ? round($totalOrders / $from->diffInDays(now()), 1) : 0,
            'best_selling_channel' => $revenueByChannel->first()->sales_channel ?? 'N/A',
            'best_selling_product' => $topProducts->first()->name ?? 'N/A',
            'peak_day' => $revenueByDayOfWeek->sortByDesc('revenue')->first()->day ?? 'N/A',
            'peak_hour' => $revenueByHour->sortByDesc('revenue')->first()->hour ?? 'N/A'
        ];

        return response()->json([
            'summary' => $summary,
            'daily_revenue' => $dailyRevenue,
            'weekly_revenue' => $weeklyRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'top_products' => $topProducts,
            'revenue_by_channel' => $revenueByChannel,
            'revenue_by_day_of_week' => $revenueByDayOfWeek,
            'revenue_by_hour' => $revenueByHour
        ]);
    }

    /**
     * API: Lấy dữ liệu phân tích khách hàng
     */
    public function customerAnalysis(Request $request)
    {
        $period = $request->get('period', '30d');
        $from = $this->getDateFromPeriod($period);

        // Khách hàng mới theo ngày
        $newCustomersDaily = Customer::where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%Y-%m-%d", created_at) as date'),
                DB::raw('COUNT(*) as new_customers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Phân tích RFM (Recency, Frequency, Monetary)
        $rfmAnalysis = $this->getRFMAnalysis($from);

        // Khách hàng theo nhóm
        $customersByGroup = Customer::join('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->select(
                'customer_groups.name as group_name',
                DB::raw('COUNT(customers.id) as customer_count')
            )
            ->groupBy('customer_groups.id', 'customer_groups.name')
            ->get();

        // Chi tiêu theo khu vực (tổng doanh thu theo tỉnh thành)
        $spendingByRegion = Order::join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', $from)
            ->select(
                'customers.city',
                DB::raw('SUM(orders.total_amount) as total_spending'),
                DB::raw('COUNT(DISTINCT orders.customer_id) as customer_count'),
                DB::raw('AVG(orders.total_amount) as avg_order_value')
            )
            ->groupBy('customers.city')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();

        // Khách hàng theo tỉnh thành (số lượng)
        $customersByProvince = Customer::select(
                'city',
                DB::raw('COUNT(*) as customer_count')
            )
            ->groupBy('city')
            ->orderByDesc('customer_count')
            ->limit(10)
            ->get();

        // Tỷ lệ khách hàng quay lại
        $repeatCustomers = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $totalCustomers = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->distinct('customer_id')
            ->count();

        $repeatRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        // Tính khách VIP (top 20% theo giá trị)
        $customerValues = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select('customer_id', DB::raw('SUM(total_amount) as total_value'))
            ->groupBy('customer_id')
            ->orderByDesc('total_value')
            ->get();
        
        $vipThreshold = $customerValues->count() > 0 ? $customerValues->slice(0, max(1, intval($customerValues->count() * 0.2)))->last()->total_value : 0;
        $vipCustomers = $customerValues->where('total_value', '>=', $vipThreshold)->count();

        // Tính tỷ lệ giữ chân (khách hàng có đơn trong 30 ngày qua)
        $retentionCustomers = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('customer_id')
            ->count();
        
        $totalCustomersInPeriod = Customer::where('created_at', '<=', now()->subDays(30))->count();
        $retentionRate = $totalCustomersInPeriod > 0 ? ($retentionCustomers / $totalCustomersInPeriod) * 100 : 0;

        // Phân khúc khách hàng theo giá trị và tần suất
        $customerSegments = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_value'),
                DB::raw('AVG(total_amount) as avg_order_value'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->groupBy('customer_id')
            ->get()
            ->map(function($customer) {
                $daysSinceLastOrder = now()->diffInDays($customer->last_order_date);
                
                // Phân loại theo giá trị
                if ($customer->total_value >= 5000000) {
                    $valueSegment = 'VIP';
                } elseif ($customer->total_value >= 2000000) {
                    $valueSegment = 'Cao';
                } elseif ($customer->total_value >= 500000) {
                    $valueSegment = 'Trung bình';
                } else {
                    $valueSegment = 'Thấp';
                }
                
                // Phân loại theo tần suất
                if ($customer->order_count >= 10) {
                    $frequencySegment = 'Thường xuyên';
                } elseif ($customer->order_count >= 5) {
                    $frequencySegment = 'Trung bình';
                } elseif ($customer->order_count >= 2) {
                    $frequencySegment = 'Thỉnh thoảng';
                } else {
                    $frequencySegment = 'Một lần';
                }
                
                // Phân loại theo recency
                if ($daysSinceLastOrder <= 30) {
                    $recencySegment = 'Gần đây';
                } elseif ($daysSinceLastOrder <= 90) {
                    $recencySegment = 'Trung bình';
                } else {
                    $recencySegment = 'Lâu rồi';
                }
                
                return [
                    'customer_id' => $customer->customer_id,
                    'order_count' => $customer->order_count,
                    'total_value' => $customer->total_value,
                    'avg_order_value' => $customer->avg_order_value,
                    'days_since_last_order' => $daysSinceLastOrder,
                    'value_segment' => $valueSegment,
                    'frequency_segment' => $frequencySegment,
                    'recency_segment' => $recencySegment
                ];
            });

        // Thống kê phân khúc
        $segmentStats = [
            'value_segments' => $customerSegments->groupBy('value_segment')->map->count(),
            'frequency_segments' => $customerSegments->groupBy('frequency_segment')->map->count(),
            'recency_segments' => $customerSegments->groupBy('recency_segment')->map->count()
        ];

        // Tổng quan khách hàng
        $summary = [
            'total_customers' => Customer::count(),
            'new_customers' => Customer::where('created_at', '>=', $from)->count(),
            'active_customers' => Order::whereIn('status', ['delivered'])
                ->where('created_at', '>=', $from)
                ->distinct('customer_id')
                ->count(),
            'repeat_rate' => round($repeatRate, 2),
            'vip_customers' => $vipCustomers,
            'avg_customer_value' => round($customerValues->avg('total_value') ?? 0, 0),
            'retention_rate' => round($retentionRate, 2),
            'growth_rate' => $this->calculateGrowthRate('customers', $period)
        ];

        return response()->json([
            'summary' => $summary,
            'new_customers_daily' => $newCustomersDaily,
            'rfm_analysis' => $rfmAnalysis,
            'customers_by_group' => $customersByGroup,
            'customers_by_province' => $customersByProvince,
            'spending_by_region' => $spendingByRegion,
            'customer_segments' => $customerSegments,
            'segment_stats' => $segmentStats
        ]);
    }

    /**
     * API: Lấy dữ liệu phân tích đơn hàng
     */
    public function orderAnalysis(Request $request)
    {
        $period = $request->get('period', '30d');
        $from = $this->getDateFromPeriod($period);

        // Đơn hàng theo trạng thái
        $ordersByStatus = Order::where('created_at', '>=', $from)
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('status')
            ->get();

        // Đơn hàng theo ngày trong tuần
        $ordersByDayOfWeek = Order::where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%w", created_at) as day_of_week'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day_of_week')
            ->get()
            ->map(function($item) {
                $days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                return [
                    'day' => $days[$item->day_of_week],
                    'count' => $item->count
                ];
            });

        // Đơn hàng theo giờ trong ngày
        $ordersByHour = Order::where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%H", created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->get();

        // Thời gian xử lý đơn hàng trung bình
        $avgProcessingTime = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->whereNotNull('completed_at')
            ->select(
                DB::raw('AVG((JULIANDAY(completed_at) - JULIANDAY(created_at)) * 24) as avg_hours')
            )
            ->first();

        // Tỷ lệ hủy đơn
        $cancelledOrders = Order::whereIn('status', ['failed'])
            ->where('created_at', '>=', $from)
            ->count();

        $totalOrders = Order::where('created_at', '>=', $from)->count();
        $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;

        // Giá trị đơn hàng trung bình
        $avgOrderValue = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', $from)
            ->avg('total_amount') ?? 0;

        // Giờ cao điểm (nhiều đơn nhất)
        $peakHour = Order::where('created_at', '>=', $from)
            ->select(
                DB::raw('strftime("%H", created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        // Tỷ lệ hoàn thành
        $completedOrders = Order::whereIn('status', ['delivered'])->where('created_at', '>=', $from)->count();
        $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        // Tổng quan đơn hàng
        $summary = [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => Order::whereIn('status', ['pending', 'processing'])->where('created_at', '>=', $from)->count(),
            'cancelled_orders' => $cancelledOrders,
            'cancellation_rate' => round($cancellationRate, 2),
            'completion_rate' => round($completionRate, 2),
            'avg_order_value' => round($avgOrderValue, 0),
            'avg_processing_time' => round($avgProcessingTime->avg_hours ?? 0, 1),
            'peak_order_hour' => $peakHour->hour ?? 'N/A',
            'growth_rate' => $this->calculateGrowthRate('orders', $period)
        ];

        return response()->json([
            'summary' => $summary,
            'orders_by_status' => $ordersByStatus,
            'orders_by_day_of_week' => $ordersByDayOfWeek,
            'orders_by_hour' => $ordersByHour
        ]);
    }

    /**
     * Tính toán phân tích RFM
     */
    private function getRFMAnalysis($from)
    {
        $rfmData = DB::table('customers')
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', $from)
            ->select(
                'customers.id',
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('COUNT(orders.id) as frequency'),
                DB::raw('SUM(orders.total_amount) as monetary')
            )
            ->groupBy('customers.id')
            ->get();

        // Phân loại RFM
        $rfmSegments = [
            'champions' => 0,      // R:5, F:5, M:5
            'loyal_customers' => 0, // R:4-5, F:4-5, M:4-5
            'potential_loyalists' => 0, // R:3-5, F:1-3, M:1-3
            'new_customers' => 0,   // R:4-5, F:1-2, M:1-2
            'promising' => 0,       // R:3-4, F:1-2, M:1-2
            'need_attention' => 0,  // R:2-3, F:2-3, M:2-3
            'about_to_sleep' => 0,  // R:2-3, F:1-2, M:1-2
            'at_risk' => 0,         // R:1-2, F:2-5, M:2-5
            'cannot_lose_them' => 0, // R:1-2, F:4-5, M:4-5
            'hibernating' => 0,     // R:1-2, F:1-2, M:1-2
            'lost' => 0            // R:1-2, F:1-2, M:1-2
        ];

        foreach ($rfmData as $customer) {
            $recency = $this->calculateRecencyScore($customer->last_order_date);
            $frequency = $this->calculateFrequencyScore($customer->frequency);
            $monetary = $this->calculateMonetaryScore($customer->monetary);

            $segment = $this->getRFMSegment($recency, $frequency, $monetary);
            $rfmSegments[$segment]++;
        }

        return $rfmSegments;
    }

    /**
     * Tính điểm Recency
     */
    private function calculateRecencyScore($lastOrderDate)
    {
        if (!$lastOrderDate) return 1;
        
        $daysSinceLastOrder = Carbon::parse($lastOrderDate)->diffInDays(now());
        
        if ($daysSinceLastOrder <= 30) return 5;
        if ($daysSinceLastOrder <= 60) return 4;
        if ($daysSinceLastOrder <= 90) return 3;
        if ($daysSinceLastOrder <= 180) return 2;
        return 1;
    }

    /**
     * Tính điểm Frequency
     */
    private function calculateFrequencyScore($frequency)
    {
        if ($frequency >= 10) return 5;
        if ($frequency >= 5) return 4;
        if ($frequency >= 3) return 3;
        if ($frequency >= 2) return 2;
        return 1;
    }

    /**
     * Tính điểm Monetary
     */
    private function calculateMonetaryScore($monetary)
    {
        if ($monetary >= 5000000) return 5; // 5M+
        if ($monetary >= 2000000) return 4; // 2M+
        if ($monetary >= 1000000) return 3; // 1M+
        if ($monetary >= 500000) return 2;  // 500K+
        return 1;
    }

    /**
     * Xác định phân khúc RFM
     */
    private function getRFMSegment($recency, $frequency, $monetary)
    {
        if ($recency >= 4 && $frequency >= 4 && $monetary >= 4) {
            return $recency == 5 && $frequency == 5 && $monetary == 5 ? 'champions' : 'loyal_customers';
        }
        if ($recency >= 4 && $frequency <= 2 && $monetary <= 2) return 'new_customers';
        if ($recency >= 3 && $frequency <= 3 && $monetary <= 3) return 'potential_loyalists';
        if ($recency >= 3 && $frequency <= 2 && $monetary <= 2) return 'promising';
        if ($recency >= 2 && $frequency >= 2 && $monetary >= 2) return 'need_attention';
        if ($recency >= 2 && $frequency <= 2 && $monetary <= 2) return 'about_to_sleep';
        if ($recency <= 2 && $frequency >= 2 && $monetary >= 2) {
            return $frequency >= 4 && $monetary >= 4 ? 'cannot_lose_them' : 'at_risk';
        }
        return 'hibernating';
    }

    /**
     * Tính tỷ lệ tăng trưởng
     */
    private function calculateGrowthRate($type, $period)
    {
        $currentFrom = $this->getDateFromPeriod($period);
        $previousFrom = $this->getDateFromPeriod($period, true);

        $currentValue = 0;
        $previousValue = 0;

        if ($type === 'revenue') {
            $currentValue = Order::whereIn('status', ['delivered'])
                ->where('created_at', '>=', $currentFrom)
                ->sum('total_amount');
            $previousValue = Order::whereIn('status', ['delivered'])
                ->where('created_at', '>=', $previousFrom)
                ->where('created_at', '<', $currentFrom)
                ->sum('total_amount');
        } elseif ($type === 'orders') {
            $currentValue = Order::where('created_at', '>=', $currentFrom)->count();
            $previousValue = Order::where('created_at', '>=', $previousFrom)
                ->where('created_at', '<', $currentFrom)
                ->count();
        }

        if ($previousValue == 0) return 0;
        
        // Đảm bảo kết quả không âm cho doanh thu tích lũy
        $growthRate = round((($currentValue - $previousValue) / $previousValue) * 100, 1);
        return max(0, $growthRate); // Không cho phép âm
    }

    /**
     * Tính tốc độ tăng trưởng doanh thu theo tuần
     */
    private function calculateWeeklyGrowthRate($weeklyRevenue)
    {
        if ($weeklyRevenue->count() < 2) return 0;
        
        $currentWeek = $weeklyRevenue->last();
        $previousWeek = $weeklyRevenue->slice(-2, 1)->first();
        
        if (!$currentWeek || !$previousWeek || $previousWeek->revenue == 0) return 0;
        
        // Đảm bảo kết quả không âm cho doanh thu tích lũy
        $growthRate = round((($currentWeek->revenue - $previousWeek->revenue) / $previousWeek->revenue) * 100, 1);
        return max(0, $growthRate); // Không cho phép âm
    }
    
    /**
     * Tính tốc độ tăng trưởng doanh thu theo tháng
     */
    private function calculateMonthlyGrowthRate($monthlyRevenue)
    {
        if ($monthlyRevenue->count() < 2) return 0;
        
        $currentMonth = $monthlyRevenue->last();
        $previousMonth = $monthlyRevenue->slice(-2, 1)->first();
        
        if (!$currentMonth || !$previousMonth || $previousMonth->revenue == 0) return 0;
        
        // Đảm bảo kết quả không âm cho doanh thu tích lũy
        $growthRate = round((($currentMonth->revenue - $previousMonth->revenue) / $previousMonth->revenue) * 100, 1);
        return max(0, $growthRate); // Không cho phép âm
    }

    /**
     * Lấy ngày bắt đầu từ period
     */
    private function getDateFromPeriod($period, $previous = false)
    {
        $multiplier = $previous ? 2 : 1;
        
        switch ($period) {
            case '7d':
                return now()->subDays(7 * $multiplier);
            case '30d':
                return now()->subDays(30 * $multiplier);
            case '90d':
                return now()->subDays(90 * $multiplier);
            case '1y':
                return now()->subYear($multiplier);
            default:
                return now()->subDays(30 * $multiplier);
        }
    }
}
