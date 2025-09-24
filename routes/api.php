<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OmniAIChatController;
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
    $conversationHistory = $request->input('conversation_history', []);
    
    try {
        $llm = app(\App\Services\LLMService::class);
        if ($llm->isConfigured()) {
            // Lấy dữ liệu thực tế từ database
            $dataService = app(\App\Services\DataService::class);
            $realData = $dataService->getRealDataForLLM($message);
            
            // Debug logging
            \Log::info('LLM Debug', [
                'message' => $message,
                'real_data_length' => strlen($realData),
                'real_data_preview' => substr($realData, 0, 200)
            ]);
            
            $response = $llm->chat($message, [
                'conversation_history' => $conversationHistory,
                'real_data' => $realData
            ]);
            return response()->json([
                'success' => true,
                'type' => 'llm',
                'reply' => $response
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'LLM not configured'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});


// API endpoints cho n8n
Route::prefix('n8n')->group(function () {
    // Inventory APIs
    Route::get('/inventory/low-stock', [InventoryApiController::class, 'getLowStockProducts']);
    Route::get('/inventory/overview', [InventoryApiController::class, 'getInventoryOverview']);
    
    // Order APIs
    Route::get('/orders/pending', [OrderApiController::class, 'getPendingOrders']);
    Route::post('/orders/{order}/update-status', [OrderApiController::class, 'updateOrderStatus']);
    
    // Product APIs
    Route::get('/products/expiring-soon', [ProductApiController::class, 'getExpiringSoonProducts']);
    Route::post('/products/{product}/update-stock', [ProductApiController::class, 'updateProductStock']);
});

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

