<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\LLMService;
use App\Services\VectorSearchService;
use App\Services\UniversalVectorSearchService;
use App\Services\DataService;
use Illuminate\Support\Facades\Log;

class SalesAgent
{
    private LLMService $llmService;
    private VectorSearchService $vectorSearchService;
    private UniversalVectorSearchService $universalVectorSearchService;
    private DataService $dataService;

    public function __construct(LLMService $llmService, VectorSearchService $vectorSearchService, UniversalVectorSearchService $universalVectorSearchService, DataService $dataService)
    {
        $this->llmService = $llmService;
        $this->vectorSearchService = $vectorSearchService;
        $this->universalVectorSearchService = $universalVectorSearchService;
        $this->dataService = $dataService;
    }

    /**
     * Process sales-related queries
     */
    public function process(string $message, array $context = []): array
    {
        Log::info('SalesAgent: Processing message', [
            'message' => substr($message, 0, 100),
            'full_message' => $message
        ]);

        try {
            // Check if we have classification context from AICoordinator
            if (isset($context['classification'])) {
                $classification = $context['classification'];
                Log::info('SalesAgent: Using classification context', [
                    'primary_intent' => $classification['primary'],
                    'confidence' => $classification['confidence']
                ]);
                
                // Route based on classification
                switch ($classification['primary']) {
                    case 'daily_orders':
                        Log::info('SalesAgent: Routing to daily orders based on classification');
                        return $this->handleDailyOrderQuery($message, $context);
                        
                    case 'customer_lookup':
                        Log::info('SalesAgent: Routing to customer lookup based on classification');
                        return $this->handleCustomerLookupWithLLM($message, $context);
                        
                    case 'order_lookup':
                        Log::info('SalesAgent: Routing to order lookup based on classification');
                        $orderNumber = $this->extractOrderNumber($message);
                        return $this->handleOrderLookup($orderNumber);
                        
                    case 'sales_analysis':
                        Log::info('SalesAgent: Routing to sales analysis based on classification');
                        return $this->handleSalesAnalysis($message, $context);
                        
                    case 'promotion_management':
                        Log::info('SalesAgent: Routing to promotion simulation based on classification');
                        $cart = $context['cart'] ?? [];
                        return $this->handlePromotionSimulation($cart);
                }
            }

            // Fallback to original pattern matching if no classification context
            Log::info('SalesAgent: No classification context, using pattern matching');
            
            // Daily order count query (check first to avoid conflict with order lookup)
            if ($this->looksLikeDailyOrderQuery($message)) {
                Log::info('SalesAgent: Detected daily order query');
                return $this->handleDailyOrderQuery($message, $context);
            }

            // Order lookup
            if ($this->looksLikeOrderLookup($message)) {
                Log::info('SalesAgent: Detected order lookup');
                $orderNumber = $this->extractOrderNumber($message);
                return $this->handleOrderLookup($orderNumber);
            }

            // Customer lookup
            if ($this->looksLikeCustomerLookup($message)) {
                Log::info('SalesAgent: Detected customer lookup');
                $phone = $this->extractPhoneNumber($message);
                return $this->handleCustomerLookup($phone);
            }

            // Vector store search for orders and customers
            if ($this->looksLikeVectorSearch($message)) {
                Log::info('SalesAgent: Detected vector search query');
                return $this->handleVectorSearch($message, $context);
            }

            // Sales analysis
            if ($this->looksLikeSalesAnalysis($message)) {
                Log::info('SalesAgent: Detected sales analysis');
                return $this->handleSalesAnalysis($message, $context);
            }

            // Promotion simulation
            if ($this->looksLikePromotionSimulation($message)) {
                Log::info('SalesAgent: Detected promotion simulation');
                $cart = $context['cart'] ?? [];
                return $this->handlePromotionSimulation($cart);
            }

            // Fallback to LLM with sales context
            Log::info('SalesAgent: No specific pattern matched, using general sales query');
            return $this->handleGeneralSalesQuery($message, $context);

        } catch (\Throwable $e) {
            Log::error('SalesAgent: Error processing message', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, Sales Agent gặp lỗi khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Check if message looks like order lookup
     */
    private function looksLikeOrderLookup(string $message): bool
    {
        return preg_match('/(đơn|order)\s*(số|number)?\s*#?([A-Za-z0-9\-]+)/ui', $message);
    }

    /**
     * Extract order number from message
     */
    private function extractOrderNumber(string $message): ?string
    {
        if (preg_match('/(đơn|order)\s*(số|number)?\s*#?([A-Za-z0-9\-]+)/ui', $message, $matches)) {
            return $matches[3];
        }
        return null;
    }

    /**
     * Handle order lookup
     */
    private function handleOrderLookup(?string $orderNumber): array
    {
        if (!$orderNumber) {
            return [
                'success' => true,
                'type' => 'order_lookup',
                'found' => false,
                'reply' => 'Vui lòng cung cấp mã đơn hàng để tra cứu.',
                'products' => []
            ];
        }

        $order = Order::where('order_number', $orderNumber)
            ->with(['customer', 'items.product'])
            ->first();

        if (!$order) {
            return [
                'success' => true,
                'type' => 'order_lookup',
                'found' => false,
                'reply' => "Không tìm thấy đơn hàng với mã: {$orderNumber}",
                'products' => []
            ];
        }

        $orderData = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name ?? 'N/A',
            'customer_phone' => $order->customer->phone ?? 'N/A',
            'status' => $order->status,
            'final_amount' => number_format($order->final_amount),
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'items_count' => $order->items->count()
        ];

        // Use LLM to generate natural response instead of hard-coded format
        $orderContext = [
            'order_data' => $orderData,
            'order_number' => $orderNumber,
            'suggestion' => $this->getOrderSuggestion($orderData['status'])
        ];

        $systemPrompt = "Bạn là Sales Agent chuyên nghiệp. Người dùng vừa tra cứu thông tin đơn hàng. Hãy trả lời một cách tự nhiên và thân thiện với thông tin đơn hàng được cung cấp.

**THÔNG TIN ĐƠN HÀNG:**
- Mã đơn hàng: {$orderNumber}
- Khách hàng: {$orderData['customer_name']}
- Số điện thoại: {$orderData['customer_phone']}
- Tổng tiền: {$orderData['final_amount']}đ
- Trạng thái: " . $this->formatOrderStatus($orderData['status']) . "
- Số sản phẩm: {$orderData['items_count']} sản phẩm
- Ngày tạo: {$orderData['created_at']}

**GỢI Ý:** " . $this->getOrderSuggestion($orderData['status']) . "

Hãy trả lời một cách tự nhiên, không cần theo format cố định. Sử dụng emoji phù hợp và phong cách thân thiện.";

        $reply = $this->llmService->chat("Tôi cần thông tin về đơn hàng {$orderNumber}", [
            'system' => $systemPrompt,
            'conversation_history' => $context['conversation_history'] ?? []
        ]);

        return [
            'success' => true,
            'type' => 'order_lookup',
            'found' => true,
            'order' => $orderData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like customer lookup
     */
    private function looksLikeCustomerLookup(string $message): bool
    {
        return preg_match('/(sđt|sdt|phone|điện\s*thoại)\s*(:|là)?\s*(\+?\d[\d\s\-]{6,})/ui', $message) ||
               preg_match('/(tìm|tra|kiểm\s*tra|thông\s*tin)\s*(khách\s*hàng|customer)/ui', $message) ||
               preg_match('/(khách\s*hàng|customer)\s*(nào|gì|đó)/ui', $message) ||
               preg_match('/\b[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+/u', $message);
    }

    /**
     * Extract phone number from message
     */
    private function extractPhoneNumber(string $message): ?string
    {
        // Try to extract phone number first
        if (preg_match('/(sđt|sdt|phone|điện\s*thoại)\s*(:|là)?\s*(\+?\d[\d\s\-]{6,})/ui', $message, $matches)) {
            return preg_replace('/[^\d]/', '', $matches[3]);
        }
        
        // Try to extract customer name
        if (preg_match('/\b([A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)/u', $message, $matches)) {
            return $matches[1];
        }
        
        // Try alternative pattern for "khách hàng [tên]"
        if (preg_match('/khách\s*hàng\s+([A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)/ui', $message, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Handle customer lookup
     */
    private function handleCustomerLookup(?string $phone): array
    {
        if (!$phone) {
            return [
                'success' => true,
                'type' => 'customer_lookup',
                'found' => false,
                'reply' => 'Vui lòng cung cấp số điện thoại để tra cứu khách hàng.',
                'products' => []
            ];
        }

        $customer = Customer::where('phone', 'LIKE', "%{$phone}%")
            ->orWhere('name', 'LIKE', "%{$phone}%")
            ->with(['orders'])
            ->first();

        if (!$customer) {
            return [
                'success' => true,
                'type' => 'customer_lookup',
                'found' => false,
                'reply' => "Không tìm thấy khách hàng với SĐT: {$phone}",
                'products' => []
            ];
        }

        $totalSpent = $customer->orders->sum('final_amount');
        $ordersCount = $customer->orders->count();

        $customerData = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email ?? 'N/A',
            'total_spent' => number_format($totalSpent),
            'orders_count' => $ordersCount,
            'last_order' => $customer->orders->max('created_at')?->format('d/m/Y') ?? 'N/A'
        ];

        // Use LLM to generate natural response instead of hard-coded format
        $systemPrompt = "Bạn là Sales Agent chuyên nghiệp. Người dùng vừa tra cứu thông tin khách hàng. Hãy trả lời một cách tự nhiên và thân thiện với thông tin khách hàng được cung cấp.

**THÔNG TIN KHÁCH HÀNG:**
- Họ tên: {$customerData['name']}
- Số điện thoại: {$customerData['phone']}
- Email: {$customerData['email']}
- Tổng chi tiêu: {$customerData['total_spent']}đ
- Số đơn hàng: {$ordersCount} đơn
- Đơn hàng gần nhất: {$customerData['last_order']}

**PHÂN TÍCH:** " . $this->getCustomerAnalysis($totalSpent, $ordersCount) . "

Hãy trả lời một cách tự nhiên, không cần theo format cố định. Sử dụng emoji phù hợp và phong cách thân thiện.";

        $reply = $this->llmService->chat("Tôi cần thông tin về khách hàng có SĐT {$phone}", [
            'system' => $systemPrompt,
            'conversation_history' => $context['conversation_history'] ?? []
        ]);

        return [
            'success' => true,
            'type' => 'customer_lookup',
            'found' => true,
            'customer' => $customerData,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like daily order count query
     */
    private function looksLikeDailyOrderQuery(string $message): bool
    {
        $patterns = [
            '/(hôm nay|today)\s*(có|co|co)\s*(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)/ui',
            '/(hôm qua|yesterday)\s*(có|co|co)\s*(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)/ui',
            '/(hôm kia|day before yesterday)\s*(có|co|co)\s*(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)/ui',
            '/(tháng này|this month)\s*(có|co|co)\s*(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)/ui',
            '/(đơn|don|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
            '/(số|so|so)\s*(đơn|don|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
            '/(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
            '/(thống kê|statistics)\s*(đơn|don|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
            '/(ngày|date)\s*(\d{1,2}\/\d{1,2})\s*(có|co|co)\s*(bao nhiêu|bao nhieu|bao nhieu)\s*(đơn|don|don)/ui'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                Log::info('SalesAgent: Daily order pattern matched', [
                    'message' => $message,
                    'pattern' => $pattern
                ]);
                return true;
            }
        }
        
        Log::info('SalesAgent: No daily order pattern matched', ['message' => $message]);
        return false;
    }

    /**
     * Handle daily order count query
     */
    private function handleDailyOrderQuery(string $message, array $context = []): array
    {
        try {
            // Xác định ngày cần tra cứu
            $targetDate = $this->extractDateFromMessage($message);
            $dateLabel = $this->getDateLabel($targetDate);
            
            // Kiểm tra xem có phải là "tháng này" không
            $isMonthlyQuery = preg_match('/tháng này|this month/ui', $message);
            
            if ($isMonthlyQuery) {
                // Query cho cả tháng
                $orders = Order::where('created_at', '>=', $targetDate)->get();
                $dateLabel = 'THÁNG NÀY';
            } else {
                // Query cho ngày cụ thể
                $orders = Order::whereDate('created_at', $targetDate)->get();
            }
            
            $orderCount = $orders->count();
            $revenue = $orders->sum('final_amount');
            $pendingOrders = Order::where('status', 'pending')->count();
            
            // Prepare data for LLM
            $orderDetails = [];
            if ($orderCount > 0) {
                $avgOrderValue = $revenue / $orderCount;
                foreach ($orders->take(5) as $order) {
                    $orderDetails[] = [
                        'order_number' => $order->order_number,
                        'amount' => number_format($order->final_amount),
                        'status' => $order->status
                    ];
                }
            }

            $systemPrompt = "Bạn là Sales Agent chuyên nghiệp. Người dùng vừa hỏi về thống kê đơn hàng. Hãy trả lời một cách tự nhiên và thân thiện với dữ liệu được cung cấp.

**THỐNG KÊ ĐƠN HÀNG {$dateLabel}:**
- Số đơn hàng: {$orderCount} đơn
- Doanh thu: " . number_format($revenue) . "đ
- Đơn chờ xử lý: {$pendingOrders} đơn" . 
($orderCount > 0 ? "\n- Giá trị đơn trung bình: " . number_format($revenue / $orderCount) . "đ" : "") . "

" . ($orderCount > 0 ? "**CHI TIẾT ĐƠN HÀNG:**\n" . implode("\n", array_map(function($order) {
    return "• {$order['order_number']} - {$order['amount']}đ - {$order['status']}";
}, $orderDetails)) . ($orders->count() > 5 ? "\n• ... và " . ($orders->count() - 5) . " đơn hàng khác" : "") : "Chưa có đơn hàng nào được ghi nhận trong hệ thống.") . "

Hãy trả lời một cách tự nhiên, không cần theo format cố định. Sử dụng emoji phù hợp và phong cách thân thiện.";

            $reply = $this->llmService->chat("Tôi cần thống kê đơn hàng {$dateLabel}", [
                'system' => $systemPrompt,
                'conversation_history' => $context['conversation_history'] ?? []
            ]);

            return [
                'success' => true,
                'type' => 'daily_orders',
                'data' => [
                    'order_count' => $orderCount,
                    'revenue' => $revenue,
                    'pending_orders' => $pendingOrders,
                    'target_date' => $targetDate->format('Y-m-d')
                ],
                'reply' => $reply,
                'products' => []
            ];

        } catch (\Throwable $e) {
            Log::error('SalesAgent: Error getting daily order data', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, không thể truy xuất dữ liệu đơn hàng hôm nay. Vui lòng thử lại sau.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Check if message looks like sales analysis
     */
    private function looksLikeSalesAnalysis(string $message): bool
    {
        return preg_match('/(phân tích|analysis|xu hướng|trend)\s*(bán hàng|sales)/ui', $message);
    }

    /**
     * Handle sales analysis
     */
    private function handleSalesAnalysis(string $message, array $context): array
    {
        // Get recent sales data
        $recentOrders = Order::where('created_at', '>=', now()->subDays(30))
            ->with(['items.product'])
            ->get();

        $totalRevenue = $recentOrders->sum('final_amount');
        $ordersCount = $recentOrders->count();
        $avgOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;

        // Top products
        $topProducts = $recentOrders->flatMap(function ($order) {
            return $order->items;
        })->groupBy('product_id')
        ->map(function ($items) {
            return [
                'product_id' => $items->first()->product_id,
                'product_name' => $items->first()->product->name ?? 'Unknown',
                'total_quantity' => $items->sum('quantity'),
                'total_revenue' => $items->sum(function ($item) {
                    return $item->quantity * $item->price;
                })
            ];
        })->sortByDesc('total_revenue')->take(5);

        $analysisData = [
            'period' => '30 ngày gần nhất',
            'total_revenue' => number_format($totalRevenue),
            'orders_count' => $ordersCount,
            'avg_order_value' => number_format($avgOrderValue),
            'top_products' => $topProducts->values()->toArray()
        ];

        // Generate professional analysis
        $analysis = $this->generateProfessionalSalesAnalysis($analysisData);

        return [
            'success' => true,
            'type' => 'sales_analysis',
            'analysis' => $analysisData,
            'reply' => $analysis,
            'products' => []
        ];
    }

    /**
     * Check if message looks like promotion simulation
     */
    private function looksLikePromotionSimulation(string $message): bool
    {
        return preg_match('/(mô phỏng|simulate|test)\s*(khuyến mãi|promotion)/ui', $message);
    }

    /**
     * Handle promotion simulation
     */
    private function handlePromotionSimulation(array $cart): array
    {
        // This would integrate with PromotionService
        // For now, return a simple simulation
        return [
            'success' => true,
            'type' => 'promotion_simulation',
            'result' => [
                'discount_total' => 50000,
                'shipping_discount' => 30000,
                'applied_promotions' => ['SUMMER2024', 'FREESHIP']
            ],
            'reply' => 'Kết quả mô phỏng: Giảm 50,000đ + Free ship 30,000đ. Áp dụng 2 CTKM: SUMMER2024, FREESHIP',
            'products' => []
        ];
    }

    /**
     * Handle general sales queries with enhanced business logic
     */
    private function handleGeneralSalesQuery(string $message, array $context): array
    {
        // Check if this is actually a daily order query that wasn't caught by pattern matching
        if (strpos(strtolower($message), 'hôm nay') !== false && 
            (strpos(strtolower($message), 'đơn') !== false || strpos(strtolower($message), 'don') !== false)) {
            Log::info('SalesAgent: Detected daily order query in general handler');
            return $this->handleDailyOrderQuery($message, $context);
        }

        if (!$this->llmService->isConfigured()) {
            return [
                'success' => true,
                'type' => 'general',
                'reply' => '🛒 Sales Agent: Tôi có thể giúp bạn:\n• Tra cứu đơn hàng theo mã số\n• Tìm kiếm khách hàng theo SĐT\n• Phân tích xu hướng bán hàng\n• Mô phỏng chương trình khuyến mãi\n• Xem lịch sử mua hàng của khách\n• Thống kê đơn hàng hôm nay\n\nVui lòng hỏi cụ thể hơn!',
                'products' => []
            ];
        }

        // Get enhanced sales context from DataService
        $salesData = $this->dataService->getAgentSpecificContext('sales');
        
        $systemPrompt = "Bạn là Sales Agent chuyên nghiệp trong cửa hàng nước hoa cao cấp. 
        
        **VAI TRÒ & TRÁCH NHIỆM:**
        - Chuyên gia phân tích và quản lý bán hàng
        - Tư vấn chiến lược kinh doanh và khuyến mãi
        - Hỗ trợ quản lý khách hàng và đơn hàng
        
        **KHẢ NĂNG CHUYÊN MÔN:**
        - Tra cứu đơn hàng: \"đơn số ABC123\", \"trạng thái đơn hàng\"
        - Quản lý khách hàng: \"khách hàng SĐT 09xx\", \"lịch sử mua hàng\"
        - Phân tích bán hàng: \"xu hướng bán hàng\", \"so sánh doanh số\"
        - Thống kê đơn hàng: \"hôm nay có bao nhiêu đơn\", \"số đơn hôm nay\"
        - Khuyến mãi: \"CTKM đang chạy\", \"mô phỏng giảm giá\"
        
        **PHONG CÁCH TRẢ LỜI:**
        - Chuyên nghiệp, chính xác, có cấu trúc rõ ràng
        - Sử dụng emoji phù hợp (🛒📊💰📈)
        - Đưa ra insights kinh doanh sâu sắc
        - Gợi ý hành động cụ thể và khả thi
        - Format dữ liệu dễ đọc với markdown
        - Luôn dựa trên dữ liệu thực tế từ hệ thống";

        $reply = $this->llmService->chat($message, [
            'system' => $systemPrompt,
            'conversation_history' => $context['conversation_history'] ?? [],
            'real_data' => $this->dataService->formatBusinessContextForLLM($this->dataService->getBusinessContext()),
            'agent_data' => $salesData
        ]);

        return [
            'success' => true,
            'type' => 'llm',
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Generate professional sales analysis
     */
    private function generateProfessionalSalesAnalysis(array $data): string
    {
        $topProducts = array_slice($data['top_products'] ?? [], 0, 3);
        $topProductsText = '';
        
        foreach ($topProducts as $index => $product) {
            $rank = $index + 1;
            $name = $product['name'] ?? 'Unknown';
            $totalRevenue = $product['total_revenue'] ?? 0;
            $totalQuantity = $product['total_quantity'] ?? 0;
            $topProductsText .= "{$rank}. **{$name}** - " . number_format($totalRevenue) . "đ ({$totalQuantity} sản phẩm)\n";
        }
        
        $growthRate = $data['growth_rate'] ?? 0;
        $growthIcon = $growthRate >= 0 ? '📈' : '📉';
        $growthText = $growthRate >= 0 ? 'tăng trưởng' : 'giảm';
        
        $period = $data['period'] ?? 'N/A';
        $totalRevenue = $data['total_revenue'] ?? 0;
        $ordersCount = $data['orders_count'] ?? 0;
        $avgOrderValue = $data['avg_order_value'] ?? 0;
        
        return "📊 **BÁO CÁO PHÂN TÍCH BÁN HÀNG**\n\n" .
               "📅 **Kỳ báo cáo:** {$period}\n\n" .
               "💰 **TỔNG QUAN DOANH THU**\n" .
               "• Tổng doanh thu: **{$totalRevenue}đ**\n" .
               "• Số đơn hàng: **{$ordersCount}** đơn\n" .
               "• Giá trị đơn TB: **{$avgOrderValue}đ**\n" .
               "• Tăng trưởng: {$growthIcon} **" . number_format($growthRate, 1) . "%** ({$growthText})\n\n" .
               "🏆 **TOP SẢN PHẨM BÁN CHẠY**\n" .
               $topProductsText . "\n" .
               "💡 **KHUYẾN NGHỊ:**\n" .
               $this->getSalesRecommendations($data);
    }

    /**
     * Get sales recommendations based on analysis
     */
    private function getSalesRecommendations(array $data): string
    {
        $recommendations = [];
        
        if ($data['growth_rate'] < 0) {
            $recommendations[] = "• Cần có chiến lược khuyến mãi để tăng doanh thu";
        }
        
        if ($data['avg_order_value'] < 1000000) {
            $recommendations[] = "• Khuyến khích khách mua combo để tăng giá trị đơn";
        }
        
        if ($data['orders_count'] < 50) {
            $recommendations[] = "• Tăng cường marketing để thu hút khách hàng mới";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "• Tiếp tục duy trì chiến lược hiện tại";
            $recommendations[] = "• Tập trung vào phát triển sản phẩm mới";
        }
        
        return implode("\n", $recommendations);
    }

    /**
     * Get customer analysis based on spending and order count
     */
    private function getCustomerAnalysis(float $totalSpent, int $ordersCount): string
    {
        $avgOrderValue = $ordersCount > 0 ? $totalSpent / $ordersCount : 0;
        
        if ($totalSpent >= 10000000) {
            $segment = "VIP - Khách hàng cao cấp";
            $suggestion = "Ưu tiên chăm sóc đặc biệt, gửi sản phẩm mới trước";
        } elseif ($totalSpent >= 5000000) {
            $segment = "Premium - Khách hàng trung thành";
            $suggestion = "Gửi khuyến mãi cá nhân hóa";
        } elseif ($totalSpent >= 1000000) {
            $segment = "Regular - Khách hàng thường xuyên";
            $suggestion = "Khuyến khích mua thêm với combo deals";
        } else {
            $segment = "New - Khách hàng mới";
            $suggestion = "Tập trung vào trải nghiệm và chăm sóc";
        }
        
        return "{$segment}. Giá trị đơn TB: " . number_format($avgOrderValue) . "đ. **Gợi ý:** {$suggestion}";
    }

    /**
     * Format order status professionally
     */
    private function formatOrderStatus(string $status): string
    {
        $statusMap = [
            'pending' => '⏳ Chờ xử lý',
            'processing' => '🔄 Đang xử lý',
            'shipped' => '🚚 Đã giao hàng',
            'delivered' => '✅ Đã giao thành công',
            'cancelled' => '❌ Đã hủy',
            'returned' => '↩️ Đã trả hàng'
        ];
        
        return $statusMap[$status] ?? "❓ {$status}";
    }

    /**
     * Get order suggestion based on status
     */
    private function getOrderSuggestion(string $status): string
    {
        $suggestions = [
            'pending' => 'Đơn hàng đang chờ xử lý. Cần kiểm tra và xác nhận.',
            'processing' => 'Đơn hàng đang được chuẩn bị. Có thể liên hệ khách hàng để cập nhật.',
            'shipped' => 'Đơn hàng đã được giao. Có thể theo dõi trạng thái giao hàng.',
            'delivered' => 'Đơn hàng đã hoàn thành. Có thể gửi feedback hoặc khuyến mãi tiếp theo.',
            'cancelled' => 'Đơn hàng đã hủy. Cần kiểm tra lý do và xử lý hoàn tiền nếu cần.',
            'returned' => 'Đơn hàng đã trả. Cần xử lý hoàn tiền và cập nhật tồn kho.'
        ];
        
        return $suggestions[$status] ?? 'Cần kiểm tra trạng thái đơn hàng.';
    }

    /**
     * Get current sales context for LLM
     */
    private function getSalesContext(): string
    {
        try {
            $todayOrders = Order::whereDate('created_at', today())->count();
            $todayRevenue = Order::whereDate('created_at', today())->sum('final_amount');
            $pendingOrders = Order::where('status', 'pending')->count();
            
            // Try to get promotions count, but don't fail if table doesn't exist
            $activePromotions = 0;
            try {
                $activePromotions = \App\Models\Promotion::where('is_active', true)->count();
            } catch (\Throwable $e) {
                Log::warning('Promotion table not accessible', ['error' => $e->getMessage()]);
            }
            
            return "📊 **TÌNH HÌNH BÁN HÀNG HÔM NAY**\n" .
                   "• Tổng đơn hàng: {$todayOrders} đơn\n" .
                   "• Doanh thu: " . number_format($todayRevenue) . "đ\n" .
                   "• Đơn chờ xử lý: {$pendingOrders} đơn\n" .
                   "• CTKM đang chạy: {$activePromotions} chương trình";
        } catch (\Throwable $e) {
            Log::error('SalesAgent: Error getting sales context', ['error' => $e->getMessage()]);
            return "⚠️ Dữ liệu bán hàng hiện tại không khả dụng. Lỗi: " . $e->getMessage();
        }
    }

    /**
     * Check if message looks like vector search query
     */
    private function looksLikeVectorSearch(string $message): bool
    {
        return preg_match('/(tìm|search|kiếm)\s*(đơn hàng|order|customer|khách hàng)/ui', $message) ||
               preg_match('/(hôm qua|yesterday|tháng này|this month)\s*(có|how many)/ui', $message);
    }

    /**
     * Handle vector store search for orders and customers
     */
    private function handleVectorSearch(string $message, array $context): array
    {
        try {
            // Get vector store from context
            $vectorStore = $context['vector_store'] ?? null;
            if (!$vectorStore) {
                return [
                    'success' => false,
                    'type' => 'error',
                    'reply' => 'Vector store không khả dụng.',
                    'products' => []
                ];
            }

            // Search orders
            $orderResults = $vectorStore->searchOrders($message, 5);
            
            // Search customers
            $customerResults = $vectorStore->searchCustomers($message, 3);

            $reply = "🔍 **KẾT QUẢ TÌM KIẾM**\n\n";
            
            if (!empty($orderResults)) {
                $reply .= "📦 **ĐƠN HÀNG LIÊN QUAN:**\n";
                foreach ($orderResults as $result) {
                    $data = $result['data'];
                    $metadata = $data['metadata'] ?? [];
                    $reply .= "• Đơn #{$data['embeddable_id']}: " . number_format($metadata['amount'] ?? 0) . "đ\n";
                }
                $reply .= "\n";
            }

            if (!empty($customerResults)) {
                $reply .= "👥 **KHÁCH HÀNG LIÊN QUAN:**\n";
                foreach ($customerResults as $result) {
                    $data = $result['data'];
                    $metadata = $data['metadata'] ?? [];
                    $name = $metadata['name'] ?? 'N/A';
                    $phone = $metadata['phone'] ?? 'N/A';
                    $reply .= "• {$name} - {$phone}\n";
                }
            }

            if (empty($orderResults) && empty($customerResults)) {
                $reply .= "Không tìm thấy dữ liệu liên quan đến: \"{$message}\"";
            }

            return [
                'success' => true,
                'type' => 'vector_search',
                'orders' => $orderResults,
                'customers' => $customerResults,
                'reply' => $reply,
                'products' => []
            ];

        } catch (\Throwable $e) {
            Log::error('SalesAgent: Error in vector search', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Lỗi khi tìm kiếm dữ liệu: ' . $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Extract date from message
     */
    private function extractDateFromMessage(string $message): \Carbon\Carbon
    {
        // Kiểm tra các từ khóa ngày tháng
        if (preg_match('/hôm qua|yesterday/ui', $message)) {
            return now()->subDay();
        } elseif (preg_match('/hôm kia|day before yesterday/ui', $message)) {
            return now()->subDays(2);
        } elseif (preg_match('/hôm nay|today/ui', $message)) {
            return now();
        } elseif (preg_match('/tháng này|this month/ui', $message)) {
            return now()->startOfMonth();
        } elseif (preg_match('/(\d{1,2})\/(\d{1,2})/ui', $message, $matches)) {
            // Xử lý định dạng dd/mm
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = now()->year;
            
            // Nếu tháng lớn hơn tháng hiện tại, có thể là năm trước
            if ($month > now()->month) {
                $year--;
            }
            
            return \Carbon\Carbon::create($year, $month, $day);
        } else {
            // Mặc định là hôm nay
            return now();
        }
    }

    /**
     * Get date label for display
     */
    private function getDateLabel(\Carbon\Carbon $date): string
    {
        $today = now();
        $yesterday = now()->subDay();
        $dayBeforeYesterday = now()->subDays(2);
        
        if ($date->isSameDay($today)) {
            return 'HÔM NAY';
        } elseif ($date->isSameDay($yesterday)) {
            return 'HÔM QUA';
        } elseif ($date->isSameDay($dayBeforeYesterday)) {
            return 'HÔM KIA';
        } else {
            return 'NGÀY ' . $date->format('d/m/Y');
        }
    }

    /**
     * Handle customer lookup using LLM with vector store
     */
    private function handleCustomerLookupWithLLM(string $message, array $context): array
    {
        if (!$this->llmService->isConfigured()) {
            return [
                'success' => true,
                'type' => 'customer_lookup',
                'reply' => '💬 Sales Agent: Tôi có thể giúp bạn tra cứu thông tin khách hàng. Vui lòng cung cấp tên hoặc số điện thoại.',
                'products' => []
            ];
        }

        try {
            // Search for customer using vector store
            $customerResults = [];
            try {
                // Use universalVectorSearchService for customer search
                $customerResults = $this->universalVectorSearchService->searchCustomers($message, 5);
            } catch (\Throwable $e) {
                Log::warning('SalesAgent: Vector search for customers failed', ['error' => $e->getMessage()]);
            }
            
            // Get business context for LLM
            $businessContext = $this->dataService->getBusinessContext();
            $salesData = $this->dataService->getAgentSpecificContext('sales');
            
            // Format customer data for LLM
            $customerData = '';
            
            // Try vector search first
            if (!empty($customerResults)) {
                $customerData = "👥 **THÔNG TIN KHÁCH HÀNG TÌM THẤY:**\n\n";
                foreach ($customerResults as $i => $customer) {
                    $customerData .= "**" . ($i + 1) . ". " . $customer['name'] . "**\n";
                    $customerData .= "• **SĐT:** " . ($customer['phone'] ?? 'N/A') . "\n";
                    $customerData .= "• **Email:** " . ($customer['email'] ?? 'N/A') . "\n";
                    $customerData .= "• **Địa chỉ:** " . ($customer['address'] ?? 'N/A') . "\n";
                    $customerData .= "• **Số đơn hàng:** " . ($customer['orders_count'] ?? 0) . "\n";
                    $customerData .= "• **Tổng chi tiêu:** " . number_format($customer['total_spent'] ?? 0) . "đ\n\n";
                }
            }
            
            // Always try DataService fallback if vector search didn't find anything
            if (empty($customerData) && isset($businessContext['customers']) && !empty($businessContext['customers'])) {
                $allCustomers = $businessContext['customers'];
                $filteredCustomers = $this->filterCustomersByQuery($allCustomers, $message);
                if (!empty($filteredCustomers)) {
                    $customerData = "👥 **THÔNG TIN KHÁCH HÀNG TÌM THẤY:**\n\n";
                    foreach (array_slice($filteredCustomers, 0, 5) as $i => $customer) {
                        $customerData .= "**" . ($i + 1) . ". " . $customer['name'] . "**\n";
                        $customerData .= "• **SĐT:** " . ($customer['phone'] ?? 'N/A') . "\n";
                        $customerData .= "• **Email:** " . ($customer['email'] ?? 'N/A') . "\n";
                        $customerData .= "• **Tổng chi tiêu:** " . number_format($customer['total_spent'] ?? 0) . "đ\n";
                        $customerData .= "• **Số đơn hàng:** " . ($customer['orders_count'] ?? 0) . "\n\n";
                    }
                }
            }

            $systemPrompt = "Bạn là Sales Agent chuyên nghiệp trong cửa hàng nước hoa cao cấp.

**VAI TRÒ & TRÁCH NHIỆM:**
- Chuyên gia tra cứu thông tin khách hàng
- Phân tích lịch sử mua hàng và hành vi khách hàng
- Tư vấn và hỗ trợ khách hàng

**KHẢ NĂNG CHUYÊN MÔN:**
- Tra cứu khách hàng: \"khách hàng Nguyễn Nam Hải\", \"sđt 09xx\"
- Thông tin chi tiết: lịch sử mua hàng, tổng chi tiêu, đơn hàng gần nhất
- Phân tích khách hàng: VIP, thường xuyên, mới

**QUAN TRỌNG - SỬ DỤNG DỮ LIỆU KHÁCH HÀNG:**
- Nếu có dữ liệu khách hàng trong customer_data, HÃY SỬ DỤNG CHÍNH XÁC
- Trả lời dựa trên thông tin thực tế được cung cấp
- KHÔNG được nói \"không có thông tin\" nếu có dữ liệu trong customer_data

**PHONG CÁCH TRẢ LỜI:**
- Chuyên nghiệp, chi tiết, có cấu trúc rõ ràng
- Format dữ liệu dễ đọc với markdown
- Luôn dựa trên dữ liệu thực tế từ hệ thống

**DỮ LIỆU KHÁCH HÀNG:**
{$customerData}";

            $reply = $this->llmService->chat($message, [
                'system' => $systemPrompt,
                'conversation_history' => $context['conversation_history'] ?? [],
                'real_data' => $this->dataService->formatBusinessContextForLLM($businessContext),
                'agent_data' => $salesData,
                'customer_data' => $customerData
            ]);

            return [
                'success' => true,
                'type' => 'customer_lookup',
                'reply' => $reply,
                'products' => []
            ];

        } catch (\Throwable $e) {
            Log::error('SalesAgent: Error in LLM customer lookup', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, đã có lỗi xảy ra khi tra cứu thông tin khách hàng.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Filter customers by query from DataService
     */
    private function filterCustomersByQuery(array $customers, string $query): array
    {
        $queryLower = strtolower($query);
        $filtered = [];
        
        foreach ($customers as $customer) {
            // Ensure customer is an array
            if (!is_array($customer)) {
                continue;
            }
            
            $customerText = strtolower(
                ($customer['name'] ?? '') . ' ' . 
                ($customer['phone'] ?? '') . ' ' . 
                ($customer['email'] ?? '')
            );
            
            // Check if query matches customer
            if (strpos($customerText, $queryLower) !== false) {
                $filtered[] = $customer;
            } else {
                // Check for name patterns
                if (preg_match('/\b([a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+\s+[a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)/u', $queryLower, $matches)) {
                    $namePattern = $matches[1];
                    if (strpos($customerText, $namePattern) !== false) {
                        $filtered[] = $customer;
                    }
                }
            }
        }
        
        return array_slice($filtered, 0, 5); // Limit to 5 customers
    }
}
