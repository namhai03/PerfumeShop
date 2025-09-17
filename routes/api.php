<?php

use Illuminate\Support\Facades\Route;
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

