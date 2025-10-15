<?php

namespace App\Services;

use App\Services\SalesAgent;
use App\Services\InventoryAgent;
use App\Services\ReportAgent;
use App\Services\ChatAgent;
use App\Services\LLMService;
use App\Services\VectorSearchService;
use App\Services\VectorEmbeddingService;
use App\Services\DataService;
use App\Services\QuestionClassifier;
use Illuminate\Support\Facades\Log;

class AICoordinator
{
    private SalesAgent $salesAgent;
    private InventoryAgent $inventoryAgent;
    private ReportAgent $reportAgent;
    private ChatAgent $chatAgent;
    private LLMService $llmService;
    private VectorSearchService $vectorSearchService;
    private VectorEmbeddingService $vectorEmbeddingService;
    private DataService $dataService;
    private QuestionClassifier $questionClassifier;

    public function __construct(
        SalesAgent $salesAgent,
        InventoryAgent $inventoryAgent,
        ReportAgent $reportAgent,
        ChatAgent $chatAgent,
        LLMService $llmService,
        VectorSearchService $vectorSearchService,
        VectorEmbeddingService $vectorEmbeddingService,
        DataService $dataService,
        QuestionClassifier $questionClassifier
    ) {
        $this->salesAgent = $salesAgent;
        $this->inventoryAgent = $inventoryAgent;
        $this->reportAgent = $reportAgent;
        $this->chatAgent = $chatAgent;
        $this->llmService = $llmService;
        $this->vectorSearchService = $vectorSearchService;
        $this->vectorEmbeddingService = $vectorEmbeddingService;
        $this->dataService = $dataService;
        $this->questionClassifier = $questionClassifier;
    }

    /**
     * Route message to appropriate agent based on intelligent classification
     */
    public function processMessage(string $message, string $selectedAgent = 'omni', array $context = []): array
    {
        Log::info('AICoordinator: Processing message', [
            'message' => substr($message, 0, 100),
            'selectedAgent' => $selectedAgent,
            'context_keys' => array_keys($context)
        ]);

        try {
            // If specific agent is selected, route directly with enhanced context
            if ($selectedAgent !== 'omni') {
                $enhancedContext = $this->enhanceContextWithRealData($selectedAgent, $context);
                return $this->routeToSpecificAgent($selectedAgent, $message, $enhancedContext);
            }

            // For OmniAI, use intelligent question classification
            $classification = $this->questionClassifier->classify($message, $context);
            Log::info('AICoordinator: Question classification result', [
                'primary_intent' => $classification['primary'],
                'confidence' => $classification['confidence'],
                'method' => $classification['method']
            ]);

            // Determine target agent based on classification
            $targetAgent = $this->questionClassifier->getAgentForIntent($classification['primary']);
            
            // Check if classification is confident enough
            if ($this->questionClassifier->isConfidentEnough($classification)) {
                Log::info('AICoordinator: High confidence classification, routing to specific agent', [
                    'target_agent' => $targetAgent,
                    'confidence' => $classification['confidence']
                ]);
                
                $enhancedContext = $this->enhanceContextWithRealData($targetAgent, $context);
                $enhancedContext['classification'] = $classification;
                
                return $this->routeToSpecificAgent($targetAgent, $message, $enhancedContext);
            } else {
                Log::info('AICoordinator: Low confidence classification, using general chat', [
                    'confidence' => $classification['confidence'],
                    'threshold' => $this->questionClassifier->getConfidenceThreshold()
                ]);
                
                // For low confidence, use chat agent with classification context
                $enhancedContext = $this->enhanceContextWithRealData('chat', $context);
                $enhancedContext['classification'] = $classification;
                
                return $this->chatAgent->process($message, $enhancedContext);
            }

        } catch (\Throwable $e) {
            Log::error('AICoordinator: Error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, đã có lỗi xảy ra khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ];
        }
    }

    /**
     * Route to specific agent
     */
    private function routeToSpecificAgent(string $agentType, string $message, array $context): array
    {
        $result = [];
        
        switch ($agentType) {
            case 'sales':
                $result = $this->salesAgent->process($message, $context);
                break;
            
            case 'inventory':
                $result = $this->inventoryAgent->process($message, $context);
                break;
            
            case 'report':
                $result = $this->reportAgent->process($message, $context);
                break;
            
            case 'chat':
                $result = $this->chatAgent->process($message, $context);
                break;
            
            default:
                $result = $this->chatAgent->process($message, $context);
                break;
        }
        
        // Add agent information to response
        $result['agent'] = $agentType;
        $result['agent_name'] = $this->getAgentDisplayName($agentType);
        
        return $result;
    }
    
    /**
     * Get display name for agent
     */
    private function getAgentDisplayName(string $agentType): string
    {
        $names = [
            'sales' => 'Sales Agent',
            'inventory' => 'Inventory Agent', 
            'report' => 'Report Agent',
            'chat' => 'Chat Agent'
        ];
        
        return $names[$agentType] ?? 'Unknown Agent';
    }


    /**
     * Check if a proposal needs human approval
     */
    public function needsHumanApproval(array $response): bool
    {
        // Check if response contains important proposals
        $proposalKeywords = [
            'tạo chương trình khuyến mãi',
            'thay đổi giá',
            'xóa dữ liệu',
            'cập nhật hệ thống',
            'gửi email marketing',
            'tạo báo cáo tự động'
        ];

        $responseText = strtolower($response['reply'] ?? '');
        
        foreach ($proposalKeywords as $keyword) {
            if (strpos($responseText, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enhance context with real data from DataService
     */
    private function enhanceContextWithRealData(string $agentType, array $context): array
    {
        try {
            // Get agent-specific context data
            $agentData = $this->dataService->getAgentSpecificContext($agentType);
            
            // Add real data to context
            $context['real_data'] = $this->dataService->formatBusinessContextForLLM($this->dataService->getBusinessContext());
            $context['agent_data'] = $agentData;
            
            // Add vector store search capability
            $context['vector_store'] = $this->vectorEmbeddingService;
            
            Log::info('AICoordinator: Enhanced context with real data and vector store', [
                'agent_type' => $agentType,
                'data_keys' => array_keys($agentData)
            ]);
            
            return $context;
        } catch (\Throwable $e) {
            Log::warning('AICoordinator: Failed to enhance context with real data', [
                'error' => $e->getMessage(),
                'agent_type' => $agentType
            ]);
            
            // Return original context if enhancement fails
            return $context;
        }
    }


    /**
     * Get agent capabilities
     */
    public function getAgentCapabilities(string $agentType): array
    {
        $capabilities = [
            'omni' => ['Tra cứu', 'Phân tích', 'Gợi ý'],
            'sales' => ['Đơn hàng', 'Khách hàng', 'Bán hàng'],
            'inventory' => ['Tồn kho', 'Sản phẩm', 'Nhập xuất'],
            'report' => ['Báo cáo', 'KPI', 'Phân tích'],
            'chat' => ['Trò chuyện', 'Tìm kiếm', 'Gợi ý']
        ];

        return $capabilities[$agentType] ?? $capabilities['omni'];
    }
}
