<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Tìm kiếm theo mã sản phẩm, tên sản phẩm, barcode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
                
                // Chỉ tìm kiếm barcode nếu trường này tồn tại
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->orWhere('barcode', 'like', "%{$search}%");
                }
            });
        }

        // Lọc theo loại sản phẩm (category) - sử dụng relationship
        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Lọc theo tag (hỗ trợ nhiều tag)
        if ($request->filled('tags')) {
            $selectedTags = (array) $request->input('tags');
            foreach ($selectedTags as $tag) {
                $query->whereRaw('FIND_IN_SET(?, tags)', [$tag]);
            }
        } elseif ($request->filled('tag')) { // tương thích tham số cũ
            $query->whereRaw('FIND_IN_SET(?, tags)', [$request->input('tag')]);
        }

        // Lọc theo trạng thái có thể bán
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Lọc theo nhãn hiệu
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Lọc theo thuộc tính mùi hương và giới tính
        if ($request->filled('fragrance_family')) {
            $query->where('fragrance_family', $request->fragrance_family);
        }
        if ($request->filled('concentration')) {
            $query->where('concentration', $request->concentration);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('ingredients')) {
            $query->whereRaw('FIND_IN_SET(?, ingredients)', [$request->input('ingredients')]);
        }

        // Lọc tồn kho thấp
        if ($request->filled('low_stock') && $request->low_stock == '1') {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        // Lọc theo khoảng thời gian tạo và ngày nhập hàng
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }
        if ($request->filled('import_from')) {
            $query->whereDate('import_date', '>=', $request->import_from);
        }
        if ($request->filled('import_to')) {
            $query->whereDate('import_date', '<=', $request->import_to);
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Phân trang
        $perPage = $request->get('per_page', 20);
        $products = $query->with('categories')->paginate($perPage);

        // Lấy danh sách các giá trị duy nhất cho filter
        $categories = Category::orderBy('name')->get();
        $brands = Product::distinct()->pluck('brand')->filter()->values();
        $fragranceFamilies = Product::distinct()->pluck('fragrance_family')->filter()->values();
        $genders = Product::distinct()->pluck('gender')->filter()->values();
        // Tách các tag duy nhất từ chuỗi CSV 'tags'
        $tags = collect(Product::pluck('tags')->filter()->all())
            ->flatMap(function ($csv) {
                return collect(explode(',', $csv))
                    ->map(fn ($t) => trim($t))
                    ->filter();
            })
            ->unique()
            ->values();
        // Tách các ingredients duy nhất từ chuỗi CSV 'ingredients'
        $ingredients = collect(Product::pluck('ingredients')->filter()->all())
            ->flatMap(function ($csv) {
                return collect(explode(',', $csv))
                    ->map(fn ($i) => trim($i))
                    ->filter();
            })
            ->unique()
            ->values();
        // Không dùng productTypes/productForms nữa

        return view('products.index', compact(
            'products', 
            'categories', 
            'brands', 
            'tags',
            'ingredients',
            'fragranceFamilies',
            'genders'
        ));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $allTags = collect(Product::pluck('tags')->filter()->all())
            ->flatMap(function ($csv) {
                return collect(explode(',', $csv))
                    ->map(fn ($t) => trim($t))
                    ->filter();
            })
            ->unique()
            ->values();
        $allIngredients = collect(Product::pluck('ingredients')->filter()->all())
            ->flatMap(function ($csv) {
                return collect(explode(',', $csv))
                    ->map(fn ($i) => trim($i))
                    ->filter();
            })
            ->unique()
            ->values();
        return view('products.create', compact('categories', 'allTags', 'allIngredients'));
    }

    public function store(Request $request)
    {
        // Parse categories (CSV -> array of ids) trước validate
        if ($request->filled('categories') && is_string($request->categories)) {
            $ids = collect(explode(',', $request->categories))->map(fn($v)=> (int)trim($v))->filter()->values()->all();
            $request->merge(['categories' => $ids]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'import_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            // Cho phép bỏ qua trường category khi dùng danh mục nhiều (categories[])
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:255',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:4096',
            'volume' => 'nullable|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'import_date' => 'nullable|date',
            'is_active' => 'boolean',
            'tags' => 'nullable',
            'ingredients' => 'nullable',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            // Thuộc tính mùi hương
            'fragrance_family' => 'nullable|string|max:255',
            'top_notes' => 'nullable|string',
            'heart_notes' => 'nullable|string',
            'base_notes' => 'nullable|string',
            'gender' => 'nullable|string|max:50',
            'style' => 'nullable|string|max:255',
            'season' => 'nullable|string|max:255',
            // Biến thể (nếu gửi kèm)
            'variants' => 'nullable|array',
            'variants.*.volume_ml' => 'required_with:variants|integer|min:1',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.import_price' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        // Giữ tương thích cột category (NOT NULL) bằng chuỗi rỗng nếu không dùng
        if (!array_key_exists('category', $data) || $data['category'] === null) {
            $data['category'] = '';
        }
        // Upload ảnh nếu có
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = '/storage/' . $path;
        }
        if ($request->has('tags')) {
            $tagsValue = $request->input('tags');
            if (is_array($tagsValue)) {
                $data['tags'] = $this->normalizeTags(implode(',', $tagsValue));
            } else {
                $data['tags'] = $this->normalizeTags($tagsValue);
            }
        }
        if ($request->has('ingredients')) {
            $ingredientsValue = $request->input('ingredients');
            if (is_array($ingredientsValue)) {
                $data['ingredients'] = $this->normalizeIngredients(implode(',', $ingredientsValue));
            } else {
                $data['ingredients'] = $this->normalizeIngredients($ingredientsValue);
            }
        }
        if (!isset($data['low_stock_threshold'])) {
            $data['low_stock_threshold'] = 5;
        }
        $product = Product::create($data);

        // Gán danh mục (nhiều) nếu có
        if ($request->filled('categories')) {
            $product->categories()->sync($request->input('categories'));
        }

        // Tạo biến thể nếu có
        if ($request->filled('variants') && is_array($request->variants)) {
            foreach ($request->variants as $v) {
                if (!isset($v['volume_ml'])) continue;
                $variantData = [
                    'product_id' => $product->id,
                    'volume_ml' => (int) $v['volume_ml'],
                    'sku' => $v['sku'] ?? ($product->sku . '-' . (int)$v['volume_ml'] . 'ml'),
                    'import_price' => $v['import_price'] ?? null,
                    'selling_price' => $v['selling_price'] ?? null,
                    'stock' => $v['stock'] ?? 0,
                ];
                ProductVariant::create($variantData);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $allTags = collect(Product::pluck('tags')->filter()->all())
            ->flatMap(function ($csv) { return collect(explode(',', $csv))->map(fn($t)=>trim($t))->filter(); })
            ->unique()->values();
        $allIngredients = collect(Product::pluck('ingredients')->filter()->all())
            ->flatMap(function ($csv) { return collect(explode(',', $csv))->map(fn($i)=>trim($i))->filter(); })
            ->unique()->values();
        $selectedCategoryIds = $product->categories()->pluck('categories.id')->toArray();
        return view('products.edit', compact('product', 'categories', 'selectedCategoryIds', 'allTags', 'allIngredients'));
    }

    public function update(Request $request, Product $product)
    {
        // Parse categories (CSV -> array of ids) trước validate
        if ($request->filled('categories') && is_string($request->categories)) {
            $ids = collect(explode(',', $request->categories))->map(fn($v)=> (int)trim($v))->filter()->values()->all();
            $request->merge(['categories' => $ids]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'import_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            // Cho phép bỏ qua trường category khi dùng danh mục nhiều (categories[])
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:255',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:4096',
            'volume' => 'nullable|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'import_date' => 'nullable|date',
            'is_active' => 'boolean',
            'tags' => 'nullable',
            'ingredients' => 'nullable',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            // Thuộc tính mùi hương
            'fragrance_family' => 'nullable|string|max:255',
            'top_notes' => 'nullable|string',
            'heart_notes' => 'nullable|string',
            'base_notes' => 'nullable|string',
            'gender' => 'nullable|string|max:50',
            'style' => 'nullable|string|max:255',
            'season' => 'nullable|string|max:255',
            // Biến thể
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.volume_ml' => 'required_with:variants|integer|min:1',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.import_price' => 'nullable|numeric|min:0',
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        if (!array_key_exists('category', $data) || $data['category'] === null) {
            $data['category'] = $product->category ?? '';
        }
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = '/storage/' . $path;
        }
        if ($request->has('tags')) {
            $tagsValue = $request->input('tags');
            if (is_array($tagsValue)) {
                $data['tags'] = $this->normalizeTags(implode(',', $tagsValue));
            } else {
                $data['tags'] = $this->normalizeTags($tagsValue);
            }
        }
        if ($request->has('ingredients')) {
            $ingredientsValue = $request->input('ingredients');
            if (is_array($ingredientsValue)) {
                $data['ingredients'] = $this->normalizeIngredients(implode(',', $ingredientsValue));
            } else {
                $data['ingredients'] = $this->normalizeIngredients($ingredientsValue);
            }
        }
        if (!isset($data['low_stock_threshold'])) {
            $data['low_stock_threshold'] = $product->low_stock_threshold ?? 5;
        }
        $product->update($data);

        // Cập nhật gán danh mục
        $product->categories()->sync($request->input('categories', []));

        // Đồng bộ biến thể
        if ($request->has('variants') && is_array($request->variants)) {
            $handledIds = [];
            foreach ($request->variants as $v) {
                if (!isset($v['volume_ml'])) continue;
                if (!empty($v['id'])) {
                    $variant = ProductVariant::where('id', $v['id'])->where('product_id', $product->id)->first();
                    if ($variant) {
                        $variant->update([
                            'volume_ml' => (int)$v['volume_ml'],
                            'sku' => $v['sku'] ?? ($product->sku . '-' . (int)$v['volume_ml'] . 'ml'),
                            'import_price' => $v['import_price'] ?? null,
                            'selling_price' => $v['selling_price'] ?? null,
                            'stock' => $v['stock'] ?? 0,
                        ]);
                        $handledIds[] = $variant->id;
                    }
                } else {
                    $new = ProductVariant::create([
                        'product_id' => $product->id,
                        'volume_ml' => (int)$v['volume_ml'],
                        'sku' => $v['sku'] ?? ($product->sku . '-' . (int)$v['volume_ml'] . 'ml'),
                        'import_price' => $v['import_price'] ?? null,
                        'selling_price' => $v['selling_price'] ?? null,
                        'stock' => $v['stock'] ?? 0,
                    ]);
                    $handledIds[] = $new->id;
                }
            }
            // Xóa biến thể không còn trong payload
            if (!empty($handledIds)) {
                ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $handledIds)
                    ->delete();
            }
        }

        // Điều hướng theo ngữ cảnh (ví dụ: từ trang tồn kho)
        $redirectTo = $request->input('redirect_to');
        if ($redirectTo === 'inventory') {
            return redirect()->route('inventory.index')
                ->with('success', 'Sản phẩm đã được cập nhật thành công!');
        }

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('products.index')->with('error', 'Chưa chọn sản phẩm nào.');
        }
        Product::whereIn('id', $ids)->delete();
        return redirect()->route('products.index')->with('success', 'Đã xóa ' . count($ids) . ' sản phẩm.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:4096'
        ]);

        try {
            $ext = strtolower($request->file('file')->getClientOriginalExtension());

            // Fallback: nếu là CSV/TXT thì nhập bằng PHP thuần, không cần package Excel
            if (in_array($ext, ['csv', 'txt'])) {
                $this->importCsv($request->file('file'));
                return redirect()->route('products.index')
                    ->with('success', 'Import sản phẩm (CSV) thành công!');
            }

            // XLSX/XLS: cần package maatwebsite/excel bản hỗ trợ Concerns
            if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)
                || !interface_exists(\Maatwebsite\Excel\Concerns\ToModel::class)
                || !interface_exists(\Maatwebsite\Excel\Concerns\WithHeadingRow::class)) {
                return redirect()->route('products.index')
                    ->with('error', 'Môi trường thiếu hỗ trợ import Excel (xlsx/xls). Vui lòng chuyển file sang CSV để nhập.');
            }

            Excel::import(new class implements ToModel, WithHeadingRow, WithValidation {
                public function model(array $row)
                {
                    // Tìm sản phẩm theo SKU, nếu có thì update, không thì tạo mới
                    $product = Product::where('sku', $row['sku'])->first();
                    
                    if ($product) {
                        // Update sản phẩm đã tồn tại
                        $product->update([
                            'name' => $row['name'] ?? $product->name,
                            'description' => $row['description'] ?? $product->description,
                            'import_price' => $row['import_price'] ?? $product->import_price,
                            'selling_price' => $row['selling_price'] ?? $product->selling_price,
                            'category' => $row['category'] ?? $product->category,
                            'brand' => $row['brand'] ?? $product->brand,
                            'barcode' => $row['barcode'] ?? $product->barcode,
                            'stock' => $row['stock'] ?? $product->stock,
                            'image' => $row['image'] ?? $product->image,
                            'volume' => $row['volume'] ?? $product->volume,
                            'concentration' => $row['concentration'] ?? $product->concentration,
                            'origin' => $row['origin'] ?? $product->origin,
                            'import_date' => $row['import_date'] ?? $product->import_date,
                            'sales_channel' => $row['sales_channel'] ?? $product->sales_channel,
                            'tags' => isset($row['tags']) ? self::normalizeTagsStatic($row['tags']) : $product->tags,
                            'ingredients' => isset($row['ingredients']) ? self::normalizeIngredientsStatic($row['ingredients']) : $product->ingredients,
                            'product_type' => $row['product_type'] ?? $product->product_type,
                            'product_form' => $row['product_form'] ?? $product->product_form,
                            'expiry_date' => $row['expiry_date'] ?? $product->expiry_date,
                            'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : $product->is_active,
                            'low_stock_threshold' => $row['low_stock_threshold'] ?? $product->low_stock_threshold,
                        ]);
                        return null; // Không tạo mới
                    } else {
                        // Tạo sản phẩm mới
                        return new Product([
                            'name' => $row['name'],
                            'description' => $row['description'] ?? null,
                            'import_price' => $row['import_price'],
                            'selling_price' => $row['selling_price'],
                            'category' => $row['category'],
                            'brand' => $row['brand'] ?? null,
                            'sku' => $row['sku'],
                            'barcode' => $row['barcode'] ?? null,
                            'stock' => $row['stock'] ?? 0,
                            'image' => $row['image'] ?? null,
                            'volume' => $row['volume'] ?? null,
                            'concentration' => $row['concentration'] ?? null,
                            'origin' => $row['origin'] ?? null,
                            'import_date' => $row['import_date'] ?? null,
                            'sales_channel' => $row['sales_channel'] ?? null,
                            'tags' => isset($row['tags']) ? self::normalizeTagsStatic($row['tags']) : null,
                            'ingredients' => isset($row['ingredients']) ? self::normalizeIngredientsStatic($row['ingredients']) : null,
                            'product_type' => $row['product_type'] ?? null,
                            'product_form' => $row['product_form'] ?? null,
                            'expiry_date' => $row['expiry_date'] ?? null,
                            'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
                            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
                        ]);
                    }
                }

                public function rules(): array
                {
                    return [
                        'name' => 'required',
                        'import_price' => 'required|numeric',
                        'selling_price' => 'required|numeric',
                        'category' => 'required',
                        'sku' => 'required',
                    ];
                }

                public static function normalizeTagsStatic(?string $tags): ?string
                {
                    if (!$tags) return null;
                    $parts = array_filter(array_map('trim', explode(',', $tags)));
                    $parts = array_values(array_unique($parts));
                    return empty($parts) ? null : implode(',', $parts);
                }

                public static function normalizeIngredientsStatic(?string $ingredients): ?string
                {
                    if (!$ingredients) return null;
                    $parts = array_filter(array_map('trim', explode(',', $ingredients)));
                    $parts = array_values(array_unique($parts));
                    return empty($parts) ? null : implode(',', $parts);
                }
            }, $request->file('file'));

            return redirect()->route('products.index')
                ->with('success', 'Import sản phẩm thành công!');

        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Lỗi khi import: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $format = request('format', 'xlsx');
        $filename = 'products_template.' . $format;

        $columns = [
            'name','description','import_price','selling_price','category','brand','sku','barcode','stock','low_stock_threshold','image','volume','concentration','origin','import_date','sales_channel','tags','ingredients','product_type','product_form','expiry_date','is_active'
        ];

        if ($format === 'csv') {
            $callback = function() use ($columns) {
                $handle = fopen('php://output', 'w');
                // BOM cho UTF-8 (Excel Windows hiển thị tiếng Việt đúng)
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $columns);
                Product::select($columns)->orderBy('id')->chunk(500, function($rows) use ($handle) {
                    foreach ($rows as $row) {
                        $data = $row->toArray();
                        // Chuẩn hóa ngày và boolean
                        foreach (['import_date','expiry_date'] as $d) {
                            if (!empty($data[$d])) {
                                $data[$d] = (string) $row->$d?->format('Y-m-d');
                            }
                        }
                        $data['is_active'] = (int) ($data['is_active'] ?? 0);
                        // Đảm bảo thứ tự cột
                        $ordered = [];
                        foreach ($columns as $col) { $ordered[] = $data[$col] ?? ''; }
                        fputcsv($handle, $ordered);
                    }
                });
                fclose($handle);
            };
            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Nếu môi trường không có Concerns (package cũ), fallback sang CSV
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)
            || !interface_exists(\Maatwebsite\Excel\Concerns\FromCollection::class)) {
            // ép xuất CSV thay thế
            $altName = 'products_template.csv';
            $callback = function() use ($columns) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $columns);
                Product::select($columns)->orderBy('id')->chunk(500, function($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        $data = $row->only($columns);
                        foreach (['import_date','expiry_date'] as $d) {
                            if (!empty($data[$d])) { $data[$d] = $row->$d?->format('Y-m-d'); }
                        }
                        $data['is_active'] = (int) ($data['is_active'] ?? 0);
                        $ordered = [];
                        foreach ($columns as $col) { $ordered[] = $data[$col] ?? ''; }
                        fputcsv($handle, $ordered);
                    }
                });
                fclose($handle);
            };
            return response()->streamDownload($callback, $altName, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // Mặc định xuất XLSX qua thư viện Excel (FromCollection để tương thích rộng)
        $export = new class($columns) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private array $cols; 
            public function __construct(array $cols) { $this->cols = $cols; }
            public function collection()
            {
                return Product::query()->get($this->cols)->map(function($row){
                    $data = $row->only($this->cols);
                    foreach (['import_date','expiry_date'] as $d) {
                        if (!empty($data[$d])) { $data[$d] = $row->$d?->format('Y-m-d'); }
                    }
                    $data['is_active'] = (int) ($data['is_active'] ?? 0);
                    return $data;
                });
            }
            public function headings(): array { return $this->cols; }
        };
        return Excel::download($export, $filename);
    }

    private function normalizeTags(?string $tags): ?string
    {
        if (!$tags) {
            return null;
        }
        $parts = array_filter(array_map('trim', explode(',', $tags)));
        $parts = array_values(array_unique($parts));
        return empty($parts) ? null : implode(',', $parts);
    }

    private function normalizeIngredients(?string $ingredients): ?string
    {
        if (!$ingredients) {
            return null;
        }
        $parts = array_filter(array_map('trim', explode(',', $ingredients)));
        $parts = array_values(array_unique($parts));
        return empty($parts) ? null : implode(',', $parts);
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

            if (empty($data['sku']) || empty($data['name']) || empty($data['category'])) {
                continue; // bỏ qua dòng thiếu dữ liệu bắt buộc
            }

            $product = Product::where('sku', $data['sku'])->first();
            $payload = [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'import_price' => isset($data['import_price']) ? (float)$data['import_price'] : null,
                'selling_price' => isset($data['selling_price']) ? (float)$data['selling_price'] : null,
                'category' => $data['category'] ?? null,
                'brand' => $data['brand'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'stock' => isset($data['stock']) ? (int)$data['stock'] : 0,
                'low_stock_threshold' => isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : 5,
                'image' => $data['image'] ?? null,
                'volume' => $data['volume'] ?? null,
                'concentration' => $data['concentration'] ?? null,
                'origin' => $data['origin'] ?? null,
                'import_date' => $data['import_date'] ?? null,
                'sales_channel' => $data['sales_channel'] ?? null,
                'tags' => $this->normalizeTags($data['tags'] ?? null),
                'ingredients' => $this->normalizeIngredients($data['ingredients'] ?? null),
                'product_type' => $data['product_type'] ?? null,
                'product_form' => $data['product_form'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
            ];

            if ($product) {
                $product->update($payload);
            } else {
                $payload['sku'] = $data['sku'];
                Product::create($payload);
            }
        }

        fclose($handle);
    }
}
