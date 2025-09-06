<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    /**
     * Lấy danh sách sản phẩm có tồn kho thấp
     */
    public function getLowStockProducts(): JsonResponse
    {
        $lowStockProducts = Product::where('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'sku',
                'stock',
                'low_stock_threshold',
                'selling_price',
                'category'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockProducts,
            'count' => $lowStockProducts->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Lấy tổng quan kho hàng
     */
    public function getInventoryOverview(): JsonResponse
    {
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->count();
        $outOfStockProducts = Product::where('stock', 0)
            ->where('is_active', true)
            ->count();
        $totalStockValue = Product::where('is_active', true)
            ->sum(DB::raw('stock * selling_price'));

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStockProducts,
                'out_of_stock_products' => $outOfStockProducts,
                'total_stock_value' => $totalStockValue,
                'low_stock_percentage' => $totalProducts > 0 ? round(($lowStockProducts / $totalProducts) * 100, 2) : 0
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
}
