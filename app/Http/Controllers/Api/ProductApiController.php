<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    /**
     * Lấy danh sách sản phẩm sắp hết hạn
     */
    public function getExpiringSoonProducts(): JsonResponse
    {
        $expiringSoonProducts = Product::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'sku',
                'expiry_date',
                'stock',
                'selling_price'
            ])
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $expiringSoonProducts,
            'count' => $expiringSoonProducts->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Cập nhật tồn kho sản phẩm
     */
    public function updateProductStock(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'adjustment_type' => 'required|in:set,increase,decrease'
        ]);

        $newStock = match($request->adjustment_type) {
            'set' => $request->stock,
            'increase' => $product->stock + $request->stock,
            'decrease' => $product->stock - $request->stock,
        };

        $product->update(['stock' => max(0, $newStock)]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tồn kho thành công',
            'data' => $product->fresh(),
            'timestamp' => now()->toISOString()
        ]);
    }
}

