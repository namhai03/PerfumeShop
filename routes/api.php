<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\OmniAIChatController;
use App\Http\Controllers\Api\SemanticSearchController;
use App\Http\Controllers\Api\UniversalEmbeddingController;
use App\Http\Controllers\Api\VectorSearchController;
use App\Http\Controllers\PromotionAiController;
use App\Models\Product;

Route::get('/products/{product}/variants', function (Product $product) {
    return $product->variants()
        ->select('id','sku','volume_ml','selling_price','stock')
        ->orderBy('volume_ml')
        ->get();
});

use Illuminate\Http\Request;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Models\Order;
use App\Http\Controllers\Api\ProductApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// OmniAI Chat API - Không cần authentication
Route::post('/ai/chat', [OmniAIChatController::class, 'chat'])->name('api.ai.chat')->withoutMiddleware(['web']);
Route::post('/ai/test', [OmniAIChatController::class, 'testLLM'])->name('api.ai.test')->withoutMiddleware(['web']);

// Semantic Search API
Route::prefix('semantic')->group(function () {
    Route::post('/search', [SemanticSearchController::class, 'searchProducts'])->name('api.semantic.search');
    Route::post('/hybrid-search', [SemanticSearchController::class, 'hybridSearch'])->name('api.semantic.hybrid');
    Route::get('/suggestions', [SemanticSearchController::class, 'getSuggestions'])->name('api.semantic.suggestions');
    Route::get('/coverage-stats', [SemanticSearchController::class, 'getCoverageStats'])->name('api.semantic.coverage');
    Route::post('/analyze-performance', [SemanticSearchController::class, 'analyzePerformance'])->name('api.semantic.analyze');
    Route::get('/products/{productId}/similar', [SemanticSearchController::class, 'findSimilarProducts'])->name('api.semantic.similar');
    Route::post('/products/{productId}/generate-embeddings', [SemanticSearchController::class, 'generateEmbeddings'])->name('api.semantic.generate');
});

// Simple test route
Route::get('/ai/ping', function() {
    return response()->json(['status' => 'ok', 'message' => 'API is working']);
});

// Test chat route đơn giản
Route::post('/ai/simple-chat', function(Request $request) {
    $message = $request->input('message', '');
    return response()->json([
        'success' => true,
        'type' => 'simple',
        'reply' => 'Bạn đã gửi: ' . $message
    ]);
});

