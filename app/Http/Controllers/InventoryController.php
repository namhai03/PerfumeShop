<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Base query để có thể tái sử dụng cho tính tổng
        $baseQuery = Product::query();

        // Tìm kiếm theo mã sản phẩm, tên sản phẩm, barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Lọc theo loại sản phẩm (category) - sử dụng relationship
        if ($request->filled('category')) {
            $baseQuery->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Lọc theo thương hiệu
        if ($request->filled('brand')) {
            $baseQuery->where('brand', $request->brand);
        }

        // Lọc theo thuộc tính mùi hương và giới tính
        if ($request->filled('fragrance_family')) {
            $baseQuery->where('fragrance_family', $request->fragrance_family);
        }
        if ($request->filled('concentration')) {
            $baseQuery->where('concentration', $request->concentration);
        }
        if ($request->filled('gender')) {
            $baseQuery->where('gender', $request->gender);
        }

        // Lọc theo khoảng thời gian tạo
        if ($request->filled('created_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->created_to);
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
            'id','name','sku','barcode','stock','low_stock_threshold','selling_price','import_price','image','brand','category','concentration','updated_at'
        ])->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $products = $listQuery->with('categories')->paginate($perPage)->appends($request->query());

        // Lấy danh sách các giá trị duy nhất cho filter
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = Product::distinct()->pluck('brand')->filter()->values();
        $fragranceFamilies = Product::distinct()->pluck('fragrance_family')->filter()->values();
        $genders = Product::distinct()->pluck('gender')->filter()->values();

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
            'products', 'tab', 'sortBy', 'sortOrder', 'perPage', 'categories', 'brands', 'fragranceFamilies', 'genders', 'overallTotals', 'pageTotals'
        ));
    }

    public function history(Request $request)
    {
        $query = InventoryMovement::query()->with('product');

        // Lọc theo sản phẩm
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Lọc theo loại giao dịch
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Tìm kiếm theo tên sản phẩm, SKU hoặc ghi chú
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('product', function($p) use ($search) {
                      $p->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Khoảng thời gian: ưu tiên date_range, fallback from/to
        $dateRange = $request->get('date_range');
        if ($dateRange) {
            $today = now();
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', $today->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $lw = $today->copy()->subWeek();
                    $query->whereBetween('created_at', [$lw->startOfWeek(), $lw->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
                    break;
                case 'last_month':
                    $lm = $today->copy()->subMonth();
                    $query->whereBetween('created_at', [$lm->startOfMonth(), $lm->endOfMonth()]);
                    break;
                case 'custom':
                    // sẽ rơi xuống xử lý from/to phía dưới
                    break;
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [20,50,100]) ? $perPage : 20;

        // Thống kê theo tập dữ liệu đã lọc
        $statsQuery = (clone $query);
        $totalImport = (clone $statsQuery)->where('quantity_change', '>', 0)->sum('quantity_change');
        $totalExport = abs((clone $statsQuery)->where('quantity_change', '<', 0)->sum('quantity_change'));

        // Giao dịch trong khoảng thời gian hiện tại (nếu không có, mặc định hôm nay)
        $todayRangeQuery = (clone $query);
        if (!$dateRange && !$request->filled('from') && !$request->filled('to')) {
            $today = now();
            $todayRangeQuery->whereDate('created_at', $today);
        }
        $todayTransactions = $todayRangeQuery->count();

        $productsWithMovements = (clone $statsQuery)->distinct('product_id')->count('product_id');

        $stats = [
            'total_import' => (int) $totalImport,
            'total_export' => (int) $totalExport,
            'today_transactions' => (int) $todayTransactions,
            'products_with_movements' => (int) $productsWithMovements,
        ];

        $movements = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());
        $products = Product::orderBy('name')->get(['id','name','sku']);

        return view('inventory.history', compact('movements', 'products', 'stats'));
    }

    public function show(Request $request, Product $product)
    {
        $query = InventoryMovement::where('product_id', $product->id)
            ->with(['product', 'order']);

        // Lọc theo loại giao dịch
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Lọc theo khoảng thời gian
        if ($request->filled('time_range')) {
            $today = now();
            switch ($request->time_range) {
                case 'today':
                    $query->whereDate('transaction_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('transaction_date', $today->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('transaction_date', [$today->startOfWeek(), $today->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('transaction_date', [$today->subWeek()->startOfWeek(), $today->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('transaction_date', [$today->startOfMonth(), $today->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('transaction_date', [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()]);
                    break;
            }
        } else {
            // Lọc theo ngày tùy chỉnh
            if ($request->filled('date_from')) {
                $query->where('transaction_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
            }
        }

        // Lọc theo loại thay đổi (tăng/giảm)
        if ($request->filled('change_type')) {
            if ($request->change_type === 'increase') {
                $query->increases();
            } elseif ($request->change_type === 'decrease') {
                $query->decreases();
            }
        }

        // Tìm kiếm theo ghi chú hoặc mã tham chiếu
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        // Lọc theo nhà cung cấp
        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', "%{$request->supplier}%");
        }

        // Lọc theo mã tham chiếu
        if ($request->filled('reference_id')) {
            $query->where('reference_id', 'like', "%{$request->reference_id}%");
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $movements = $query->paginate(20)->withQueryString();

        // Thống kê
        $stats = InventoryMovement::getMovementStats($product->id, [
            'start' => $request->date_from ?? now()->subDays(30)->format('Y-m-d'),
            'end' => $request->date_to ?? now()->format('Y-m-d')
        ]);

        // Các tùy chọn lọc
        $movementTypes = InventoryMovement::getMovementTypes();
        $changeTypes = [
            'increase' => 'Tăng kho',
            'decrease' => 'Giảm kho',
        ];

        return view('inventory.show', compact(
            'product', 
            'movements', 
            'stats', 
            'movementTypes', 
            'changeTypes'
        ));
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

    public function exportHistory(Request $request, Product $product)
    {
        $query = InventoryMovement::where('product_id', $product->id)
            ->with(['product', 'order']);

        // Áp dụng cùng logic lọc như show method
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('change_type')) {
            if ($request->change_type === 'increase') {
                $query->increases();
            } elseif ($request->change_type === 'decrease') {
                $query->decreases();
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        $movements = $query->orderBy('transaction_date', 'desc')->get();

        $fileName = 'lich_su_kho_' . $product->sku . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new class($movements, $product) implements FromCollection, WithHeadings {
            private $movements;
            private $product;

            public function __construct($movements, $product)
            {
                $this->movements = $movements;
                $this->product = $product;
            }

            public function collection()
            {
                return $this->movements->map(function ($movement) {
                    return [
                        'Thời gian' => $movement->transaction_date_formatted,
                        'Loại giao dịch' => $movement->type_text,
                        'Thay đổi' => $movement->quantity_change_formatted,
                        'Tồn trước' => $movement->before_stock,
                        'Tồn sau' => $movement->after_stock,
                        'Ghi chú' => $movement->note,
                        'Mã tham chiếu' => $movement->reference_id,
                        'Nhà cung cấp' => $movement->supplier,
                        'Giá đơn vị' => $movement->unit_cost ? number_format($movement->unit_cost, 0, ',', '.') . '₫' : '',
                        'Tổng giá trị' => $movement->total_value ? number_format($movement->total_value, 0, ',', '.') . '₫' : '',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Thời gian',
                    'Loại giao dịch', 
                    'Thay đổi',
                    'Tồn trước',
                    'Tồn sau',
                    'Ghi chú',
                    'Mã tham chiếu',
                    'Nhà cung cấp',
                    'Giá đơn vị',
                    'Tổng giá trị'
                ];
            }
        }, $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:4096'
        ]);

        try {
            $ext = strtolower($request->file('file')->getClientOriginalExtension());

            // Fallback: nếu là CSV/TXT thì nhập bằng PHP thuần
            if (in_array($ext, ['csv', 'txt'])) {
                $this->importCsv($request->file('file'));
                return redirect()->route('inventory.index')
                    ->with('success', 'Import tồn kho (CSV) thành công!');
            }

            // XLSX/XLS: cần package maatwebsite/excel
            if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                return redirect()->route('inventory.index')
                    ->with('error', 'Môi trường thiếu hỗ trợ import Excel (xlsx/xls). Vui lòng chuyển file sang CSV để nhập.');
            }

            Excel::import(new class implements ToModel, WithHeadingRow {
                public function model(array $row)
                {
                    $product = Product::where('sku', $row['sku'])->first();
                    
                    if ($product) {
                        // Update tồn kho
                        $product->update([
                            'stock' => $row['stock'] ?? $product->stock,
                            'low_stock_threshold' => $row['low_stock_threshold'] ?? $product->low_stock_threshold,
                            'import_price' => $row['import_price'] ?? $product->import_price,
                            'selling_price' => $row['selling_price'] ?? $product->selling_price,
                        ]);
                        return null;
                    } else {
                        // Tạo sản phẩm mới
                        return new Product([
                            'name' => $row['name'],
                            'sku' => $row['sku'],
                            'stock' => $row['stock'] ?? 0,
                            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
                            'import_price' => $row['import_price'] ?? 0,
                            'selling_price' => $row['selling_price'] ?? 0,
                            'brand' => $row['brand'] ?? null,
                            'category' => $row['category'] ?? '',
                            'is_active' => true,
                        ]);
                    }
                }
            }, $request->file('file'));

            return redirect()->route('inventory.index')
                ->with('success', 'Import tồn kho thành công!');

        } catch (\Exception $e) {
            return redirect()->route('inventory.index')
                ->with('error', 'Lỗi khi import: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $format = request('format', 'xlsx');
        $filename = 'inventory_export_' . now()->format('Y-m-d_H-i-s') . '.' . $format;

        $columns = $this->getInventoryExportColumns();

        if ($format === 'csv') {
            $callback = function() use ($columns) {
                $handle = fopen('php://output', 'w');
                // BOM cho UTF-8
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $columns);
                // Lấy dữ liệu tức thời từ DB, không cache
                Product::with('categories')->orderBy('sku')->chunk(500, function($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        $data = $row->only($columns);
                        // Bổ sung cột quan hệ/biến đổi
                        if (in_array('categories_names', $columns)) {
                            $data['categories_names'] = $row->categories ? $row->categories->pluck('name')->join(', ') : '';
                        }
                        // Đảm bảo tính toàn vẹn: ép kiểu số cho các cột định lượng
                        $data['stock'] = (int)($data['stock'] ?? 0);
                        $data['low_stock_threshold'] = (int)($data['low_stock_threshold'] ?? 0);
                        $data['import_price'] = (float)($data['import_price'] ?? 0);
                        $data['selling_price'] = (float)($data['selling_price'] ?? 0);
                        fputcsv($handle, $data);
                    }
                });
                fclose($handle);
            };
            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Nếu môi trường không có Excel package, fallback sang CSV
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $altName = 'inventory_export.csv';
            $callback = function() use ($columns) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $columns);
                Product::select($columns)->orderBy('sku')->chunk(500, function($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        $data = $row->only($columns);
                        fputcsv($handle, $data);
                    }
                });
                fclose($handle);
            };
            return response()->streamDownload($callback, $altName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Mặc định xuất XLSX
        $export = new class($columns) implements FromCollection, WithHeadings {
            private array $cols; 
            public function __construct(array $cols) { $this->cols = $cols; }
            public function collection()
            {
                // Lấy dữ liệu tức thời từ DB và map theo cột chuẩn
                return Product::with('categories')->orderBy('sku')->get()->map(function($row){
                    $data = $row->only($this->cols);
                    if (in_array('categories_names', $this->cols)) {
                        $data['categories_names'] = $row->categories ? $row->categories->pluck('name')->join(', ') : '';
                    }
                    $data['stock'] = (int)($data['stock'] ?? 0);
                    $data['low_stock_threshold'] = (int)($data['low_stock_threshold'] ?? 0);
                    $data['import_price'] = (float)($data['import_price'] ?? 0);
                    $data['selling_price'] = (float)($data['selling_price'] ?? 0);
                    return $data;
                });
            }
            public function headings(): array { return $this->cols; }
        };
        return Excel::download($export, $filename);
    }

    private function importCsv($uploadedFile): void
    {
        $path = $uploadedFile->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Không thể mở file CSV');
        }

        $headers = [];
        if (($row = fgetcsv($handle)) !== false) {
            // Loại bỏ BOM UTF-8
            if (isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            }
            $headers = array_map(fn($h) => strtolower(trim($h)), $row);
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim($row[0]) === '') { continue; }
            $data = [];
            foreach ($headers as $i => $key) {
                $data[$key] = $row[$i] ?? null;
            }

            if (empty($data['sku'])) {
                continue; // bỏ qua dòng thiếu SKU
            }

            $product = Product::where('sku', $data['sku'])->first();
            $payload = [
                'stock' => isset($data['stock']) ? (int)$data['stock'] : ($product ? $product->stock : 0),
                'low_stock_threshold' => isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : ($product ? $product->low_stock_threshold : 5),
                'import_price' => isset($data['import_price']) ? (float)$data['import_price'] : ($product ? $product->import_price : 0),
                'selling_price' => isset($data['selling_price']) ? (float)$data['selling_price'] : ($product ? $product->selling_price : 0),
            ];

            if ($product) {
                $product->update($payload);
            } else {
                $payload['sku'] = $data['sku'];
                $payload['name'] = $data['name'] ?? 'Sản phẩm mới';
                $payload['brand'] = $data['brand'] ?? null;
                $payload['category'] = $data['category'] ?? '';
                $payload['is_active'] = true;
                Product::create($payload);
            }
        }

        fclose($handle);
    }

    public function downloadImportTemplate()
    {
        $filename = 'inventory_import_template_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 để Excel hiển thị tiếng Việt đúng
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            $columns = $this->getInventoryExportColumns();
            fputcsv($handle, $columns);
            // Sinh một vài dòng mẫu dựa trên dữ liệu hiện có (nếu có)
            $samples = Product::with('categories')->orderBy('id', 'asc')->limit(3)->get();
            if ($samples->count() > 0) {
                foreach ($samples as $row) {
                    $data = $row->only($columns);
                    if (in_array('categories_names', $columns)) {
                        $data['categories_names'] = $row->categories ? $row->categories->pluck('name')->join(', ') : '';
                    }
                    $data['stock'] = (int)($data['stock'] ?? 0);
                    $data['low_stock_threshold'] = (int)($data['low_stock_threshold'] ?? 0);
                    $data['import_price'] = (float)($data['import_price'] ?? 0);
                    $data['selling_price'] = (float)($data['selling_price'] ?? 0);
                    fputcsv($handle, $data);
                }
            } else {
                // Không có dữ liệu -> ghi 2 dòng minh hoạ
                fputcsv($handle, ['CHANEL-COCO-EDP-50','Coco Mademoiselle 50ml','Chanel','Nước hoa nữ',10,5,1500000,2300000,'','', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
                fputcsv($handle, ['DIOR-SAUVAGE-EDT-100','Sauvage EDT 100ml','Dior','Nước hoa nam',8,3,1800000,2700000,'','', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getInventoryExportColumns(): array
    {
        // Danh sách cột chuẩn (đủ thông tin sản phẩm). Các cột dư sẽ bị bỏ qua khi import.
        return [
            'id',
            'sku',
            'name',
            'description',
            'brand',
            'category',
            'categories_names', // tên danh mục (nhiều), tính khi export
            'barcode',
            'stock',
            'low_stock_threshold',
            'import_price',
            'selling_price',
            'image',
            'volume',
            'concentration',
            'origin',
            'import_date',
            'sales_channel',
            'tags',
            'is_active',
            'product_type',
            'product_form',
            'expiry_date',
            'branch_price',
            'customer_group_price',
            'created_date',
            'fragrance_family',
            'top_notes',
            'heart_notes',
            'base_notes',
            'gender',
            'style',
            'season',
            'created_at',
            'updated_at',
        ];
    }
}


