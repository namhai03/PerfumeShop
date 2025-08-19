<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $q = $request->get('search');
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('sales_channel')) {
            $query->where('sales_channel', $request->get('sales_channel'));
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:manual,smart,system',
            'sales_channel' => 'nullable|in:online,offline',
            'conditions' => 'nullable',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        if ($request->filled('conditions') && is_string($request->conditions)) {
            $decoded = json_decode($request->conditions, true);
            $data['conditions'] = $decoded ?: null;
        }

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
            'type' => 'nullable|in:manual,smart,system',
            'sales_channel' => 'nullable|in:online,offline',
            'conditions' => 'nullable',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);
        if ($request->filled('conditions') && is_string($request->conditions)) {
            $decoded = json_decode($request->conditions, true);
            $data['conditions'] = $decoded ?: null;
        }
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Đã xóa danh mục.');
    }
}


