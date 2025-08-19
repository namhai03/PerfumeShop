<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Base query để có thể tái sử dụng cho tính tổng
        $baseQuery = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search){
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Tabs: tat_ca | con_hang | het_hang | low_stock
        $tab = $request->get('tab', 'tat_ca');
        if ($tab === 'con_hang') {
            $baseQuery->where('stock', '>', 0);
        } elseif ($tab === 'het_hang') {
            $baseQuery->where('stock', '<=', 0);
        } elseif ($tab === 'low_stock') {
            $baseQuery->where('stock', '>', 0)
                      ->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        // Sắp xếp có whitelist để tránh lỗi truy vấn
        $allowedSorts = ['sku','name','stock','selling_price','import_price','id'];
        $sortBy = $request->get('sort_by', 'sku');
        $sortOrder = $request->get('sort_order', 'asc');
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'sku'; }
        $sortOrder = strtolower($sortOrder) === 'desc' ? 'desc' : 'asc';

        // Query cho danh sách (chỉ select cột cần thiết)
        $listQuery = (clone $baseQuery)->select([
            'id','name','sku','barcode','stock','low_stock_threshold','selling_price','import_price','image','category','updated_at'
        ])->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $products = $listQuery->paginate($perPage)->appends($request->query());

        $categories = Product::distinct()->pluck('category')->filter()->values();

        // Tổng theo toàn bộ tập kết quả đã lọc (không phân trang)
        $overallTotals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(stock),0) as total_qty')
            ->selectRaw('COALESCE(SUM(stock * import_price),0) as total_cost')
            ->selectRaw('COALESCE(SUM(stock * selling_price),0) as total_retail')
            ->first();

        // Tổng theo trang hiện tại
        $pageTotals = [
            'total_qty' => $products->getCollection()->sum('stock'),
            'total_cost' => $products->getCollection()->reduce(function($carry, $p){
                return $carry + ((float)($p->stock ?? 0) * (float)($p->import_price ?? 0));
            }, 0.0),
            'total_retail' => $products->getCollection()->reduce(function($carry, $p){
                return $carry + ((float)($p->stock ?? 0) * (float)($p->selling_price ?? 0));
            }, 0.0),
        ];

        return view('inventory.index', compact(
            'products', 'tab', 'sortBy', 'sortOrder', 'perPage', 'categories', 'overallTotals', 'pageTotals'
        ));
    }

    public function history(Request $request)
    {
        $query = InventoryMovement::query()->with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        $products = Product::orderBy('name')->get(['id','name','sku']);

        return view('inventory.history', compact('movements', 'products'));
    }

    public function show(Product $product)
    {
        $movements = InventoryMovement::where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('inventory.show', compact('product', 'movements'));
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:import,export,adjust,stocktake,return,damage',
            'quantity' => 'required|integer',
            'note' => 'nullable|string|max:1000',
            'transaction_date' => 'nullable|date',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'order_id' => 'nullable|integer',
        ]);

        $quantity = (int) $validated['quantity'];

        // Chuẩn hóa theo type:
        // - import, return: dương
        // - export, damage: âm
        // - adjust: có thể dương/âm tùy input
        // - stocktake: quantity là tồn thực tế -> tính chênh lệch
        $before = (int) $product->stock;
        $change = 0;

        switch ($validated['type']) {
            case 'import':
                $change = abs($quantity);
                break;
            case 'return':
                $change = abs($quantity);
                break;
            case 'export':
                $change = -abs($quantity);
                break;
            case 'damage':
                $change = -abs($quantity);
                break;
            case 'stocktake':
                $change = $quantity - $before; // đặt theo tồn thực tế
                break;
            case 'adjust':
                $change = $quantity; // chấp nhận âm/dương
                break;
        }

        $after = $before + $change;
        if ($after < 0) {
            return back()->with('error', 'Không thể thực hiện, tồn kho sẽ âm.');
        }

        $product->update(['stock' => $after]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $validated['type'],
            'quantity_change' => $change,
            'before_stock' => $before,
            'after_stock' => $after,
            'performed_by' => null,
            'note' => $validated['note'] ?? null,
            'transaction_date' => $validated['transaction_date'] ?? now(),
            'unit_cost' => $validated['unit_cost'] ?? null,
            'supplier' => $validated['supplier'] ?? null,
            'reference_id' => $validated['reference_id'] ?? null,
            'order_id' => $validated['order_id'] ?? null,
        ]);

        return back()->with('success', 'Cập nhật tồn kho thành công.');
    }
}


