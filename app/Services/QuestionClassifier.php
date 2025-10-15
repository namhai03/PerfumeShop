<?php

namespace App\Services;

use App\Services\LLMService;
use Illuminate\Support\Facades\Log;

class QuestionClassifier
{
    private LLMService $llmService;
    
    // Intent patterns for classification
    private array $intentPatterns = [
        'order_lookup' => [
            'patterns' => [
                '/(đơn|order)\s*(số|number)?\s*#?([A-Za-z0-9\-]+)/ui',
                '/(tra cứu|lookup|check)\s*(đơn|order)/ui',
                '/(trạng thái|status)\s*(đơn|order)/ui'
            ],
            'keywords' => ['đơn hàng', 'order', 'mã đơn', 'order number', 'trạng thái đơn'],
            'confidence_weight' => 0.9
        ],
        
        'customer_lookup' => [
            'patterns' => [
                '/(sđt|sdt|phone|điện\s*thoại)\s*(:|là)?\s*(\+?\d[\d\s\-]{6,})/ui',
                '/(khách hàng|customer)\s*(sđt|phone)/ui',
                '/(lịch sử|history)\s*(mua hàng|purchase)/ui',
                '/(tìm|tra|kiểm\s*tra|thông\s*tin)\s*(khách\s*hàng|customer)/ui',
                '/(khách\s*hàng|customer)\s*(nào|gì|đó)/ui',
                '/(\b[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+\s+[A-ZÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ][a-zàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]+)\s*(đã|mua|bao\s*nhiêu)/ui'
            ],
            'keywords' => ['khách hàng', 'customer', 'sđt', 'phone', 'lịch sử mua', 'tìm khách hàng', 'thông tin khách hàng'],
            'confidence_weight' => 0.9
        ],
        
        'daily_orders' => [
            'patterns' => [
                '/(hôm nay|today)\s*(có|co)\s*(bao nhiêu|bao nhieu)\s*(đơn|don)/ui',
                '/(hôm qua|yesterday)\s*(có|co)\s*(bao nhiêu|bao nhieu)\s*(đơn|don)/ui',
                '/(hôm kia|day before yesterday)\s*(có|co)\s*(bao nhiêu|bao nhieu)\s*(đơn|don)/ui',
                '/(tháng này|this month)\s*(có|co)\s*(bao nhiêu|bao nhieu)\s*(đơn|don)/ui',
                '/(đơn|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
                '/(số|so)\s*(đơn|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
                '/(thống kê|statistics)\s*(đơn|don)\s*(hôm nay|today|hôm qua|yesterday|hôm kia|tháng này)/ui',
                '/(ngày|date)\s*(\d{1,2}\/\d{1,2})\s*(có|co)\s*(bao nhiêu|bao nhieu)\s*(đơn|don)/ui',
                '/(đơn\s*hàng|orders?)\s*(hôm qua|yesterday|hôm nay|today)/ui'
            ],
            'keywords' => ['hôm nay', 'today', 'hôm qua', 'yesterday', 'hôm kia', 'tháng này', 'this month', 'đơn hàng', 'orders', 'thống kê', 'ngày'],
            'confidence_weight' => 0.95
        ],
        
        'sales_analysis' => [
            'patterns' => [
                '/(phân tích|analysis|xu hướng|trend)\s*(bán hàng|sales)/ui',
                '/(so sánh|compare)\s*(doanh số|revenue)/ui',
                '/(hiệu suất|performance)\s*(bán hàng|sales)/ui'
            ],
            'keywords' => ['phân tích', 'analysis', 'xu hướng', 'trend', 'bán hàng', 'sales'],
            'confidence_weight' => 0.85
        ],
        
        'inventory_check' => [
            'patterns' => [
                '/(tồn|stock)\s*(thấp|low|hết)/ui',
                '/(kiểm tra|check)\s*(tồn|stock)/ui',
                '/(sản phẩm|product)\s*(hết hàng|out of stock)/ui'
            ],
            'keywords' => ['tồn kho', 'stock', 'hết hàng', 'out of stock', 'kiểm tra tồn'],
            'confidence_weight' => 0.9
        ],
        
        'product_search' => [
            'patterns' => [
                '/(tìm|search|gợi ý)\s*(sản phẩm|product|nước hoa)/ui',
                '/(nước hoa|perfume)\s*(nam|nữ|men|women)/ui',
                '/(mùi|hương|fragrance)\s*(nào|gì)/ui'
            ],
            'keywords' => ['tìm sản phẩm', 'product search', 'nước hoa', 'perfume', 'mùi hương'],
            'confidence_weight' => 0.8
        ],
        
        'promotion_management' => [
            'patterns' => [
                '/(ctkm|khuyến mãi|promotion)\s*(đang chạy|running)/ui',
                '/(mô phỏng|simulate)\s*(khuyến mãi|promotion)/ui',
                '/(tạo|create)\s*(chương trình|program)\s*(khuyến mãi|promotion)/ui'
            ],
            'keywords' => ['khuyến mãi', 'promotion', 'ctkm', 'mô phỏng', 'chương trình'],
            'confidence_weight' => 0.85
        ],
        
        'report_generation' => [
            'patterns' => [
                '/(báo cáo|report)\s*(doanh thu|revenue|kpi)/ui',
                '/(xuất|export)\s*(báo cáo|report)/ui',
                '/(kpi|tổng quan|chỉ số)\s*(hôm nay|tuần|tháng)/ui'
            ],
            'keywords' => ['báo cáo', 'report', 'kpi', 'doanh thu', 'revenue', 'xuất báo cáo'],
            'confidence_weight' => 0.9
        ],
        
        'general_chat' => [
            'patterns' => [
                '/(^|\s)(xin chào|hello|hi)(\s|$)/ui',
                '/(bạn có thể|có thể)\s*(làm gì|help)/ui',
                '/(cảm ơn|thank you|thanks)/ui'
            ],
            'keywords' => ['xin chào', 'hello', 'hi', 'cảm ơn', 'thank you'],
            'confidence_weight' => 0.7
        ]
    ];

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }

    /**
     * Classify question using pattern matching first, then LLM as fallback
     */
    public function classify(string $message, array $context = []): array
    {
        Log::info('QuestionClassifier: Classifying message', [
            'message' => substr($message, 0, 100),
            'context_keys' => array_keys($context)
        ]);

        // Always use LLM as primary method for better accuracy
        $llmResult = $this->classifyByLLM($message, $context);
        
        // Only use pattern matching if LLM fails completely
        if ($llmResult['method'] === 'llm_unavailable' || $llmResult['confidence'] < 0.3) {
            $patternResult = $this->classifyByPatterns($message);
            
            if ($patternResult['confidence'] >= 0.5) {
                Log::info('QuestionClassifier: Using pattern matching as fallback', [
                    'primary_intent' => $patternResult['primary'],
                    'confidence' => $patternResult['confidence'],
                    'method' => $patternResult['method']
                ]);
                return $patternResult;
            }
        }

        Log::info('QuestionClassifier: Classification result', [
            'primary_intent' => $llmResult['primary'],
            'confidence' => $llmResult['confidence'],
            'method' => $llmResult['method']
        ]);

        return $llmResult;
    }

    /**
     * Pattern-based classification
     */
    private function classifyByPatterns(string $message): array
    {
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($this->intentPatterns as $intent => $config) {
            $score = 0;
            
            // Check patterns
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $message)) {
                    $score += $config['confidence_weight'];
                    break; // Only need one pattern match
                }
            }
            
            // Check keywords
            foreach ($config['keywords'] as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    $score += 0.3; // Lower weight for keyword matches
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $intent;
            }
        }
        
        if ($bestMatch && $bestScore >= 0.5) {
            return [
                'primary' => $bestMatch,
                'confidence' => min($bestScore, 1.0),
                'method' => 'pattern_matching',
                'reasoning' => "Matched pattern for {$bestMatch} with score {$bestScore}"
            ];
        }
        
        return [
            'primary' => 'general_chat',
            'confidence' => 0.3,
            'method' => 'pattern_fallback',
            'reasoning' => 'No strong pattern match found'
        ];
    }

    /**
     * LLM-based classification as fallback method
     */
    private function classifyByLLM(string $message, array $context): array
    {
        if (!$this->llmService->isConfigured()) {
            return [
                'primary' => 'general_chat',
                'confidence' => 0.5,
                'method' => 'llm_unavailable',
                'reasoning' => 'LLM not configured, defaulting to general chat'
            ];
        }

        try {
            $systemPrompt = "Bạn là chuyên gia phân tích intent cho hệ thống AI của cửa hàng nước hoa PerfumeShop.

**NHIỆM VỤ:** Phân tích câu hỏi tiếng Việt và xác định intent chính xác nhất dựa trên ngữ cảnh và ý định thực sự của người dùng.

**CÁC INTENT CÓ THỂ:**
1. **customer_lookup** - Tra cứu khách hàng: \"khách hàng Nguyễn Xuân Sơn\", \"Nguyễn Xuân Sơn đã mua bao nhiêu đơn\", \"thông tin khách hàng\", \"lịch sử mua hàng của khách hàng\"
2. **daily_orders** - Thống kê đơn hàng theo ngày: \"hôm qua có bao nhiêu đơn\", \"đơn hàng hôm qua\", \"số đơn hôm nay\", \"doanh thu hôm qua\"
3. **order_lookup** - Tra cứu đơn hàng cụ thể: \"đơn số ABC123\", \"trạng thái đơn hàng DH20251009lySW\", \"kiểm tra đơn hàng\"
4. **product_search** - Tìm kiếm sản phẩm: \"nước hoa nữ quyến rũ\", \"nước hoa nam\", \"mùi hương nữ\", \"gợi ý sản phẩm\", \"tìm nước hoa\"
5. **inventory_check** - Kiểm tra tồn kho: \"tồn thấp\", \"sản phẩm hết hàng\", \"kiểm tra kho\", \"còn bao nhiêu\"
6. **sales_analysis** - Phân tích bán hàng: \"xu hướng bán hàng\", \"so sánh doanh số\", \"hiệu suất sales\", \"phân tích kinh doanh\"
7. **report_generation** - Tạo báo cáo: \"báo cáo doanh thu\", \"xuất báo cáo\", \"kpi\", \"dashboard\", \"báo cáo tháng\"
8. **promotion_management** - Quản lý khuyến mãi: \"CTKM đang chạy\", \"mô phỏng khuyến mãi\", \"tạo chương trình\", \"khuyến mãi\"
9. **general_chat** - Trò chuyện chung: \"xin chào\", \"bạn có thể làm gì\", \"cảm ơn\", \"giúp đỡ\"

**QUY TẮC PHÂN TÍCH QUAN TRỌNG:**
- **customer_lookup**: Khi có TÊN KHÁCH HÀNG + \"đã mua\", \"mua bao nhiêu\", \"lịch sử\", \"thông tin\"
- **daily_orders**: Khi có \"hôm qua\", \"hôm nay\", \"đơn hàng\" + \"bao nhiêu\", \"có bao nhiêu\"
- **product_search**: Khi có \"nước hoa\", \"sản phẩm\", \"tìm\", \"gợi ý\" + mô tả sản phẩm
- **order_lookup**: Khi có MÃ ĐƠN HÀNG cụ thể hoặc \"đơn hàng\" + mã số
- Phân tích ngữ cảnh và ý định thực sự của người dùng
- Không chỉ dựa vào từ khóa mà cần hiểu ý nghĩa
- Ưu tiên intent cụ thể hơn intent chung
- Trả về JSON với format: {\"primary\": \"intent_name\", \"confidence\": 0.0-1.0, \"reasoning\": \"lý do chi tiết\"}
- Confidence từ 0.8 trở lên cho intent rõ ràng, 0.6-0.8 cho intent có thể, dưới 0.6 cho general_chat

**VÍ DỤ PHÂN TÍCH CHÍNH XÁC:**
- \"Nguyễn Xuân Sơn đã mua bao nhiêu đơn hàng\" → {\"primary\": \"customer_lookup\", \"confidence\": 0.95, \"reasoning\": \"Có tên khách hàng cụ thể và hỏi về số đơn hàng đã mua\"}
- \"hôm qua có bao nhiêu đơn hàng\" → {\"primary\": \"daily_orders\", \"confidence\": 0.9, \"reasoning\": \"Hỏi về thống kê đơn hàng của ngày hôm qua\"}
- \"đơn hàng hôm qua\" → {\"primary\": \"daily_orders\", \"confidence\": 0.85, \"reasoning\": \"Hỏi về đơn hàng của ngày hôm qua, không phải đơn hàng cụ thể\"}
- \"nước hoa nữ quyến rũ\" → {\"primary\": \"product_search\", \"confidence\": 0.9, \"reasoning\": \"Tìm kiếm sản phẩm nước hoa với mô tả cụ thể\"}
- \"đơn số DH20251009lySW\" → {\"primary\": \"order_lookup\", \"confidence\": 0.95, \"reasoning\": \"Tra cứu đơn hàng cụ thể với mã số\"}
- \"xin chào\" → {\"primary\": \"general_chat\", \"confidence\": 0.8, \"reasoning\": \"Lời chào thông thường\"}";

            $response = $this->llmService->chat($message, [
                'system' => $systemPrompt,
                'max_tokens' => 300,
                'temperature' => 0.1 // Low temperature for consistent classification
            ]);

            // Parse JSON response
            $result = json_decode($response, true);
            
            if (is_array($result) && isset($result['primary'])) {
                return [
                    'primary' => $result['primary'],
                    'confidence' => $result['confidence'] ?? 0.7,
                    'method' => 'llm',
                    'reasoning' => $result['reasoning'] ?? 'LLM classification'
                ];
            }
            
        } catch (\Throwable $e) {
            Log::warning('QuestionClassifier: LLM classification failed', [
                'error' => $e->getMessage(),
                'message' => substr($message, 0, 50)
            ]);
        }

        return [
            'primary' => 'general_chat',
            'confidence' => 0.5,
            'method' => 'llm_fallback',
            'reasoning' => 'LLM classification failed, defaulting to general chat'
        ];
    }


    /**
     * Get agent mapping for intent
     */
    public function getAgentForIntent(string $intent): string
    {
        $mapping = [
            'order_lookup' => 'sales',
            'customer_lookup' => 'sales',
            'daily_orders' => 'sales',
            'sales_analysis' => 'sales',
            'promotion_management' => 'sales',
            'inventory_check' => 'inventory',
            'product_search' => 'chat', // Route product search to ChatAgent
            'report_generation' => 'report',
            'general_chat' => 'chat'
        ];

        return $mapping[$intent] ?? 'chat';
    }

    /**
     * Get confidence threshold for agent routing
     */
    public function getConfidenceThreshold(): float
    {
        return 0.6; // Lower threshold since we're using LLM as primary method
    }

    /**
     * Check if classification is confident enough for specific agent routing
     */
    public function isConfidentEnough(array $classification): bool
    {
        return $classification['confidence'] >= $this->getConfidenceThreshold();
    }

    /**
     * Get classification statistics for monitoring
     */
    public function getClassificationStats(): array
    {
        return [
            'total_intents' => count($this->intentPatterns),
            'confidence_threshold' => $this->getConfidenceThreshold(),
            'supported_agents' => ['sales', 'inventory', 'report', 'chat'],
            'methods' => ['pattern_matching', 'llm', 'hybrid']
        ];
    }
}