// Test LLM route đơn giản với memory và dữ liệu thực tế
Route::post('/ai/simple-llm', function(Request $request) {
    $message = $request->input('message', '');
    $agent = $request->input('agent', 'omni'); // Agent được gửi từ frontend
    $conversationHistory = $request->input('conversation_history', []);
    
    try {
        // Sử dụng AICoordinator để tự động phân loại và route đến Agent phù hợp
        $aiCoordinator = app(\App\Services\AICoordinator::class);
        
        $context = [
            'conversation_history' => $conversationHistory
        ];
        
        // Gọi AICoordinator để xử lý message với agent được chỉ định
        $result = $aiCoordinator->processMessage($message, $agent, $context);
        
        // Đảm bảo tất cả response đều có key cần thiết cho frontend
        $result['success'] = $result['success'] ?? true;
        $result['type'] = $result['type'] ?? 'general';
        $result['reply'] = $result['reply'] ?? 'Xin lỗi, tôi không thể xử lý yêu cầu này.';
        $result['products'] = $result['products'] ?? [];
        
        // Xử lý lỗi
        if (!$result['success']) {
            $result['type'] = 'error';
            $result['reply'] = $result['reply'] ?? 'Đã có lỗi xảy ra khi xử lý yêu cầu của bạn.';
        }
        
        // Log kết quả để debug
        Log::info('AI Coordinator Result', [
            'message' => $message,
            'agent' => $agent,
            'result_type' => $result['type'] ?? 'unknown',
            'success' => $result['success'] ?? false
        ]);
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        Log::error('AI Coordinator Error', [
            'message' => $message,
            'agent' => $agent,
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
});

// Universal Embedding API (Database-based - có thể bị lock)
Route::prefix('embedding')->group(function () {
    Route::post('/generate-all', [UniversalEmbeddingController::class, 'generateAll'])->name('api.embedding.generate-all');
    Route::post('/search', [UniversalEmbeddingController::class, 'search'])->name('api.embedding.search');
    Route::post('/search/products', [UniversalEmbeddingController::class, 'searchProducts'])->name('api.embedding.search-products');
    Route::post('/search/orders', [UniversalEmbeddingController::class, 'searchOrders'])->name('api.embedding.search-orders');
    Route::post('/search/customers', [UniversalEmbeddingController::class, 'searchCustomers'])->name('api.embedding.search-customers');
    Route::post('/search/shipments', [UniversalEmbeddingController::class, 'searchShipments'])->name('api.embedding.search-shipments');
    Route::post('/search/promotions', [UniversalEmbeddingController::class, 'searchPromotions'])->name('api.embedding.search-promotions');
    Route::post('/hybrid-search', [UniversalEmbeddingController::class, 'hybridSearch'])->name('api.embedding.hybrid-search');
    Route::get('/suggestions', [UniversalEmbeddingController::class, 'getSuggestions'])->name('api.embedding.suggestions');
    Route::post('/analyze-performance', [UniversalEmbeddingController::class, 'analyzePerformance'])->name('api.embedding.analyze-performance');
    Route::get('/stats', [UniversalEmbeddingController::class, 'getStats'])->name('api.embedding.stats');
    Route::get('/config', [UniversalEmbeddingController::class, 'checkConfiguration'])->name('api.embedding.config');
});

// Vector Store API (File-based - không bị database lock)
Route::prefix('vector')->group(function () {
    Route::post('/search', [VectorSearchController::class, 'search'])->name('api.vector.search');
    Route::post('/search/products', [VectorSearchController::class, 'searchProducts'])->name('api.vector.search-products');
    Route::post('/search/orders', [VectorSearchController::class, 'searchOrders'])->name('api.vector.search-orders');
    Route::post('/search/customers', [VectorSearchController::class, 'searchCustomers'])->name('api.vector.search-customers');
    Route::post('/search/shipments', [VectorSearchController::class, 'searchShipments'])->name('api.vector.search-shipments');
    Route::post('/search/promotions', [VectorSearchController::class, 'searchPromotions'])->name('api.vector.search-promotions');
    Route::get('/stats', [VectorSearchController::class, 'getStats'])->name('api.vector.stats');
    Route::delete('/clear', [VectorSearchController::class, 'clearAll'])->name('api.vector.clear');
    Route::get('/config', [VectorSearchController::class, 'checkConfig'])->name('api.vector.config');
});

// AI Promotions API
Route::post('/promotions/ai/suggest', [PromotionAiController::class, 'suggest'])->name('api.promotions.ai.suggest');
Route::post('/promotions/ai/generate-copy', [PromotionAiController::class, 'generateCopy'])->name('api.promotions.ai.generateCopy');
Route::post('/promotions/ai/launch', [PromotionAiController::class, 'launch'])->name('api.promotions.ai.launch');
Route::post('/promotions/ai/generate-image', [PromotionAiController::class, 'generateImage'])->name('api.promotions.ai.generateImage');
Route::post('/promotions/ai/send-email', [PromotionAiController::class, 'sendEmail'])->name('api.promotions.ai.sendEmail');



// API hỗ trợ tra cứu đơn hàng theo order_number để auto-fill tạo vận đơn
Route::get('/orders/by-number/{order_number}', function (string $order_number) {
    $order = Order::where('order_number', $order_number)->first();
    if (!$order) {
        return response()->json(['found' => false], 404);
    }
    return [
        'found' => true,
        'order_number' => $order->order_number,
        'customer_name' => $order->customer_name,
        'phone' => $order->phone,
        'address' => $order->delivery_address,
        'ward' => $order->ward,
        'city' => $order->city,
        'final_amount' => (float)$order->final_amount,
    ];
});

