<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\LLMService;
use App\Services\VectorSearchService;
use App\Services\VectorEmbeddingService;
use App\Services\DataService;
use Illuminate\Support\Facades\Log;

class InventoryAgent
{
    private LLMService $llmService;
    private VectorSearchService $vectorSearchService;
    private VectorEmbeddingService $vectorEmbeddingService;
    private DataService $dataService;

    public function __construct(LLMService $llmService, VectorSearchService $vectorSearchService, VectorEmbeddingService $vectorEmbeddingService, DataService $dataService)
    {
        $this->llmService = $llmService;
        $this->vectorSearchService = $vectorSearchService;
        $this->vectorEmbeddingService = $vectorEmbeddingService;
        $this->dataService = $dataService;
    }

    /**
     * Process inventory-related queries
     */
    public function process(string $message, array $context = []): array
    {
        Log::info('InventoryAgent: Processing message', ['message' => substr($message, 0, 100)]);

        try {
            // Low stock check
            if ($this->looksLikeLowStock($message)) {
                $threshold = $this->extractThreshold($message) ?? 5;
                return $this->handleLowStock($threshold);
            }

            // Product search
            if ($this->looksLikeProductSearch($message)) {
                return $this->handleProductSearch($message, $context);
            }

            // Inventory history
            if ($this->looksLikeInventoryHistory($message)) {
                return $this->handleInventoryHistory($message, $context);
            }

            // Stock adjustment
            if ($this->looksLikeStockAdjustment($message)) {
                return $this->handleStockAdjustment($message, $context);
            }

            // Fallback to LLM with inventory context
            return $this->handleGeneralInventoryQuery($message, $context);

        } catch (\Throwable $e) {
            Log::error('InventoryAgent: Error processing message', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, Inventory Agent gặp lỗi khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Check if message looks like low stock query
     */
    private function looksLikeLowStock(string $message): bool
    {
        return preg_match('/(tồn|stock)\s*(thấp|low|hết)/ui', $message) ||
               preg_match('/(kiểm tra|check)\s*(tồn|stock)/ui', $message);
    }

    /**
     * Extract threshold from message
     */
    private function extractThreshold(string $message): ?int
    {
        if (preg_match('/(tồn|stock)\s*(thấp|low)\s*[<≤]\s*(\d+)/ui', $message, $matches)) {
            return (int) $matches[3];
        }
        return null;
    }

    /**
     * Handle low stock check
     */
    private function handleLowStock(int $threshold): array
    {
        $lowStockProducts = Product::where('is_active', true)
            ->where('stock', '<=', $threshold)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_stock' => $product->stock,
                    'low_stock_threshold' => $product->low_stock_threshold
                ];
            })
            ->values()
            ->toArray();

        if (empty($lowStockProducts)) {
            return [
                'success' => true,
                'type' => 'low_stock',
                'products' => [],
                'threshold' => $threshold,
                'reply' => "Không có sản phẩm nào có tồn ≤ {$threshold}."
            ];
        }

        $productNames = array_column($lowStockProducts, 'name');
        $reply = "⚠️ **CẢNH BÁO TỒN KHO THẤP**\n\n" .
                 "🔍 **Ngưỡng cảnh báo:** ≤ {$threshold} sản phẩm\n" .
                 "📊 **Tổng số sản phẩm:** " . count($lowStockProducts) . " sản phẩm\n\n" .
                 "📦 **DANH SÁCH SẢN PHẨM CẦN NHẬP HÀNG:**\n" .
                 $this->formatLowStockProducts($lowStockProducts) . "\n" .
                 "💡 **HÀNH ĐỘNG KHUYẾN NGHỊ:**\n" .
                 $this->getLowStockRecommendations(count($lowStockProducts));

        return [
            'success' => true,
            'type' => 'low_stock',
            'products' => $lowStockProducts,
            'threshold' => $threshold,
            'reply' => $reply
        ];
    }

    /**
     * Check if message looks like product search
     */
    private function looksLikeProductSearch(string $message): bool
    {
        return preg_match('/(tìm|search|gợi ý)\s*(sản phẩm|product|nước hoa)/ui', $message) ||
               preg_match('/(nước hoa|perfume)\s*(nam|nữ|men|women)/ui', $message);
    }

