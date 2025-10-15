<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\InventoryMovement;
use App\Models\CashVoucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard tổng quan chính
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * API: Lấy dữ liệu KPI tổng quan
     */
    public function getKpiData(Request $request)
    {
        $period = $request->get('period', '30d');
        
        // Convert period to KPI period format
        $kpiPeriod = $this->convertToKpiPeriod($period);
        
        $dateRange = $this->getDateRange($kpiPeriod);
        $previousRange = $this->getPreviousDateRange($kpiPeriod);

        // KPI Cards
        $kpis = [
            'revenue' => $this->getRevenueKpi($dateRange, $previousRange),
            'orders' => $this->getOrdersKpi($dateRange, $previousRange),
            'customers' => $this->getCustomersKpi($dateRange, $previousRange),
            'custom_orders' => $this->getCustomOrdersKpi($dateRange, $previousRange),
            'inventory_alerts' => $this->getInventoryAlertsKpi(),
            'pending_orders' => $this->getPendingOrdersKpi(),
        ];

        return response()->json($kpis);
    }
    
    /**
     * Convert period to KPI period format
     */
    private function convertToKpiPeriod($period)
    {
        return match($period) {
            '7d' => 'week',
            '30d' => 'month',
            '90d' => 'quarter',
            '1y' => 'year',
            default => 'month'
        };
    }

    /**
     * API: Lấy dữ liệu biểu đồ tổng quan
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30d');
        
        $charts = [
            'revenue_trend' => $this->getRevenueTrend($period),
            'top_products' => $this->getTopProducts($period),
            'revenue_by_product' => $this->getRevenueByProduct($period),
            'customer_growth' => $this->getCustomerGrowth($period),
            'inventory_status' => $this->getInventoryStatus(),
        ];

        return response()->json($charts);
    }

    /**
     * API: Lấy thông tin nhanh và cảnh báo
     */
    public function getQuickInfo(Request $request)
    {
        $quickInfo = [
            'pending_orders' => $this->getPendingOrders(),
            'low_stock_products' => $this->getLowStockProducts(),
            'recent_customers' => $this->getRecentCustomers(),
            'cash_flow_summary' => $this->getCashFlowSummary(),
        ];

        return response()->json($quickInfo);
    }

    /**
     * Lấy khoảng thời gian theo period
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return [
                    'from' => Carbon::today(),
                    'to' => Carbon::today()->endOfDay()
                ];
            case 'week':
                return [
                    'from' => Carbon::now()->startOfWeek(),
                    'to' => Carbon::now()->endOfWeek()
                ];
            case 'month':
                return [
                    'from' => Carbon::now()->startOfMonth(),
                    'to' => Carbon::now()->endOfMonth()
                ];
            case 'quarter':
                return [
                    'from' => Carbon::now()->startOfQuarter(),
                    'to' => Carbon::now()->endOfQuarter()
                ];
            case 'year':
                return [
                    'from' => Carbon::now()->startOfYear(),
                    'to' => Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'from' => Carbon::today(),
                    'to' => Carbon::today()->endOfDay()
                ];
        }
    }

    /**
     * Lấy khoảng thời gian trước đó để so sánh
     */
    private function getPreviousDateRange($period)
    {
        switch ($period) {
            case 'today':
                return [
                    'from' => Carbon::yesterday(),
                    'to' => Carbon::yesterday()->endOfDay()
                ];
            case 'week':
                return [
                    'from' => Carbon::now()->subWeek()->startOfWeek(),
                    'to' => Carbon::now()->subWeek()->endOfWeek()
                ];
            case 'month':
                return [
                    'from' => Carbon::now()->subMonth()->startOfMonth(),
                    'to' => Carbon::now()->subMonth()->endOfMonth()
                ];
            case 'quarter':
                return [
                    'from' => Carbon::now()->subQuarter()->startOfQuarter(),
                    'to' => Carbon::now()->subQuarter()->endOfQuarter()
                ];
            case 'year':
                return [
                    'from' => Carbon::now()->subYear()->startOfYear(),
                    'to' => Carbon::now()->subYear()->endOfYear()
                ];
            default:
                return [
                    'from' => Carbon::yesterday(),
                    'to' => Carbon::yesterday()->endOfDay()
                ];
        }
    }

    /**
     * KPI Doanh thu
     */
    private function getRevenueKpi($current, $previous)
    {
        $currentRevenue = Order::whereIn('status', ['delivered'])
            ->whereBetween('created_at', [$current['from'], $current['to']])
            ->sum('total_amount');

        $previousRevenue = Order::whereIn('status', ['delivered'])
            ->whereBetween('created_at', [$previous['from'], $previous['to']])
            ->sum('total_amount');

        $change = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'value' => number_format($currentRevenue),
            'change' => round($change, 1),
            'change_type' => $change >= 0 ? 'positive' : 'negative',
            'icon' => 'fas fa-chart-line',
            'color' => '#10b981'
        ];
    }

    /**
     * KPI Đơn hàng
     */
    private function getOrdersKpi($current, $previous)
    {
        $currentOrders = Order::whereBetween('created_at', [$current['from'], $current['to']])
            ->count();

        $previousOrders = Order::whereBetween('created_at', [$previous['from'], $previous['to']])
            ->count();

        $change = $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;

        return [
            'value' => number_format($currentOrders),
            'change' => round($change, 1),
            'change_type' => $change >= 0 ? 'positive' : 'negative',
            'icon' => 'fas fa-shopping-cart',
            'color' => '#3b82f6'
        ];
    }

    /**
     * KPI Khách hàng mới
     */
    private function getCustomersKpi($current, $previous)
    {
        $currentCustomers = Customer::whereBetween('created_at', [$current['from'], $current['to']])
            ->count();

        $previousCustomers = Customer::whereBetween('created_at', [$previous['from'], $previous['to']])
            ->count();

        $change = $previousCustomers > 0 ? (($currentCustomers - $previousCustomers) / $previousCustomers) * 100 : 0;

        return [
            'value' => number_format($currentCustomers),
            'change' => round($change, 1),
            'change_type' => $change >= 0 ? 'positive' : 'negative',
            'icon' => 'fas fa-users',
            'color' => '#8b5cf6'
        ];
    }

    /**
     * KPI Đơn custom (tạm thời dùng type = 'sale' thay cho is_custom)
     */
    private function getCustomOrdersKpi($current, $previous)
    {
        // Tạm thời dùng type = 'sale' vì chưa có field is_custom
        $currentCustom = Order::where('type', 'sale')
            ->whereBetween('created_at', [$current['from'], $current['to']])
            ->count();

        $previousCustom = Order::where('type', 'sale')
            ->whereBetween('created_at', [$previous['from'], $previous['to']])
            ->count();

        $change = $previousCustom > 0 ? (($currentCustom - $previousCustom) / $previousCustom) * 100 : 0;

        return [
            'value' => number_format($currentCustom),
            'change' => round($change, 1),
            'change_type' => $change >= 0 ? 'positive' : 'negative',
            'icon' => 'fas fa-flask',
            'color' => '#f59e0b'
        ];
    }

    /**
     * KPI Cảnh báo tồn kho
     */
    private function getInventoryAlertsKpi()
    {
        $lowStockCount = Product::where('stock', '<=', DB::raw('low_stock_threshold'))
            ->where('is_active', true)
            ->count();

        return [
            'value' => number_format($lowStockCount),
            'change' => 0,
            'change_type' => $lowStockCount > 0 ? 'negative' : 'positive',
            'icon' => 'fas fa-exclamation-triangle',
            'color' => $lowStockCount > 0 ? '#ef4444' : '#10b981'
        ];
    }

    /**
     * KPI Đơn hàng chờ xử lý
     */
    private function getPendingOrdersKpi()
    {
        $pendingCount = Order::whereIn('status', ['new', 'processing'])
            ->count();

        return [
            'value' => number_format($pendingCount),
            'change' => 0,
            'change_type' => $pendingCount > 0 ? 'negative' : 'positive',
            'icon' => 'fas fa-clock',
            'color' => $pendingCount > 0 ? '#f59e0b' : '#10b981'
        ];
    }

    /**
     * Biểu đồ xu hướng doanh thu
     */
    private function getRevenueTrend($period)
    {
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };

        // Vì completed_at luôn NULL, sử dụng created_at để lọc theo thời gian
        // Nhưng cần lưu ý rằng đây là thời gian tạo đơn hàng, không phải giao hàng
        $revenueData = Order::whereIn('status', ['delivered'])
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Tạo mảng đầy đủ các ngày trong khoảng thời gian
        $labels = [];
        $revenue = [];
        $orders = [];
        
        // Bắt đầu từ $days ngày trước đến hôm nay
        for ($i = $days; $i > 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            
            $dayData = $revenueData->where('date', $date)->first();
            $revenue[] = $dayData ? (float)$dayData->revenue : 0;
            $orders[] = $dayData ? (int)$dayData->orders_count : 0;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders
        ];
    }

    /**
     * Top sản phẩm bán chạy
     */
    private function getTopProducts($period)
    {
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return $topProducts->toArray();
    }

    /**
     * Doanh thu theo sản phẩm (thay thế cho đơn hàng theo kênh)
     */
    private function getRevenueByProduct($period)
    {
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };

        $productRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['delivered'])
            ->where('orders.created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_revenue', 'desc')
            ->limit(8)
            ->get();

        return [
            'labels' => $productRevenue->pluck('name')->toArray(),
            'revenue' => $productRevenue->pluck('total_revenue')->toArray(),
            'quantity' => $productRevenue->pluck('total_quantity')->toArray()
        ];
    }

    /**
     * Tăng trưởng khách hàng
     */
    private function getCustomerGrowth($period)
    {
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            '1y' => 365,
            default => 30
        };

        $customerData = Customer::where('created_at', '>=', Carbon::now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as new_customers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Tạo mảng đầy đủ các ngày trong khoảng thời gian
        $labels = [];
        $customers = [];
        
        // Bắt đầu từ $days ngày trước đến hôm nay
        for ($i = $days; $i > 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            
            $dayData = $customerData->where('date', $date)->first();
            $customers[] = $dayData ? (int)$dayData->new_customers : 0;
        }

        return [
            'labels' => $labels,
            'customers' => $customers
        ];
    }

    /**
     * Trạng thái tồn kho
     */
    private function getInventoryStatus()
    {
        $inventoryStatus = [
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('stock', '<=', DB::raw('low_stock_threshold'))
                ->where('is_active', true)->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)
                ->where('is_active', true)->count(),
            'normal_stock' => Product::where('stock', '>', DB::raw('low_stock_threshold'))
                ->where('is_active', true)->count()
        ];

        return $inventoryStatus;
    }

    /**
     * Đơn hàng chờ xử lý
     */
    private function getPendingOrders()
    {
        return Order::with(['customer', 'items.product'])
            ->whereIn('status', ['new', 'processing'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Sản phẩm sắp hết hàng
     */
    private function getLowStockProducts()
    {
        return Product::where('stock', '<=', DB::raw('low_stock_threshold'))
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();
    }

    /**
     * Khách hàng mới nhất
     */
    private function getRecentCustomers()
    {
        return Customer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Tóm tắt dòng tiền
     */
    private function getCashFlowSummary()
    {
        $today = Carbon::today();
        
        $todayIncome = CashVoucher::where('type', 'income')
            ->whereDate('created_at', $today)
            ->sum('amount');

        $todayExpense = CashVoucher::where('type', 'expense')
            ->whereDate('created_at', $today)
            ->sum('amount');

        return [
            'today_income' => $todayIncome,
            'today_expense' => $todayExpense,
            'net_flow' => $todayIncome - $todayExpense
        ];
    }
}
