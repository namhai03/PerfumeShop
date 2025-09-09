<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $q = $request->get('search');
            $query->where('name', 'like', "%{$q}%")
                  ;
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

        // Thống kê số lượng sản phẩm theo danh mục (nếu đã liên kết)
        $categoryCounts = Category::withCount('products')->pluck('products_count', 'id');

        return view('categories.index', compact('categories', 'categoryCounts'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function show(Category $category)
    {
        $products = $category->products()->orderBy('created_at', 'desc')->paginate(request('per_page', 20));
        $productCount = $category->products()->count();
        
        // Lấy danh sách sản phẩm chưa có trong danh mục này để hiển thị trong modal
        $availableProducts = Product::whereDoesntHave('categories', function($query) use ($category) {
            $query->where('category_id', $category->id);
        })->where('is_active', true)->orderBy('name')->get();
        
        return view('categories.show', compact('category', 'products', 'productCount', 'availableProducts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sales_channel' => 'nullable|in:online,offline',
            'is_active' => 'sometimes|boolean',
        ]);

        // Generate unique slug
        $data['slug'] = $this->generateUniqueSlug(Str::slug($data['name']));

        $category = Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Đã tạo danh mục: ' . $category->name);
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sales_channel' => 'nullable|in:online,offline',
            'is_active' => 'sometimes|boolean',
        ]);
        // Keep current slug base from name but ensure uniqueness excluding current id
        $baseSlug = Str::slug($data['name']);
        $data['slug'] = $this->generateUniqueSlug($baseSlug, $category->id);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        // Prevent delete if category still has products
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Không thể xóa danh mục vì còn sản phẩm liên kết.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Đã xóa danh mục.');
    }

    public function addProduct(Request $request, Category $category)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'note' => 'nullable|string|max:500'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Kiểm tra xem sản phẩm đã có trong danh mục chưa
        if ($category->products()->where('product_id', $product->id)->exists()) {
            return redirect()->back()->with('error', 'Sản phẩm đã có trong danh mục này.');
        }

        // Thêm sản phẩm vào danh mục
        $category->products()->attach($product->id, [
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', "Đã thêm sản phẩm '{$product->name}' vào danh mục.");
    }

    public function removeProduct(Request $request, Category $category)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Xóa sản phẩm khỏi danh mục
        $category->products()->detach($product->id);

        return redirect()->back()->with('success', "Đã xóa sản phẩm '{$product->name}' khỏi danh mục.");
    }

    private function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: 'danh-muc';
        $suffix = 1;
        while (Category::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }
        return $slug;
    }
}


