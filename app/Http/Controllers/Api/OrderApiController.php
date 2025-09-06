<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderApiController extends Controller
{
    /**
     * Lấy danh sách đơn hàng chờ xử lý
     */
    public function getPendingOrders(): JsonResponse
    {
        $pendingOrders = Order::whereIn('status', [Order::STATUS_NEW, Order::STATUS_PROCESSING])
            ->with(['customer:id,name,phone', 'items:id,order_id,product_id,quantity,price'])
            ->orderBy('order_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendingOrders,
            'count' => $pendingOrders->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:new,processing,completed'
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => $order->fresh(),
            'timestamp' => now()->toISOString()
        ]);
    }
}

