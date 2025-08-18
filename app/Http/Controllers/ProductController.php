<?php

namespace App\Http\Controllers;

use App\Models\Product;
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

        // Lọc theo kênh bán hàng (sales_channel)
        if ($request->filled('sales_channel')) {
            $query->where('sales_channel', $request->sales_channel);
        }

        // Lọc theo loại sản phẩm (category)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Lọc theo tag
        if ($request->filled('tag')) {
            $query->where('tags', 'like', "%{$request->tag}%");
        }

        // Lọc theo trạng thái có thể bán
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Lọc theo nhãn hiệu
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Lọc theo ngày tạo
        if ($request->filled('created_date')) {
            $query->whereDate('created_date', $request->created_date);
        }

        // Lọc theo loại sản phẩm (product_type)
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        // Lọc theo hình thức sản phẩm (product_form)
        if ($request->filled('product_form')) {
            $query->where('product_form', $request->product_form);
        }

        // Lọc theo sản phẩm lô - HSD
        if ($request->filled('has_expiry')) {
            if ($request->has_expiry == '1') {
                $query->whereNotNull('expiry_date');
            } else {
                $query->whereNull('expiry_date');
            }
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Phân trang
        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        // Lấy danh sách các giá trị duy nhất cho filter
        $categories = Product::distinct()->pluck('category')->filter()->values();
        $brands = Product::distinct()->pluck('brand')->filter()->values();
        $salesChannels = Product::distinct()->pluck('sales_channel')->filter()->values();
        $tags = Product::distinct()->pluck('tags')->filter()->values();
        $productTypes = Product::distinct()->pluck('product_type')->filter()->values();
        $productForms = Product::distinct()->pluck('product_form')->filter()->values();

        return view('products.index', compact(
            'products', 
            'categories', 
            'brands', 
            'salesChannels', 
            'tags',
            'productTypes',
            'productForms'
        ));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'import_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:255',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'volume' => 'nullable|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'import_date' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'import_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:255',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'volume' => 'nullable|string|max:255',
            'concentration' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'import_date' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
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
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        try {
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
                            'stock' => $row['stock'] ?? $product->stock,
                            'image' => $row['image'] ?? $product->image,
                            'volume' => $row['volume'] ?? $product->volume,
                            'concentration' => $row['concentration'] ?? $product->concentration,
                            'origin' => $row['origin'] ?? $product->origin,
                            'import_date' => $row['import_date'] ?? $product->import_date,
                            'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : $product->is_active,
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
                            'stock' => $row['stock'] ?? 0,
                            'image' => $row['image'] ?? null,
                            'volume' => $row['volume'] ?? null,
                            'concentration' => $row['concentration'] ?? null,
                            'origin' => $row['origin'] ?? null,
                            'import_date' => $row['import_date'] ?? null,
                            'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true,
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
                        'sku' => 'required|unique:products,sku',
                    ];
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
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function collection()
            {
                return Product::all();
            }

            public function headings(): array
            {
                return [
                    'ID',
                    'Tên sản phẩm',
                    'Mô tả',
                    'Giá nhập',
                    'Giá bán',
                    'Danh mục',
                    'Thương hiệu',
                    'SKU',
                    'Tồn kho',
                    'Hình ảnh',
                    'Dung tích',
                    'Nồng độ',
                    'Xuất xứ',
                    'Ngày nhập hàng',
                    'Trạng thái',
                    'Ngày tạo',
                    'Ngày cập nhật'
                ];
            }
        }, 'products.xlsx');
    }
}
