<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = (string) config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Generate embedding for a given text.
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        if (empty(trim($text))) {
            return [];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/embeddings', [
                    'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
                    'input' => mb_convert_encoding($text, 'UTF-8', 'auto'),
                    'encoding_format' => 'float'
                ]);

            if (!$response->successful()) {
                \Log::error('OpenAI Embedding API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? [];

        } catch (\Exception $e) {
            \Log::error('LLM Embedding Error', [
                'message' => $e->getMessage(),
                'text_length' => strlen($text)
            ]);
            return [];
        }
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Enhanced chat with optimized context handling
     */
    public function chat(string $message, array $context = []): string
    {
        if (!$this->isConfigured()) {
            return 'LLM chưa được cấu hình. Vui lòng thiết lập OPENAI_API_KEY.';
        }

        // Use custom system prompt if provided, otherwise use enhanced system prompt
        if (isset($context['system']) && !empty($context['system'])) {
            $system = $context['system'];
        } else {
            $system = $this->getEnhancedSystemPrompt($context);
        }
        
        // Build messages array with optimized context
        $messages = [
            ['role' => 'system', 'content' => mb_convert_encoding($system, 'UTF-8', 'auto')],
        ];

        // Add conversation history if available (limit to last 10 messages for efficiency)
        if (isset($context['conversation_history']) && is_array($context['conversation_history'])) {
            $recentHistory = array_slice($context['conversation_history'], -10);
            foreach ($recentHistory as $history) {
                if (isset($history['role']) && isset($history['content'])) {
                    $messages[] = [
                        'role' => $history['role'],
                        'content' => mb_convert_encoding($history['content'], 'UTF-8', 'auto')
                    ];
                }
            }
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => mb_convert_encoding($message, 'UTF-8', 'auto')];

        // Optimize payload based on context
        $payload = $this->buildOptimizedPayload($messages, $context);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', $payload);

            if (!$response->successful()) {
                $errorBody = $response->body();
                \Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                return 'Xin lỗi, tôi không thể xử lý câu hỏi này lúc này. Vui lòng thử lại sau.';
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';
            return trim((string) $text);

        } catch (\Exception $e) {
            \Log::error('LLM Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Có lỗi xảy ra khi xử lý câu hỏi. Vui lòng thử lại sau.';
        }
    }

    /**
     * Generate a marketing image via OpenAI Images API (base64 or URL depending on provider)
     * Returns a direct URL when possible, otherwise a data URL
     */
    public function generateImage(string $prompt, int $width = 1024, int $height = 1024): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $size = $width . 'x' . $height;

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post(rtrim($this->baseUrl, '/') . '/images/generations', [
                    'model' => 'gpt-image-1',
                    'prompt' => mb_convert_encoding($prompt, 'UTF-8', 'auto'),
                    'size' => $size,
                ]);

            if (!$response->successful()) {
                \Log::error('OpenAI Image API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            // Prefer URL if available
            $url = $data['data'][0]['url'] ?? null;
            if ($url) return $url;
            $b64 = $data['data'][0]['b64_json'] ?? null;
            if ($b64) return 'data:image/png;base64,' . $b64;
            return null;
        } catch (\Exception $e) {
            \Log::error('LLM Image Service Error', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Lấy system prompt mặc định cho OmniAI - Trợ lý nội bộ
     */
    private function getDefaultSystemPrompt(): string
    {
        return "Bạn là OmniAI - trợ lý AI nội bộ chuyên nghiệp cho cửa hàng nước hoa PerfumeShop.

🎯 **VAI TRÒ & TRÁCH NHIỆM:**
- Trợ lý AI thông minh chuyên về quản lý cửa hàng nước hoa
- Chuyên gia phân tích dữ liệu kinh doanh và đưa ra insights
- Cố vấn chiến lược dựa trên dữ liệu thực tế từ hệ thống

📊 **NGUYÊN TẮC LÀM VIỆC:**
1. **DỮ LIỆU LÀ CHÂN LÝ**: Chỉ trả lời dựa trên dữ liệu thực tế được cung cấp
2. **CHÍNH XÁC TUYỆT ĐỐI**: Không bịa đặt, không suy đoán không có căn cứ
3. **SỐ LIỆU CỤ THỂ**: Luôn đưa ra con số chính xác khi có dữ liệu
4. **CONTEXT-AWARE**: Hiểu và sử dụng ngữ cảnh cuộc trò chuyện
5. **ACTIONABLE**: Đưa ra gợi ý hành động cụ thể và khả thi

🔍 **CÁCH TRẢ LỜI:**
- **Có dữ liệu**: Trả lời với số liệu cụ thể và phân tích sâu sắc
- **Thiếu dữ liệu**: \"Tôi không có thông tin này trong hệ thống hiện tại\"
- **Cần làm rõ**: \"Bạn có thể cung cấp thêm thông tin cụ thể không?\"
- **Phân tích**: Đưa ra insights và khuyến nghị dựa trên dữ liệu

💡 **PHONG CÁCH TRẢ LỜI:**
- Chuyên nghiệp, chính xác, có cấu trúc rõ ràng
- Sử dụng emoji phù hợp để tăng tính trực quan
- Format dữ liệu dễ đọc với markdown
- Đưa ra khuyến nghị hành động cụ thể
- Luôn có căn cứ từ dữ liệu thực tế

⚠️ **QUY TẮC NGHIÊM NGẶT:**
- KHÔNG được bịa đặt thông tin
- KHÔNG được đưa ra số liệu không có trong dữ liệu
- KHÔNG được trả lời \"linh tinh\" hoặc không có căn cứ
- CHỈ sử dụng dữ liệu được cung cấp trong context

Hãy luôn chính xác, chuyên nghiệp và hữu ích!";
    }

    /**
     * Build optimized payload based on context
     */
    private function buildOptimizedPayload(array $messages, array $context): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 800,
        ];

        // Adjust parameters based on context
        if (isset($context['classification'])) {
            $classification = $context['classification'];
            
            // Adjust temperature based on task type
            if (in_array($classification['primary'], ['order_lookup', 'customer_lookup', 'inventory_check'])) {
                $payload['temperature'] = 0.1; // Lower temperature for factual queries
                $payload['max_tokens'] = 600; // Shorter responses for lookups
            } elseif (in_array($classification['primary'], ['sales_analysis', 'report_generation'])) {
                $payload['temperature'] = 0.2; // Slightly higher for analysis
                $payload['max_tokens'] = 1000; // Longer responses for analysis
            } elseif ($classification['primary'] === 'general_chat') {
                $payload['temperature'] = 0.4; // Higher creativity for general chat
                $payload['max_tokens'] = 800;
            }
        }

        // Add product context if available
        if (isset($context['relevant_products']) && !empty($context['relevant_products'])) {
            // Add product context to the last user message
            $lastMessageIndex = count($payload['messages']) - 1;
            $payload['messages'][$lastMessageIndex]['content'] .= "\n\n🛍️ **THÔNG TIN SẢN PHẨM LIÊN QUAN:**\n" . $context['relevant_products'];
        }

        return $payload;
    }

    /**
     * Get enhanced system prompt with real data context
     */
    public function getEnhancedSystemPrompt(array $context = []): string
    {
        $basePrompt = $this->getDefaultSystemPrompt();
        
        // Add real data context if available
        if (isset($context['real_data']) && !empty($context['real_data'])) {
            $basePrompt .= "\n\n📊 **DỮ LIỆU THỰC TẾ TỪ HỆ THỐNG:**\n" . $context['real_data'];
        }
        
        // Add agent-specific context if available
        if (isset($context['agent_data']) && !empty($context['agent_data'])) {
            $basePrompt .= "\n\n🎯 **DỮ LIỆU CHUYÊN MÔN:**\n";
            foreach ($context['agent_data'] as $key => $data) {
                if (is_array($data)) {
                    $basePrompt .= "• {$key}: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
                } else {
                    $basePrompt .= "• {$key}: {$data}\n";
                }
            }
        }
        
        // Add product data if available
        if (isset($context['product_data']) && !empty($context['product_data'])) {
            $basePrompt .= "\n\n📦 **DỮ LIỆU SẢN PHẨM CHI TIẾT:**\n" . $context['product_data'];
        }

        // Add classification context if available
        if (isset($context['classification'])) {
            $classification = $context['classification'];
            $basePrompt .= "\n\n🎯 **PHÂN TÍCH CÂU HỎI:**\n";
            $basePrompt .= "• Intent: {$classification['primary']}\n";
            $basePrompt .= "• Confidence: " . number_format($classification['confidence'] * 100, 1) . "%\n";
            $basePrompt .= "• Method: {$classification['method']}\n";
            if (isset($classification['reasoning'])) {
                $basePrompt .= "• Reasoning: {$classification['reasoning']}\n";
            }
        }
        
        // Add customer data if available
        if (isset($context['customer_data']) && !empty($context['customer_data'])) {
            $basePrompt .= "\n\n👥 **DỮ LIỆU KHÁCH HÀNG:**\n" . $context['customer_data'];
        }
        
        return $basePrompt;
    }
}


