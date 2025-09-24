<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function chat(string $message, array $context = []): string
    {
        if (!$this->isConfigured()) {
            return 'LLM chưa được cấu hình. Vui lòng thiết lập OPENAI_API_KEY.';
        }

        $system = $context['system'] ?? $this->getDefaultSystemPrompt();
        
        // Thêm context về sản phẩm nếu có
        if (isset($context['relevant_products']) && !empty($context['relevant_products'])) {
            $system .= "\n\nThông tin sản phẩm liên quan:\n" . $context['relevant_products'];
        }
        
        // Thêm dữ liệu thực tế từ database nếu có
        if (isset($context['real_data']) && !empty($context['real_data'])) {
            $system .= "\n\nDữ liệu thực tế từ hệ thống:\n" . $context['real_data'];
            \Log::info('LLM Real Data Added', [
                'data_length' => strlen($context['real_data']),
                'data_preview' => substr($context['real_data'], 0, 200)
            ]);
        } else {
            \Log::info('LLM No Real Data', ['context_keys' => array_keys($context)]);
        }

        $messages = [
            ['role' => 'system', 'content' => mb_convert_encoding($system, 'UTF-8', 'auto')],
        ];

        // Thêm conversation history nếu có
        if (isset($context['conversation_history']) && is_array($context['conversation_history'])) {
            foreach ($context['conversation_history'] as $history) {
                if (isset($history['role']) && isset($history['content'])) {
                    $messages[] = [
                        'role' => $history['role'],
                        'content' => mb_convert_encoding($history['content'], 'UTF-8', 'auto')
                    ];
                }
            }
        }

        // Thêm message hiện tại
        $messages[] = ['role' => 'user', 'content' => mb_convert_encoding($message, 'UTF-8', 'auto')];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 500,
        ];

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
     * Lấy system prompt mặc định cho OmniAI - Trợ lý nội bộ
     */
    private function getDefaultSystemPrompt(): string
    {
        return "Bạn là OmniAI - trợ lý AI nội bộ cho cửa hàng nước hoa PerfumeShop.

🎯 NHIỆM VỤ CHÍNH:
Bạn là trợ lý AI chuyên nghiệp, chỉ trả lời dựa trên DỮ LIỆU THỰC TẾ từ hệ thống.

📋 QUY TẮC NGHIÊM NGẶT:
1. **CHỈ sử dụng dữ liệu được cung cấp** trong phần 'Dữ liệu thực tế từ hệ thống'
2. **KHÔNG được bịa đặt** thông tin không có trong dữ liệu
3. **Nếu có dữ liệu thống kê**, hãy trả lời với số liệu cụ thể
4. **Luôn đưa ra số liệu cụ thể** khi có dữ liệu
5. **Trả lời ngắn gọn, chính xác** - không dài dòng
6. **Context-aware** với cuộc trò chuyện trước đó

🔍 CÁCH TRẢ LỜI:
- Có dữ liệu: Đưa ra thông tin chính xác với số liệu cụ thể
- Không có dữ liệu: 'Tôi không có thông tin này trong hệ thống'
- Cần thêm thông tin: 'Bạn có thể cung cấp thêm thông tin cụ thể không?'

⚠️ LƯU Ý QUAN TRỌNG:
- KHÔNG được trả lời 'linh tinh' hoặc bịa đặt
- KHÔNG được đưa ra thông tin không có trong dữ liệu
- CHỈ trả lời dựa trên dữ liệu thực tế được cung cấp
- Nếu có dữ liệu thống kê, hãy trả lời với số liệu cụ thể

Hãy luôn chính xác và chuyên nghiệp!";
    }
}