    /**
     * Handle product search with vector search
     */
    private function handleProductSearch(string $message, array $context): array
    {
        try {
            // Use vector search for semantic product search
            $searchResults = $this->vectorEmbeddingService->searchProducts($message, 5);
            
            if (empty($searchResults)) {
                return [
                    'success' => true,
                    'type' => 'product_search',
                    'products' => [],
                    'reply' => 'Không tìm thấy sản phẩm phù hợp với yêu cầu của bạn.'
                ];
            }

            $products = collect($searchResults)->map(function ($result) {
                $data = $result['data'];
                $metadata = $data['metadata'] ?? [];
                return [
                    'id' => $data['embeddable_id'],
                    'name' => $metadata['brand'] . ' ' . $data['content_text'],
                    'price' => number_format($metadata['price'] ?? 0),
                    'category' => $metadata['category'] ?? '',
                    'similarity' => round($result['final_score'] * 100, 1)
                ];
            })->toArray();

            $productNames = array_column($products, 'name');
            $reply = "🔍 **KẾT QUẢ TÌM KIẾM SẢN PHẨM**\n\n" .
                     "📊 **Tìm thấy:** " . count($products) . " sản phẩm phù hợp\n\n" .
                     "🛍️ **DANH SÁCH SẢN PHẨM:**\n" .
                     $this->formatProductSearchResults($products) . "\n" .
                     "💡 **GỢI Ý:** " . $this->getProductSearchSuggestions($products);

            return [
                'success' => true,
                'type' => 'product_search',
                'products' => $products,
                'reply' => $reply
            ];

        } catch (\Throwable $e) {
            Log::warning('InventoryAgent: Vector search failed, falling back to text search', ['error' => $e->getMessage()]);
            
            // Fallback to text search
            $products = Product::where('name', 'LIKE', "%{$message}%")
                ->orWhere('description', 'LIKE', "%{$message}%")
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => number_format($product->price),
                        'category' => $product->category->name ?? 'N/A',
                        'similarity' => 0
                    ];
                })
                ->toArray();

            if (empty($products)) {
                return [
                    'success' => true,
                    'type' => 'product_search',
                    'products' => [],
                    'reply' => 'Không tìm thấy sản phẩm phù hợp với yêu cầu của bạn.'
                ];
            }

            $productNames = array_column($products, 'name');
            $reply = "Tìm thấy " . count($products) . " sản phẩm: " . implode(', ', $productNames);

            return [
                'success' => true,
                'type' => 'product_search',
                'products' => $products,
                'reply' => $reply
            ];
        }
    }

    /**
     * Check if message looks like inventory history query
     */
    private function looksLikeInventoryHistory(string $message): bool
    {
        return preg_match('/(lịch sử|history)\s*(tồn|inventory|kho)/ui', $message) ||
               preg_match('/(xuất|nhập)\s*(kho|inventory)/ui', $message);
    }

    /**
     * Handle inventory history
     */
    private function handleInventoryHistory(string $message, array $context): array
    {
        $movements = InventoryMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => $movement->id,
                    'product_name' => $movement->product->name ?? 'Unknown',
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                    'reason' => $movement->reason,
                    'user_name' => $movement->user->name ?? 'System',
                    'created_at' => $movement->created_at->format('d/m/Y H:i')
                ];
            })
            ->toArray();

        if (empty($movements)) {
            return [
                'success' => true,
                'type' => 'inventory_history',
                'movements' => [],
                'reply' => 'Chưa có lịch sử tồn kho nào.',
                'products' => []
            ];
        }

        $movementTexts = array_map(function ($movement) {
            $type = $movement['type'] ?? 'Unknown';
            $quantity = $movement['quantity'] ?? 0;
            $productName = $movement['product_name'] ?? 'Unknown';
            $reason = $movement['reason'] ?? 'N/A';
            return "{$type} {$quantity} {$productName} - {$reason}";
        }, $movements);

        $reply = "Lịch sử tồn kho gần nhất:\n" . implode("\n", $movementTexts);

        return [
            'success' => true,
            'type' => 'inventory_history',
            'movements' => $movements,
            'reply' => $reply,
            'products' => []
        ];
    }

    /**
     * Check if message looks like stock adjustment
     */
    private function looksLikeStockAdjustment(string $message): bool
    {
        return preg_match('/(điều chỉnh|adjust|thay đổi)\s*(tồn|stock)/ui', $message) ||
               preg_match('/(cập nhật|update)\s*(số lượng|quantity)/ui', $message);
    }

    /**
     * Handle stock adjustment (proposal for human approval)
     */
    private function handleStockAdjustment(string $message, array $context): array
    {
        // This would typically require human approval
        return [
            'success' => true,
            'type' => 'stock_adjustment_proposal',
            'needs_approval' => true,
            'proposal' => [
                'type' => 'stock_adjustment',
                'message' => 'Đề xuất điều chỉnh tồn kho',
                'details' => 'Cần phê duyệt trước khi thực hiện điều chỉnh tồn kho'
            ],
            'reply' => 'Tôi có thể giúp bạn điều chỉnh tồn kho. Tuy nhiên, thao tác này cần được phê duyệt trước khi thực hiện.',
            'products' => []
        ];
    }

    /**
     * Handle general inventory queries with enhanced business logic
     */
    private function handleGeneralInventoryQuery(string $message, array $context): array
    {
        // Get real product data first
        $businessContext = $this->dataService->getBusinessContext();
        $products = $businessContext['products']['products'] ?? [];
        
        if (empty($products)) {
            return [
                'success' => true,
                'type' => 'general',
                'reply' => '📦 **BÁO CÁO TỒN KHO**\n\nKhông có dữ liệu sản phẩm trong hệ thống. Vui lòng thêm sản phẩm vào kho trước khi kiểm tra.',
                'products' => []
            ];
        }
        
        // Generate real inventory report
        $reply = $this->generateRealInventoryReport($products);
        
        return [
            'success' => true,
            'type' => 'inventory_report',
            'reply' => $reply,
            'products' => $products
        ];
    }
    
    /**
     * Generate real inventory report based on actual data
     */
    private function generateRealInventoryReport(array $products): string
    {
        $lowStockProducts = [];
        $outOfStockProducts = [];
        $normalStockProducts = [];
        
        foreach ($products as $product) {
            $totalStock = $product['variants'] ? 
                array_sum(array_column($product['variants'], 'stock')) : 
                ($product['stock'] ?? 0);
            
            if ($totalStock == 0) {
                $outOfStockProducts[] = $product;
            } elseif ($totalStock <= 5) {
                $lowStockProducts[] = $product;
            } else {
                $normalStockProducts[] = $product;
            }
        }
        
        $report = "📦 **BÁO CÁO TỒN KHO THỰC TẾ**\n\n";
        
        // Summary
        $report .= "**TỔNG QUAN:**\n";
        $report .= "• Tổng sản phẩm: " . count($products) . " sản phẩm\n";
        $report .= "• Hết hàng: " . count($outOfStockProducts) . " sản phẩm\n";
        $report .= "• Tồn thấp (≤5): " . count($lowStockProducts) . " sản phẩm\n";
        $report .= "• Tồn đủ (>5): " . count($normalStockProducts) . " sản phẩm\n\n";
        
        // Out of stock products
        if (!empty($outOfStockProducts)) {
            $report .= "**1. SẢN PHẨM HẾT HÀNG:**\n";
            foreach ($outOfStockProducts as $product) {
                $report .= "• " . $product['name'] . " (" . ($product['brand'] ?? 'N/A') . ")\n";
            }
            $report .= "\n";
        }
        
        // Low stock products
        if (!empty($lowStockProducts)) {
            $report .= "**2. SẢN PHẨM TỒN THẤP:**\n";
            foreach ($lowStockProducts as $product) {
                $totalStock = $product['variants'] ? 
                    array_sum(array_column($product['variants'], 'stock')) : 
                    ($product['stock'] ?? 0);
                $report .= "• " . $product['name'] . " - Tồn: " . $totalStock . " (" . ($product['brand'] ?? 'N/A') . ")\n";
            }
            $report .= "\n";
        }
        
        // Normal stock products
        if (!empty($normalStockProducts)) {
            $report .= "**3. SẢN PHẨM TỒN ĐỦ:**\n";
            foreach ($normalStockProducts as $product) {
                $totalStock = $product['variants'] ? 
                    array_sum(array_column($product['variants'], 'stock')) : 
                    ($product['stock'] ?? 0);
                $report .= "• " . $product['name'] . " - Tồn: " . $totalStock . " (" . ($product['brand'] ?? 'N/A') . ")\n";
            }
            $report .= "\n";
        }
        
        // Recommendations
        $report .= "**4. KHUYẾN NGHỊ:**\n";
        if (!empty($outOfStockProducts)) {
            $report .= "• Đặt hàng ngay cho " . count($outOfStockProducts) . " sản phẩm hết hàng\n";
        }
        if (!empty($lowStockProducts)) {
            $report .= "• Xem xét nhập thêm cho " . count($lowStockProducts) . " sản phẩm tồn thấp\n";
        }
        if (empty($outOfStockProducts) && empty($lowStockProducts)) {
            $report .= "• Tình trạng tồn kho ổn định\n";
        }
        
        $report .= "\nNếu cần thông tin chi tiết về sản phẩm cụ thể, hãy cho tôi biết! 📊";
        
        return $report;
    }

    /**
     * Format product search results professionally
     */
    private function formatProductSearchResults(array $products): string
    {
        $formatted = '';
        foreach ($products as $index => $product) {
            $rank = $index + 1;
            $similarity = $product['similarity'] ?? 0;
            $similarityIcon = $similarity >= 80 ? '🟢' : ($similarity >= 60 ? '🟡' : '🔴');
            
            $name = $product['name'] ?? 'Unknown';
            $price = $product['price'] ?? 'N/A';
            $category = $product['category'] ?? 'N/A';
            
            $formatted .= "{$rank}. **{$name}**\n";
            $formatted .= "   💰 Giá: {$price}đ\n";
            $formatted .= "   📂 Danh mục: {$category}\n";
            $formatted .= "   🎯 Độ phù hợp: {$similarityIcon} {$similarity}%\n\n";
        }
        
        return $formatted;
    }

    /**
     * Get product search suggestions
     */
    private function getProductSearchSuggestions(array $products): string
    {
        if (empty($products)) {
            return "Thử tìm kiếm với từ khóa khác hoặc mô tả chi tiết hơn";
        }
        
        $highSimilarityCount = count(array_filter($products, fn($p) => ($p['similarity'] ?? 0) >= 80));
        
        if ($highSimilarityCount > 0) {
            return "Có sản phẩm phù hợp cao, có thể xem chi tiết hoặc so sánh";
        } else {
            return "Có thể cần mô tả chi tiết hơn về mùi hương hoặc đặc điểm mong muốn";
        }
    }

    /**
     * Format low stock products professionally
     */
    private function formatLowStockProducts(array $products): string
    {
        $formatted = '';
        foreach ($products as $index => $product) {
            $rank = $index + 1;
            $totalStock = $product['total_stock'] ?? 0;
            $urgency = $totalStock == 0 ? '🔴 HẾT HÀNG' : 
                      ($totalStock <= 2 ? '🟠 CẤP BÁCH' : '🟡 CẢNH BÁO');
            
            $name = $product['name'] ?? 'Unknown';
            $formatted .= "{$rank}. **{$name}** - {$totalStock} sản phẩm ({$urgency})\n";
        }
        
        return $formatted;
    }

    /**
     * Get low stock recommendations
     */
    private function getLowStockRecommendations(int $productCount): string
    {
        if ($productCount == 0) {
            return "• Tình hình tồn kho ổn định, tiếp tục theo dõi";
        } elseif ($productCount <= 3) {
            return "• Ưu tiên nhập hàng cho các sản phẩm hết hàng\n• Liên hệ nhà cung cấp để đặt hàng\n• Cập nhật trạng thái sản phẩm trên website";
        } else {
            return "• Cần có kế hoạch nhập hàng tổng thể\n• Phân tích xu hướng tiêu thụ để dự báo nhu cầu\n• Xem xét điều chỉnh giá để cân bằng cung cầu";
        }
    }

    /**
     * Get current inventory context for LLM
     */
    private function getInventoryContext(): string
    {
        try {
            $lowStockProducts = Product::with(['variants'])
                ->get()
                ->filter(function ($product) {
                    return $product->variants->sum('stock_quantity') <= 5;
                })
                ->count();
            
            $totalProducts = Product::count();
            $outOfStockProducts = Product::with(['variants'])
                ->get()
                ->filter(function ($product) {
                    return $product->variants->sum('stock_quantity') == 0;
                })
                ->count();
            
            return "Tổng sản phẩm: {$totalProducts}. Tồn thấp (≤5): {$lowStockProducts}. Hết hàng: {$outOfStockProducts}";
        } catch (\Throwable $e) {
            return "Dữ liệu tồn kho hiện tại không khả dụng";
        }
    }

    /**
     * Format products for inventory context
     */
    private function formatProductsForInventory(array $products): string
    {
        if (empty($products)) {
            return '';
        }

        $formatted = "📦 **DANH SÁCH SẢN PHẨM HIỆN TẠI:**\n\n";
        
        foreach ($products as $i => $product) {
            $formatted .= "**" . ((int)$i + 1) . ". " . $product['name'] . "**\n";
            $formatted .= "• **Thương hiệu:** " . ($product['brand'] ?? 'N/A') . "\n";
            $price = $product['price'] ?? 0;
            $formatted .= "• **Giá:** " . (is_numeric($price) ? number_format((float)$price) : 'N/A') . "đ\n";
            $formatted .= "• **Tồn kho:** " . (int)($product['stock'] ?? 0) . " sản phẩm\n";
            $formatted .= "• **Danh mục:** " . ($product['category'] ?? 'N/A') . "\n";
            $formatted .= "• **Trạng thái:** " . ($product['is_active'] ? 'Hoạt động' : 'Ngừng bán') . "\n";
            
            if (!empty($product['variants'])) {
                $formatted .= "• **Biến thể:** " . count($product['variants']) . " loại\n";
            }
            
            $formatted .= "\n";
        }

        return $formatted;
    }
}
