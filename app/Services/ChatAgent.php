<?php

namespace App\Services;

use App\Models\Product;
use App\Services\LLMService;
use App\Services\VectorSearchService;
use App\Services\VectorEmbeddingService;
use App\Services\DataService;
use Illuminate\Support\Facades\Log;

class ChatAgent
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
     * Process chat and semantic search queries
     */
    public function process(string $message, array $context = []): array
    {
        Log::info('ChatAgent: Processing message', ['message' => substr($message, 0, 100)]);

        try {
            // Product recommendation
            if ($this->looksLikeProductRecommendation($message)) {
                return $this->handleProductRecommendation($message, $context);
            }

            // Semantic search
            if ($this->looksLikeSemanticSearch($message)) {
                return $this->handleSemanticSearch($message, $context);
            }

            // General chat with enhanced context
            return $this->handleGeneralChat($message, $context);

        } catch (\Throwable $e) {
            Log::error('ChatAgent: Error processing message', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, Chat Agent gặp lỗi khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Check if message looks like product recommendation
     */
    private function looksLikeProductRecommendation(string $message): bool
    {
        return preg_match('/(gợi ý|recommend|suggest)\s*(sản phẩm|product|nước hoa)/ui', $message) ||
               preg_match('/(nước hoa|perfume)\s*(nào|gì|cho)/ui', $message) ||
               preg_match('/(tôi|mình)\s*(thích|muốn|cần)\s*(nước hoa|perfume)/ui', $message);
    }

    /**
     * Handle product recommendation with vector search
     */
    private function handleProductRecommendation(string $message, array $context): array
    {
        try {
            // Use vector store for semantic product recommendation
            $searchResults = $this->vectorEmbeddingService->searchProducts($message, 5);
            
            if (empty($searchResults)) {
                return [
                    'success' => true,
                    'type' => 'product_recommendation',
                    'products' => [],
                    'reply' => 'Tôi chưa thể tìm thấy sản phẩm phù hợp với sở thích của bạn. Bạn có thể mô tả cụ thể hơn về mùi hương hoặc phong cách bạn thích không?'
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
                    'description' => $data['content_text'],
                    'similarity' => round($result['final_score'] * 100, 1)
                ];
            })->toArray();

            // Use LLM to generate personalized recommendation
            if ($this->llmService->isConfigured()) {
                $systemPrompt = "Bạn là chuyên gia tư vấn nước hoa. Dựa trên danh sách sản phẩm và yêu cầu của khách hàng, hãy đưa ra lời khuyên cá nhân hóa.";
                
                $productContext = json_encode($products, JSON_UNESCAPED_UNICODE);
                
                $recommendation = $this->llmService->chat($message, [
                    'system' => $systemPrompt,
                    'relevant_products' => $productContext
                ]);
            } else {
                $productNames = array_column($products, 'name');
                $recommendation = "Dựa trên sở thích của bạn, tôi gợi ý các sản phẩm sau: " . implode(', ', $productNames);
            }

            return [
                'success' => true,
                'type' => 'product_recommendation',
                'products' => $products,
                'reply' => $recommendation
            ];

        } catch (\Throwable $e) {
            Log::warning('ChatAgent: Vector search failed for recommendation', ['error' => $e->getMessage()]);
            
            // Fallback to text-based recommendation
            return $this->handleTextBasedRecommendation($message, $context);
        }
    }

    /**
     * Check if message looks like semantic search
     */
    private function looksLikeSemanticSearch(string $message): bool
    {
        return preg_match('/(tìm|search|kiếm)\s*(nước hoa|perfume|sản phẩm)/ui', $message) ||
               preg_match('/(có|nào)\s*(nước hoa|perfume)\s*(nam|nữ|men|women)/ui', $message) ||
               preg_match('/(mùi|hương|fragrance)\s*(nào|gì)/ui', $message) ||
               preg_match('/nước hoa\s*(nam|nữ|men|women|quyến rũ|thơm|ngọt)/ui', $message) ||
               preg_match('/(perfume|nước hoa)\s*(cho|dành cho|phù hợp)/ui', $message);
    }

    /**
     * Handle semantic search
     */
    private function handleSemanticSearch(string $message, array $context): array
    {
        try {
            // Use vector search for semantic product search
            $searchResults = $this->vectorEmbeddingService->searchProducts($message, 8);
            
            // Get business context for LLM
            $businessContext = $this->dataService->getBusinessContext();
            $chatData = $this->dataService->getAgentSpecificContext('chat');
            
            // Format product data for LLM
            $productData = '';
            if (!empty($searchResults)) {
                $productData = "🛍️ **SẢN PHẨM TÌM THẤY:**\n\n";
                foreach ($searchResults as $i => $result) {
                    $data = $result['data'];
                    $metadata = $data['metadata'] ?? [];
                    $productName = ($metadata['brand'] ?? '') . " " . $data['content_text'];
                    
                    $productData .= "**" . ((int)$i + 1) . ". " . $productName . "**\n";
                    $productData .= "• **Giá:** " . number_format((float)($metadata['price'] ?? 0)) . "đ\n";
                    $productData .= "• **Danh mục:** " . ($metadata['category'] ?? 'N/A') . "\n";
                    $productData .= "• **Tồn kho:** " . ($metadata['stock'] ?? 0) . " sản phẩm\n";
                    $productData .= "• **Độ tương đồng:** " . round($result['final_score'] * 100, 1) . "%\n\n";
                }
            } else {
                // Fallback to DataService
                   try {
                       if (isset($businessContext['products']['products']) && !empty($businessContext['products']['products'])) {
                           $allProducts = $businessContext['products']['products'];
                           $filteredProducts = $this->filterProductsByQuery($allProducts, $message);
                           if (!empty($filteredProducts)) {
                               $productData = "🛍️ **SẢN PHẨM TÌM THẤY:**\n\n";
                               foreach (array_slice($filteredProducts, 0, 5) as $i => $product) {
                                   $productData .= "**" . ((int)$i + 1) . ". " . $product['name'] . "**\n";
                                   $productData .= "• **Thương hiệu:** " . ($product['brand'] ?? 'N/A') . "\n";
                                   $productData .= "• **Giá:** " . number_format((float)($product['price'] ?? 0)) . "đ\n";
                                   $productData .= "• **Tồn kho:** " . ($product['stock'] ?? 0) . " sản phẩm\n\n";
                               }
                           }
                       }
                   } catch (\Throwable $e) {
                       Log::warning('ChatAgent: DataService fallback failed', ['error' => $e->getMessage()]);
                   }
            }

            if (!$this->llmService->isConfigured()) {
                if (!empty($searchResults)) {
                    $productNames = array_column(array_map(fn($r) => $r['data'], $searchResults), 'content_text');
                    $reply = "Tìm thấy " . count($searchResults) . " sản phẩm: " . implode(', ', $productNames);
                } else {
                    $reply = 'Không tìm thấy sản phẩm nào phù hợp với yêu cầu của bạn.';
                }
                
                return [
                    'success' => true,
                    'type' => 'semantic_search',
                    'products' => $searchResults,
                    'reply' => $reply
                ];
            }

            $systemPrompt = "Bạn là Chat Agent chuyên nghiệp trong cửa hàng nước hoa cao cấp.

**VAI TRÒ & TRÁCH NHIỆM:**
- Chuyên gia tư vấn nước hoa và mùi hương
- Trợ lý cửa hàng nước hoa chuyên nghiệp
- Người bạn am hiểu về thế giới nước hoa

**KHẢ NĂNG CHUYÊN MÔN:**
- Tìm kiếm sản phẩm: \"nước hoa nam\", \"mùi hương nữ\", \"nước hoa sang trọng\"
- Gợi ý cá nhân: \"tôi thích mùi gì\", \"phù hợp với ai\"
- Tư vấn mùi hương: \"mùi nào phù hợp\", \"cách chọn nước hoa\"
- Thông tin sản phẩm: giá, xuất xứ, đặc điểm

**QUAN TRỌNG - FORMAT SẢN PHẨM:**
- KHÔNG sử dụng ### trong tiêu đề sản phẩm
- Sử dụng **Tên sản phẩm** thay vì ### Tên sản phẩm
- KHÔNG sử dụng ### 1. ### 2. ### 3. - chỉ dùng **1.** **2.** **3.**
- Nếu có dữ liệu sản phẩm trong product_data, HÃY SỬ DỤNG CHÍNH XÁC
- KHÔNG tự tạo danh sách sản phẩm - chỉ sử dụng dữ liệu có sẵn

**PHONG CÁCH TRẢ LỜI:**
- Thân thiện, nhiệt tình, chuyên nghiệp
- Sử dụng emoji phù hợp (💬🌸✨🛍️)
- Đưa ra lời khuyên hữu ích và chính xác
- Format dữ liệu dễ đọc với markdown
- Luôn dựa trên dữ liệu thực tế từ hệ thống

**DỮ LIỆU SẢN PHẨM:**
{$productData}";

            $reply = $this->llmService->chat($message, [
                'system' => $systemPrompt,
                'conversation_history' => $context['conversation_history'] ?? [],
                'real_data' => $this->dataService->formatBusinessContextForLLM($businessContext),
                'agent_data' => $chatData,
                'product_data' => $productData
            ]);
            
            // Remove ### from reply if present
            $reply = preg_replace('/###\s*\d+\.\s*/', '', $reply);
            $reply = str_replace('###', '', $reply); // Fallback

            return [
                'success' => true,
                'type' => 'semantic_search',
                'products' => $searchResults,
                'reply' => $reply
            ];

        } catch (\Throwable $e) {
            Log::warning('ChatAgent: Vector search failed for semantic search', ['error' => $e->getMessage()]);
            
            // Fallback to text-based search
            return $this->handleTextBasedSearch($message, $context);
        }
    }

    /**
     * Handle general chat with enhanced context and business logic
     */
    private function handleGeneralChat(string $message, array $context): array
    {
        if (!$this->llmService->isConfigured()) {
            return [
                'success' => true,
                'type' => 'general',
                'reply' => '💬 Chat Agent: Tôi có thể giúp bạn:\n• Tìm kiếm sản phẩm thông minh\n• Gợi ý nước hoa phù hợp\n• Tư vấn về mùi hương\n• Trò chuyện về nước hoa\n• Hỗ trợ các câu hỏi chung\n\nHãy hỏi tôi bất cứ điều gì!',
                'products' => []
            ];
        }

        // Try to find relevant products for context
        $relevantProducts = '';
        $productData = [];
        
        if ($this->looksLikeProductQuery($message)) {
            try {
                // First try vector search
                $vectorProducts = $this->vectorEmbeddingService->searchProducts($message, 3);
                if (!empty($vectorProducts)) {
                    $relevantProducts = $this->formatProductsForLLM($vectorProducts);
                    $productData = $vectorProducts;
                    Log::info('ChatAgent: Found relevant products via vector search', ['count' => count($vectorProducts)]);
                } else {
                    // Fallback to DataService
                    $businessContext = $this->dataService->getBusinessContext();
                    if (isset($businessContext['products']) && !empty($businessContext['products'])) {
                        $allProducts = $businessContext['products'];
                        $filteredProducts = $this->filterProductsByQuery($allProducts, $message);
                        if (!empty($filteredProducts)) {
                            $relevantProducts = $this->formatProductsFromDataService($filteredProducts);
                            $productData = $filteredProducts;
                            Log::info('ChatAgent: Found relevant products via DataService', ['count' => count($filteredProducts)]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ChatAgent: Failed to get product context', ['error' => $e->getMessage()]);
            }
        }

        // Get enhanced chat context from DataService
        $chatData = $this->dataService->getAgentSpecificContext('chat');

        $systemPrompt = "Bạn là Chat Agent - trợ lý thông minh và chuyên nghiệp chuyên về nước hoa cao cấp.

        **VAI TRÒ & TRÁCH NHIỆM:**
        - Chuyên gia tư vấn nước hoa và mùi hương
        - Trợ lý cửa hàng nước hoa chuyên nghiệp
        - Người bạn am hiểu về thế giới nước hoa
        
        **KHẢ NĂNG CHUYÊN MÔN:**
        - Tìm kiếm sản phẩm: \"nước hoa nam\", \"mùi hương nữ\"
        - Gợi ý cá nhân: \"tôi thích mùi gì\", \"phù hợp với ai\"
        - Tư vấn mùi hương: \"mùi nào phù hợp\", \"cách chọn nước hoa\"
        - Thông tin sản phẩm: giá, xuất xứ, đặc điểm
        
        **QUAN TRỌNG - ĐỊNH DẠNG SẢN PHẨM:**
        - KHÔNG sử dụng ### trong tiêu đề sản phẩm
        - Sử dụng **Tên sản phẩm** thay vì ### Tên sản phẩm
        - KHÔNG sử dụng ### 1. ### 2. ### 3. - chỉ dùng **1.** **2.** **3.**
        - Nếu có dữ liệu sản phẩm trong product_data, HÃY SỬ DỤNG CHÍNH XÁC
        - KHÔNG tự tạo danh sách sản phẩm - chỉ sử dụng dữ liệu có sẵn
        
        **PHONG CÁCH TRẢ LỜI:**
        - Thân thiện, nhiệt tình, chuyên nghiệp
        - Sử dụng emoji phù hợp (💬🌸✨🛍️)
        - Đưa ra lời khuyên hữu ích và chính xác
        - Tạo cảm giác tin cậy và gần gũi
        - Format thông tin dễ đọc với markdown
        - Luôn dựa trên dữ liệu thực tế từ hệ thống";

        $reply = $this->llmService->chat($message, [
            'system' => $systemPrompt,
            'relevant_products' => $relevantProducts,
            'conversation_history' => $context['conversation_history'] ?? [],
            'real_data' => $this->dataService->formatBusinessContextForLLM($this->dataService->getBusinessContext()),
            'agent_data' => $chatData
        ]);
        
        // Remove ### from reply if present
        $reply = preg_replace('/###\s*\d+\.\s*/', '', $reply);
        $reply = str_replace('###', '', $reply); // Fallback

        return [
            'success' => true,
            'type' => 'llm',
            'reply' => $reply,
            'products' => [] // Ensure products key exists for frontend compatibility
        ];
    }

    /**
     * Get store context for LLM
     */
    private function getStoreContext(): string
    {
        try {
            $totalProducts = Product::count();
            $categories = \App\Models\Category::count();
            $avgPrice = Product::avg('price');
            
            return "Cửa hàng có {$totalProducts} sản phẩm nước hoa thuộc {$categories} danh mục. Giá trung bình: " . number_format($avgPrice) . "đ";
        } catch (\Throwable $e) {
            return "Cửa hàng nước hoa chuyên nghiệp với nhiều sản phẩm chất lượng";
        }
    }

    /**
     * Check if message looks like product query
     */
    private function looksLikeProductQuery(string $message): bool
    {
        $productKeywords = ['nước hoa', 'perfume', 'mùi hương', 'fragrance', 'sản phẩm', 'product'];
        
        foreach ($productKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Format products for LLM context
     */
    private function formatProductsForLLM(array $products): string
    {
        $formatted = "Thông tin sản phẩm liên quan:\n";
        
        foreach ($products as $product) {
            $name = $product['name'] ?? 'Unknown';
            $category = $product['category'] ?? 'N/A';
            $price = $product['price'] ?? 0;
            $description = $product['description'] ?? '';
            
            $formatted .= "- {$name} ({$category}): " . number_format($price) . "đ";
            if (!empty($description)) {
                $formatted .= " - {$description}";
            }
            $formatted .= "\n";
        }
        
        return $formatted;
    }

    /**
     * Handle text-based recommendation (fallback)
     */
    private function handleTextBasedRecommendation(string $message, array $context): array
    {
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
                    'description' => $product->description ?? '',
                    'similarity' => 0
                ];
            })
            ->toArray();

        if (empty($products)) {
            return [
                'success' => true,
                'type' => 'product_recommendation',
                'products' => [],
                'reply' => 'Tôi chưa thể tìm thấy sản phẩm phù hợp. Bạn có thể mô tả cụ thể hơn về sở thích của mình không?'
            ];
        }

        $productNames = array_column($products, 'name');
        $reply = "Dựa trên yêu cầu của bạn, tôi gợi ý: " . implode(', ', $productNames);

        return [
            'success' => true,
            'type' => 'product_recommendation',
            'products' => $products,
            'reply' => $reply
        ];
    }

    /**
     * Handle text-based search (fallback)
     */
    private function handleTextBasedSearch(string $message, array $context): array
    {
        $products = Product::where('name', 'LIKE', "%{$message}%")
            ->orWhere('description', 'LIKE', "%{$message}%")
            ->limit(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => number_format($product->price),
                    'category' => $product->category->name ?? 'N/A',
                    'description' => $product->description ?? '',
                    'similarity' => 0
                ];
            })
            ->toArray();

        if (empty($products)) {
            return [
                'success' => true,
                'type' => 'semantic_search',
                'products' => [],
                'reply' => 'Không tìm thấy sản phẩm nào phù hợp với yêu cầu của bạn.'
            ];
        }

        $productNames = array_column($products, 'name');
        $reply = "Tìm thấy " . count($products) . " sản phẩm: " . implode(', ', $productNames);

        return [
            'success' => true,
            'type' => 'semantic_search',
            'products' => $products,
            'reply' => $reply
        ];
    }

    /**
     * Filter products by query from DataService
     */
    private function filterProductsByQuery(array $products, string $query): array
    {
        $queryLower = strtolower($query);
        $keywords = ['nam', 'nữ', 'men', 'women', 'nam giới', 'nữ giới', 'unisex'];
        
        $filtered = [];
        foreach ($products as $product) {
            $productText = strtolower($product['name'] . ' ' . $product['brand'] . ' ' . ($product['description'] ?? ''));
            
            // Check if query matches product
            if (strpos($productText, $queryLower) !== false) {
                $filtered[] = $product;
            } else {
                // Check for gender keywords
                foreach ($keywords as $keyword) {
                    if (strpos($queryLower, $keyword) !== false && strpos($productText, $keyword) !== false) {
                        $filtered[] = $product;
                        break;
                    }
                }
            }
        }
        
        return array_slice($filtered, 0, 5); // Limit to 5 products
    }

    /**
     * Format products from DataService for LLM
     */
    private function formatProductsFromDataService(array $products): string
    {
        if (empty($products)) {
            return '';
        }

        $formatted = "🛍️ **SẢN PHẨM LIÊN QUAN:**\n\n";
        
        foreach ($products as $i => $product) {
            $formatted .= "**" . ((int)$i + 1) . ". " . $product['name'] . "**\n";
            $formatted .= "• **Thương hiệu:** " . ($product['brand'] ?? 'N/A') . "\n";
            $formatted .= "• **Giá:** " . number_format((float)($product['price'] ?? 0)) . "đ\n";
            $formatted .= "• **Tồn kho:** " . ($product['stock'] ?? 0) . " sản phẩm\n";
            $formatted .= "• **Danh mục:** " . ($product['category'] ?? 'N/A') . "\n";
            
            if (!empty($product['description'])) {
                $formatted .= "• **Mô tả:** " . substr($product['description'], 0, 100) . "...\n";
            }
            
            $formatted .= "\n";
        }

        return $formatted;
    }
}
