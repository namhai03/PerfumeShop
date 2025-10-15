<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AICoordinator;

class OmniAIChatController extends Controller
{
    private AICoordinator $aiCoordinator;

    public function __construct(AICoordinator $aiCoordinator)
    {
        $this->aiCoordinator = $aiCoordinator;
    }

    /**
     * Endpoint chính xử lý chat cho OmniAI với AI Agents Architecture.
     * Sử dụng AICoordinator để route đến các agents chuyên biệt.
     * 
     * Lưu ý: Không cần key để phân loại, sử dụng LLM để phân loại agent phù hợp.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            // Không cần validate agent key nữa, agent là optional, để LLM tự phân loại
            'agent' => 'nullable|string',
            'context' => 'nullable|array',
        ]);

        $message = trim($request->input('message'));
        // Không lấy agent từ request, để LLM tự phân loại
        $selectedAgent = 'omni'; // Luôn sử dụng omni để LLM tự phân loại
        $context = $request->input('context', []);

        try {
            Log::info('OmniAI: Processing message with LLM-based agent classification', [
                'message' => substr($message, 0, 100),
                'selected_agent' => $selectedAgent,
                'context' => $context
            ]);

            // Sử dụng AICoordinator để LLM tự phân loại agent phù hợp
            $response = $this->aiCoordinator->processMessage($message, $selectedAgent, $context);

            // Đảm bảo tất cả response đều có key cần thiết cho frontend
            $response = $this->ensureResponseKeys($response);

            // Check if response needs human approval
            if ($this->aiCoordinator->needsHumanApproval($response)) {
                $response['needs_approval'] = true;
                $response['proposal'] = [
                    'type' => 'important_proposal',
                    'message' => 'Đề xuất quan trọng cần phê duyệt',
                    'details' => $response['reply']
                ];
            }

            Log::info('OmniAI: Response generated', [
                'type' => $response['type'] ?? 'unknown',
                'success' => $response['success'] ?? false,
                'needs_approval' => $response['needs_approval'] ?? false
            ]);

            return response()->json($response);

        } catch (\Throwable $e) {
            Log::error('OmniAI: Error processing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'type' => 'error',
                'reply' => 'Xin lỗi, đã có lỗi xảy ra khi xử lý yêu cầu của bạn.',
                'error' => $e->getMessage(),
                'products' => []
            ]);
        }
    }

    /**
     * Đảm bảo tất cả response đều có key cần thiết cho frontend
     */
    private function ensureResponseKeys(array $response): array
    {
        // Đảm bảo các key bắt buộc tồn tại với giá trị mặc định
        $response['success'] = $response['success'] ?? true;
        $response['type'] = $response['type'] ?? 'general';
        $response['reply'] = $response['reply'] ?? 'Xin lỗi, tôi không thể xử lý yêu cầu này.';
        $response['products'] = $response['products'] ?? [];
        
        // Xử lý lỗi
        if (!$response['success']) {
            $response['type'] = 'error';
            $response['reply'] = $response['reply'] ?? 'Đã có lỗi xảy ra khi xử lý yêu cầu của bạn.';
        }
        
        return $response;
    }
}