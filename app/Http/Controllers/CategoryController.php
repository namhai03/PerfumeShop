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
        return view('categories.show', compact('category', 'products', 'productCount'));
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


