<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\LLMService;
use App\Services\VectorSearchService;
use App\Services\DataService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReportAgent
{
    private LLMService $llmService;
    private VectorSearchService $vectorSearchService;
    private DataService $dataService;

    public function __construct(LLMService $llmService, VectorSearchService $vectorSearchService, DataService $dataService)
    {
        $this->llmService = $llmService;
        $this->vectorSearchService = $vectorSearchService;
        $this->dataService = $dataService;
    }

    /**
     * Process report-related queries
     */
    public function process(string $message, array $context = []): array
    {
        Log::info('ReportAgent: Processing message', ['message' => substr($message, 0, 100)]);

        try {
            // KPI analysis
            if ($this->looksLikeKpi($message)) {
                $period = $this->extractKpiPeriod($message) ?? 'today';
                return $this->handleKpi($period);
            }

            // Revenue report
            if ($this->looksLikeRevenueReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleRevenueReport($period);
            }

            // Customer report
            if ($this->looksLikeCustomerReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleCustomerReport($period);
            }

            // Order report
            if ($this->looksLikeOrderReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleOrderReport($period);
            }

            // Export report
            if ($this->looksLikeExportReport($message)) {
                return $this->handleExportReport($message, $context);
            }

            // Fallback to LLM with report context
            return $this->handleGeneralReportQuery($message, $context);

        } catch (\Throwable $e) {
            Log::error('ReportAgent: Error processing message', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, Report Agent gặp lỗi khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Check if message looks like KPI query
     */
    private function looksLikeKpi(string $message): bool
    {
        return preg_match('/(kpi|tổng\s*quan|chỉ\s*số)\s*(hôm\s*nay|tuần|tháng|quý|năm)?/ui', $message);
    }

    /**
     * Extract KPI period from message
     */
    private function extractKpiPeriod(string $message): ?string
    {
        if (preg_match('/(hôm\s*nay|today)/ui', $message)) return 'today';
        if (preg_match('/(tuần|week)/ui', $message)) return 'week';
        if (preg_match('/(tháng|month)/ui', $message)) return 'month';
        if (preg_match('/(quý|quarter)/ui', $message)) return 'quarter';
        if (preg_match('/(năm|year)/ui', $message)) return 'year';
        return null;
    }

    /**
     * Handle KPI analysis
     */
    private function handleKpi(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $kpiData = [
            'period' => $period,
            'date_range' => $dateRange,
            'revenue' => $this->getRevenue($dateRange),
            'orders' => $this->getOrdersCount($dateRange),
            'customers' => $this->getCustomersCount($dateRange),
            'avg_order_value' => $this->getAvgOrderValue($dateRange),
            'top_products' => $this->getTopProducts($dateRange, 5),
            'growth_rate' => $this->getGrowthRate($dateRange)
        ];

        $reply = $this->formatKpiReport($kpiData);

        return [
            'success' => true,
            'type' => 'kpi',
            'kpi' => $kpiData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like revenue report
     */
    private function looksLikeRevenueReport(string $message): bool
    {
        return preg_match('/(báo cáo|report)\s*(doanh thu|revenue)/ui', $message) ||
               preg_match('/(doanh thu|revenue)\s*(báo cáo|report)/ui', $message);
    }

    /**
     * Extract report period from message
     */
    private function extractReportPeriod(string $message): ?string
    {
        if (preg_match('/(\d+)\s*d/ui', $message, $matches)) return $matches[1] . 'd';
        if (preg_match('/(\d+)\s*w/ui', $message, $matches)) return $matches[1] . 'w';
        if (preg_match('/(\d+)\s*m/ui', $message, $matches)) return $matches[1] . 'm';
        return null;
    }

    /**
     * Handle revenue report
     */
    private function handleRevenueReport(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $revenueData = [
            'period' => $period,
            'date_range' => $dateRange,
            'total_revenue' => $this->getRevenue($dateRange),
            'daily_revenue' => $this->getDailyRevenue($dateRange),
            'revenue_by_category' => $this->getRevenueByCategory($dateRange),
            'revenue_growth' => $this->getRevenueGrowth($dateRange)
        ];

        $reply = $this->formatRevenueReport($revenueData);

        return [
            'success' => true,
            'type' => 'revenue_report',
            'revenue' => $revenueData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like customer report
     */
    private function looksLikeCustomerReport(string $message): bool
    {
        return preg_match('/(báo cáo|report)\s*(khách hàng|customer)/ui', $message) ||
               preg_match('/(phân tích|analysis)\s*(khách hàng|customer)/ui', $message);
    }

    /**
     * Handle customer report
     */
    private function handleCustomerReport(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $customerData = [
            'period' => $period,
            'date_range' => $dateRange,
            'total_customers' => $this->getCustomersCount($dateRange),
            'new_customers' => $this->getNewCustomersCount($dateRange),
            'customer_segments' => $this->getCustomerSegments($dateRange),
            'top_customers' => $this->getTopCustomers($dateRange, 10),
            'customer_retention' => $this->getCustomerRetention($dateRange)
        ];

        $reply = $this->formatCustomerReport($customerData);

        return [
            'success' => true,
            'type' => 'customer_report',
            'customers' => $customerData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like order report
     */
    private function looksLikeOrderReport(string $message): bool
    {
        return preg_match('/(báo cáo|report)\s*(đơn hàng|order)/ui', $message) ||
               preg_match('/(phân tích|analysis)\s*(đơn hàng|order)/ui', $message);
    }

    /**
     * Handle order report
     */
    private function handleOrderReport(string $period): array
    {
        $dateRange = $this->getDateRange($period);
        
        $orderData = [
            'period' => $period,
            'date_range' => $dateRange,
            'total_orders' => $this->getOrdersCount($dateRange),
            'orders_by_status' => $this->getOrdersByStatus($dateRange),
            'orders_by_day' => $this->getOrdersByDay($dateRange),
            'avg_order_value' => $this->getAvgOrderValue($dateRange),
            'order_trends' => $this->getOrderTrends($dateRange)
        ];

        $reply = $this->formatOrderReport($orderData);

        return [
            'success' => true,
            'type' => 'order_report',
            'orders' => $orderData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like export report
     */
    private function looksLikeExportReport(string $message): bool
    {
        return preg_match('/(xuất|export)\s*(báo cáo|report)/ui', $message) ||
               preg_match('/(tải|download)\s*(báo cáo|report)/ui', $message);
    }

    /**
     * Handle export report (proposal for human approval)
     */
    private function handleExportReport(string $message, array $context): array
    {
        // This would typically require human approval
        return [
            'success' => true,
            'type' => 'export_proposal',
            'needs_approval' => true,
            'proposal' => [
                'type' => 'export_report',
                'message' => 'Đề xuất xuất báo cáo',
                'details' => 'Cần phê duyệt trước khi xuất báo cáo Excel'
            ],
            'reply' => 'Tôi có thể giúp bạn xuất báo cáo Excel. Tuy nhiên, thao tác này cần được phê duyệt trước khi thực hiện.',
            'products' => []
        ];
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period): array
    {
        $now = now();
        
        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'quarter':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            default:
                if (preg_match('/(\d+)d/', $period, $matches)) {
                    $days = (int) $matches[1];
                    return [$now->copy()->subDays($days), $now];
                }
                return [$now->copy()->subDays(30), $now];
        }
    }

    /**
     * Get revenue for date range
     */
    private function getRevenue(array $dateRange): float
    {
        return Order::whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'cancelled')
            ->sum('final_amount');
    }

    /**
     * Get orders count for date range
     */
    private function getOrdersCount(array $dateRange): int
    {
        return Order::whereBetween('created_at', $dateRange)->count();
    }

    /**
     * Get customers count for date range
     */
    private function getCustomersCount(array $dateRange): int
    {
        return Customer::whereBetween('created_at', $dateRange)->count();
    }

    /**
     * Get average order value for date range
     */
    private function getAvgOrderValue(array $dateRange): float
    {
        $ordersCount = $this->getOrdersCount($dateRange);
        if ($ordersCount === 0) return 0;
        
        return $this->getRevenue($dateRange) / $ordersCount;
    }

    /**
     * Get top products for date range
     */
    private function getTopProducts(array $dateRange, int $limit = 5): array
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', $dateRange)
            ->where('orders.status', '!=', 'cancelled')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get growth rate
     */
    private function getGrowthRate(array $dateRange): float
    {
        $currentRevenue = $this->getRevenue($dateRange);
        
        // Get previous period revenue
        $periodLength = $dateRange[1]->diffInDays($dateRange[0]);
        $previousStart = $dateRange[0]->copy()->subDays($periodLength);
        $previousEnd = $dateRange[0]->copy();
        
        $previousRevenue = $this->getRevenue([$previousStart, $previousEnd]);
        
        if ($previousRevenue == 0) return 0;
        
        return (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
    }

    /**
     * Format KPI report
     */
    private function formatKpiReport(array $kpiData): string
    {
        $growthIcon = $kpiData['growth_rate'] >= 0 ? '📈' : '📉';
        
        return "📊 KPI {$kpiData['period']}:\n" .
               "• Doanh thu: " . number_format($kpiData['revenue']) . "đ ({$growthIcon} " . number_format($kpiData['growth_rate'], 1) . "%)\n" .
               "• Đơn hàng: " . number_format($kpiData['orders']) . "\n" .
               "• Khách hàng: " . number_format($kpiData['customers']) . "\n" .
               "• Giá trị đơn TB: " . number_format($kpiData['avg_order_value']) . "đ";
    }

    /**
     * Format revenue report
     */
    private function formatRevenueReport(array $revenueData): string
    {
        return "💰 Báo cáo doanh thu {$revenueData['period']}:\n" .
               "• Tổng doanh thu: " . number_format($revenueData['total_revenue']) . "đ\n" .
               "• Tăng trưởng: " . number_format($revenueData['revenue_growth'], 1) . "%\n" .
               "• Doanh thu TB/ngày: " . number_format($revenueData['total_revenue'] / max(1, $revenueData['date_range'][1]->diffInDays($revenueData['date_range'][0]))) . "đ";
    }

    /**
     * Format customer report
     */
    private function formatCustomerReport(array $customerData): string
    {
        return "👥 Báo cáo khách hàng {$customerData['period']}:\n" .
               "• Tổng KH: " . number_format($customerData['total_customers']) . "\n" .
               "• KH mới: " . number_format($customerData['new_customers']) . "\n" .
               "• Tỷ lệ giữ chân: " . number_format($customerData['customer_retention'], 1) . "%";
    }

    /**
     * Format order report
     */
    private function formatOrderReport(array $orderData): string
    {
        return "📦 Báo cáo đơn hàng {$orderData['period']}:\n" .
               "• Tổng đơn: " . number_format($orderData['total_orders']) . "\n" .
               "• Giá trị TB: " . number_format($orderData['avg_order_value']) . "đ\n" .
               "• Đơn/ngày: " . number_format($orderData['total_orders'] / max(1, $orderData['date_range'][1]->diffInDays($orderData['date_range'][0])), 1);
    }

    /**
     * Handle general report queries with LLM
     */
    private function handleGeneralReportQuery(string $message, array $context): array
    {
        if (!$this->llmService->isConfigured()) {
            return [
                'success' => true,
                'type' => 'general',
                'reply' => 'Report Agent: Tôi có thể giúp bạn tạo báo cáo KPI, doanh thu, khách hàng, đơn hàng. Vui lòng hỏi cụ thể hơn.',
                'products' => []
            ];
        }

        // Get enhanced report context from DataService
        $reportData = $this->dataService->getAgentSpecificContext('report');
        
        $systemPrompt = "Bạn là Report Agent chuyên về phân tích và báo cáo trong cửa hàng nước hoa.
        
        **VAI TRÒ & TRÁCH NHIỆM:**
        - Chuyên gia phân tích dữ liệu và tạo báo cáo
        - Cung cấp insights kinh doanh dựa trên số liệu thực tế
        - Hỗ trợ ra quyết định chiến lược
        
        **KHẢ NĂNG CHUYÊN MÔN:**
        - Báo cáo KPI: \"kpi hôm nay\", \"chỉ số kinh doanh\"
        - Báo cáo doanh thu: \"doanh thu tháng\", \"xu hướng tăng trưởng\"
        - Báo cáo khách hàng: \"phân tích khách hàng\", \"customer segments\"
        - Báo cáo đơn hàng: \"thống kê đơn hàng\", \"order analysis\"
        - Xuất báo cáo: \"export excel\", \"tải báo cáo\"
        
        **PHONG CÁCH TRẢ LỜI:**
        - Chuyên nghiệp, có cấu trúc, dễ hiểu
        - Sử dụng emoji phù hợp (📊📈📉💰)
        - Đưa ra insights sâu sắc từ dữ liệu
        - Gợi ý hành động dựa trên phân tích
        - Format dữ liệu rõ ràng với markdown
        - Luôn dựa trên dữ liệu thực tế từ hệ thống";

        $reply = $this->llmService->chat($message, [
            'system' => $systemPrompt,
            'conversation_history' => $context['conversation_history'] ?? [],
            'real_data' => $this->dataService->formatBusinessContextForLLM($this->dataService->getBusinessContext()),
            'agent_data' => $reportData
        ]);

        return [
            'success' => true,
            'type' => 'llm',
            'reply' => $reply,
            'products' => []
        ];
    }

    // Additional helper methods for report data...
    private function getDailyRevenue(array $dateRange): array { return []; }
    private function getRevenueByCategory(array $dateRange): array { return []; }
    private function getRevenueGrowth(array $dateRange): float { return 0; }
    private function getNewCustomersCount(array $dateRange): int { return 0; }
    private function getCustomerSegments(array $dateRange): array { return []; }
    private function getTopCustomers(array $dateRange, int $limit): array { return []; }
    private function getCustomerRetention(array $dateRange): float { return 0; }
    private function getOrdersByStatus(array $dateRange): array { return []; }
    private function getOrdersByDay(array $dateRange): array { return []; }
    private function getOrderTrends(array $dateRange): array { return []; }
}
