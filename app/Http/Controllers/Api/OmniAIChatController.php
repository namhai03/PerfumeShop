<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\PromotionService;
use App\Services\LLMService;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;

class OmniAIChatController extends Controller
{
    private LLMService $llmService;

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Endpoint chính xử lý chat cho OmniAI.
     * Intent: tra đơn hàng, tra khách hàng, sản phẩm tồn thấp, CTKM active, mô phỏng CTKM.
     * Fallback: LLM chat cho các câu hỏi khác.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
        ]);

        $message = trim($request->input('message'));

        try {
            // Rất đơn giản: nhận diện intent sơ bộ dựa vào từ khóa & pattern.
            if ($this->looksLikeOrderLookup($message)) {
                $orderNumber = $this->extractOrderNumber($message);
                return $this->handleOrderLookup($orderNumber);
            }

            if ($this->looksLikeCustomerLookup($message)) {
                $phone = $this->extractPhoneNumber($message);
                return $this->handleCustomerLookup($phone);
            }

            if ($this->looksLikeLowStock($message)) {
                $threshold = $this->extractThreshold($message) ?? 5;
                return $this->handleLowStock($threshold);
            }

            if ($this->looksLikeActivePromotions($message)) {
                return $this->handleActivePromotions();
            }

            if ($this->looksLikePromotionSimulation($message)) {
                $cart = $request->input('context.cart', []);
                return $this->handlePromotionSimulation($cart);
            }

            if ($this->looksLikeKpi($message)) {
                $period = $this->extractKpiPeriod($message) ?? 'today';
                return $this->handleKpi($period);
            }

            if ($this->looksLikeRevenueReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleRevenueReport($period);
            }

            if ($this->looksLikeCustomerReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleCustomerReport($period);
            }

            if ($this->looksLikeOrderReport($message)) {
                $period = $this->extractReportPeriod($message) ?? '30d';
                return $this->handleOrderReport($period);
            }

            // Fallback: gọi LLM cho tất cả câu hỏi khác
            if ($this->llmService->isConfigured()) {
                Log::info('OmniAI: Using LLM for message', ['message' => $message]);
                
                // Tìm kiếm sản phẩm liên quan nếu có từ khóa sản phẩm
                $relevantProducts = '';
                if ($this->looksLikeProductQuery($message)) {
                    $products = $this->searchProducts($message);
                    if (!empty($products)) {
                        $relevantProducts = $this->formatProductsForLLM($products);
                        Log::info('OmniAI: Found relevant products', ['count' => count($products)]);
                    }
                }

                $answer = $this->llmService->chat($message, [
                    'relevant_products' => $relevantProducts
                ]);
                
                Log::info('OmniAI: LLM response generated', ['response_length' => strlen($answer)]);
                
                return response()->json([
                    'success' => true,
                    'type' => 'llm',
                    'reply' => $answer,
                ]);
            } else {
                Log::warning('OmniAI: LLM not configured', ['api_key_set' => !empty(config('services.openai.api_key'))]);
            }

            return response()->json([
                'success' => true,
                'type' => 'smalltalk',
                'reply' => 'Bạn có thể hỏi: "đơn số X?", "sdt 09xx?", "tồn thấp < 5?", "ctkm đang chạy?", "kpi hôm nay/tuần/tháng?", "báo cáo doanh thu 30d?", "tìm nước hoa nam/nữ", "gợi ý sản phẩm", hoặc mô phỏng CTKM (đính kèm cart trong context).',
            ]);
        } catch (\Throwable $e) {
            Log::error('OmniAI chat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi xử lý yêu cầu.'], 500);
        }
    }

    private function looksLikeOrderLookup(string $m): bool
    {
        return preg_match('/(đơn|order)\s*(số|number)?\s*#?([A-Za-z0-9\-]+)/ui', $m) === 1;
    }

    private function extractOrderNumber(string $m): ?string
    {
        if (preg_match('/#?([A-Za-z0-9\-]{4,})/u', $m, $mm)) {
            return $mm[1];
        }
        return null;
    }

    private function handleOrderLookup(?string $orderNumber)
    {
        if (!$orderNumber) {
            return response()->json(['success' => false, 'error' => 'Không tìm được mã đơn trong câu hỏi'], 400);
        }
        $order = Order::with(['customer', 'items.product', 'latestShipment'])
            ->where('order_number', $orderNumber)
            ->first();
        if (!$order) {
            return response()->json(['success' => true, 'type' => 'order_lookup', 'found' => false, 'reply' => 'Không tìm thấy đơn ' . $orderNumber]);
        }
        return response()->json([
            'success' => true,
            'type' => 'order_lookup',
            'found' => true,
            'order' => $order,
        ]);
    }

    private function looksLikeCustomerLookup(string $m): bool
    {
        return preg_match('/(sđt|sdt|phone|điện\s*thoại)\s*(:|là)?\s*(\+?\d[\d\s\-]{6,})/ui', $m) === 1;
    }

    private function extractPhoneNumber(string $m): ?string
    {
        if (preg_match('/(\+?\d[\d\s\-]{6,})/u', $m, $mm)) {
            return preg_replace('/\s+/', '', $mm[1]);
        }
        return null;
    }

    private function handleCustomerLookup(?string $phone)
    {
        if (!$phone) {
            return response()->json(['success' => false, 'error' => 'Không tìm được SĐT trong câu hỏi'], 400);
        }
        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            return response()->json(['success' => true, 'type' => 'customer_lookup', 'found' => false, 'reply' => 'Không tìm thấy khách hàng với SĐT ' . $phone]);
        }
        return response()->json([
            'success' => true,
            'type' => 'customer_lookup',
            'found' => true,
            'customer' => $customer,
        ]);
    }

    private function looksLikeLowStock(string $m): bool
    {
        return preg_match('/(tồn\s*thấp|low\s*stock|hết\s*hàng)/ui', $m) === 1;
    }

    private function extractThreshold(string $m): ?int
    {
        if (preg_match('/(<|<=|dưới|<\s*)(\d{1,4})/ui', $m, $mm)) {
            return (int) $mm[2];
        }
        if (preg_match('/(\d{1,4})\s*(trở\s*xuống|hoặc\s*ít\s*hơn)/ui', $m, $mm)) {
            return (int) $mm[1];
        }
        return null;
    }

    private function handleLowStock(int $threshold)
    {
        $products = Product::with('variants')
            ->select('id','name')
            ->get()
            ->map(function ($p) use ($threshold) {
                $total = (int)($p->variants->sum('stock'));
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'total_stock' => $total,
                ];
            })
            ->filter(fn ($row) => $row['total_stock'] <= $threshold)
            ->values();

        return response()->json([
            'success' => true,
            'type' => 'low_stock',
            'threshold' => $threshold,
            'products' => $products,
        ]);
    }

    private function looksLikeActivePromotions(string $m): bool
    {
        return preg_match('/(ctkm|khuyến\s*mãi|promotion)\s*(đang\s*chạy|active)?/ui', $m) === 1;
    }

    private function handleActivePromotions()
    {
        $now = now();
        $promos = \App\Models\Promotion::where('is_active', true)
            ->where(function($q) use ($now){
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function($q) use ($now){
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('priority')
            ->get([
                'id','code','name','type','discount_value','max_discount_amount','min_order_amount','min_items','scope','is_stackable','priority','start_at','end_at'
            ]);

        return response()->json([
            'success' => true,
            'type' => 'promotions_active',
            'promotions' => $promos,
        ]);
    }

    private function looksLikePromotionSimulation(string $m): bool
    {
        return preg_match('/(mô\s*phỏng|simulate|tính)\s*(ctkm|khuyến\s*mãi|promotion)/ui', $m) === 1;
    }

    private function handlePromotionSimulation(array $cart)
    {
        if (empty($cart['items'] ?? [])) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng đính kèm cart vào context: items[{product_id, price, qty, category_ids[]}], subtotal(optional), sales_channel, customer_group_id'], 400);
        }
        /** @var PromotionService $svc */
        $svc = app(PromotionService::class);
        $result = $svc->calculate($cart);
        return response()->json([
            'success' => true,
            'type' => 'promotion_simulation',
            'result' => $result,
        ]);
    }

    private function looksLikeKpi(string $m): bool
    {
        return preg_match('/(kpi|tổng\s*quan|chỉ\s*số)\s*(hôm\s*nay|tuần|tháng|quý|năm)?/ui', $m) === 1;
    }

    private function extractKpiPeriod(string $m): ?string
    {
        if (preg_match('/hôm\s*nay/ui', $m)) return 'today';
        if (preg_match('/tuần/ui', $m)) return 'week';
        if (preg_match('/tháng/ui', $m)) return 'month';
        if (preg_match('/quý/ui', $m)) return 'quarter';
        if (preg_match('/năm/ui', $m)) return 'year';
        return null;
    }

    private function handleKpi(string $period)
    {
        $ctrl = app(DashboardController::class);
        $req = Request::create('/dashboard/kpi-data', 'GET', ['period' => $period]);
        $resp = $ctrl->getKpiData($req);
        $data = $resp->getData(true);
        return response()->json([
            'success' => true,
            'type' => 'kpi',
            'period' => $period,
            'kpis' => $data,
        ]);
    }

    private function looksLikeRevenueReport(string $m): bool
    {
        return preg_match('/(báo\s*cáo|phân\s*tích|doanh\s*thu)\s*(7d|30d|90d|1y)?/ui', $m) === 1;
    }

    private function looksLikeCustomerReport(string $m): bool
    {
        return preg_match('/(báo\s*cáo|phân\s*tích)\s*(khách\s*hàng)/ui', $m) === 1;
    }

    private function looksLikeOrderReport(string $m): bool
    {
        return preg_match('/(báo\s*cáo|phân\s*tích)\s*(đơn\s*hàng)/ui', $m) === 1;
    }

    private function extractReportPeriod(string $m): ?string
    {
        if (preg_match('/(7d|30d|90d|1y)/i', $m, $mm)) return strtolower($mm[1]);
        return null;
    }

    private function handleRevenueReport(string $period)
    {
        $ctrl = app(ReportController::class);
        $req = Request::create('/reports/revenue-analysis', 'GET', ['period' => $period]);
        $resp = $ctrl->revenueAnalysis($req);
        return response()->json([
            'success' => true,
            'type' => 'report_revenue',
            'period' => $period,
            'data' => $resp->getData(true),
        ]);
    }

    private function handleCustomerReport(string $period)
    {
        $ctrl = app(ReportController::class);
        $req = Request::create('/reports/customer-analysis', 'GET', ['period' => $period]);
        $resp = $ctrl->customerAnalysis($req);
        return response()->json([
            'success' => true,
            'type' => 'report_customer',
            'period' => $period,
            'data' => $resp->getData(true),
        ]);
    }

    private function handleOrderReport(string $period)
    {
        $ctrl = app(ReportController::class);
        $req = Request::create('/reports/order-analysis', 'GET', ['period' => $period]);
        $resp = $ctrl->orderAnalysis($req);
        return response()->json([
            'success' => true,
            'type' => 'report_order',
            'period' => $period,
            'data' => $resp->getData(true),
        ]);
    }

    /**
     * Kiểm tra xem câu hỏi có liên quan đến sản phẩm không
     */
    private function looksLikeProductQuery(string $message): bool
    {
        $productKeywords = [
            'nước hoa', 'perfume', 'mùi hương', 'fragrance',
            'tìm', 'search', 'gợi ý', 'recommend', 'sản phẩm',
            'nam', 'nữ', 'unisex', 'mùi', 'hương',
            'so sánh', 'compare', 'tốt', 'phù hợp',
            'giá', 'price', 'đắt', 'rẻ', 'khuyến mãi'
        ];

        $message = strtolower($message);
        
        foreach ($productKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tìm kiếm sản phẩm đơn giản dựa trên từ khóa
     */
    private function searchProducts(string $query): array
    {
        $keywords = $this->extractProductKeywords($query);
        if (empty($keywords)) {
            return [];
        }

        $products = Product::with(['categories', 'variants'])
            ->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('brand', 'like', "%{$keyword}%");
                }
            })
            ->limit(5)
            ->get();

        return $products->toArray();
    }

    /**
     * Trích xuất từ khóa sản phẩm từ câu hỏi
     */
    private function extractProductKeywords(string $query): array
    {
        $keywords = [];
        $query = strtolower($query);
        
        // Từ khóa nước hoa
        $perfumeKeywords = ['nước hoa', 'perfume', 'fragrance', 'mùi hương'];
        foreach ($perfumeKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $keywords[] = $keyword;
            }
        }

        // Từ khóa giới tính
        $genderKeywords = ['nam', 'nữ', 'unisex', 'male', 'female'];
        foreach ($genderKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $keywords[] = $keyword;
            }
        }

        // Từ khóa thương hiệu phổ biến
        $brandKeywords = ['chanel', 'dior', 'gucci', 'versace', 'armani', 'calvin klein', 'hugo boss'];
        foreach ($brandKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $keywords[] = $keyword;
            }
        }

        // Từ khóa mùi hương
        $fragranceKeywords = ['ngọt', 'ngọt ngào', 'hoa', 'gỗ', 'citrus', 'vanilla', 'musk'];
        foreach ($fragranceKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                $keywords[] = $keyword;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Format sản phẩm cho LLM
     */
    private function formatProductsForLLM(array $products): string
    {
        if (empty($products)) {
            return '';
        }

        $formatted = "Thông tin sản phẩm có sẵn:\n\n";
        
        foreach ($products as $index => $product) {
            $formatted .= ($index + 1) . ". {$product['name']}\n";
            if (!empty($product['description'])) {
                $formatted .= "   Mô tả: {$product['description']}\n";
            }
            if (!empty($product['brand'])) {
                $formatted .= "   Thương hiệu: {$product['brand']}\n";
            }
            if (!empty($product['categories']) && count($product['categories']) > 0) {
                $categoryNames = array_column($product['categories'], 'name');
                $formatted .= "   Danh mục: " . implode(', ', $categoryNames) . "\n";
            }
            $formatted .= "\n";
        }

        return $formatted;
    }

    /**
     * Test endpoint để kiểm tra LLM
     */
    public function testLLM(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $message = $request->input('message');
        
        try {
            if (!$this->llmService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'LLM chưa được cấu hình. Vui lòng thiết lập OPENAI_API_KEY.',
                    'config' => [
                        'api_key_set' => !empty(config('services.openai.api_key')),
                        'base_url' => config('services.openai.base_url'),
                        'model' => config('services.openai.model')
                    ]
                ]);
            }

            $answer = $this->llmService->chat($message);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'response' => $answer,
                'config' => [
                    'model' => config('services.openai.model'),
                    'base_url' => config('services.openai.base_url')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('LLM Test Error', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi test LLM: ' . $e->getMessage()
            ], 500);
        }
    }
}


