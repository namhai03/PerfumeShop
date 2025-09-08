@extends('layouts.app')

@section('title', 'Chi tiết danh mục - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 class="page-title">{{ $category->name }}</h1>
            <div style="color:#6c757d;"> Trạng thái: {{ $category->is_active ? 'Đang dùng' : 'Ngừng' }}</div>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="{{ route('categories.edit', ['category' => $category->id]) }}" class="btn btn-outline" style="width:120px; height:40px; display:inline-flex; align-items:center; justify-content:center; padding:0; text-align:center; pointer-events:auto; position:relative; z-index:2;">Sửa</a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Xóa danh mục này?')" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="width:120px; height:40px; display:inline-flex; align-items:center; justify-content:center; padding:0;">Xóa</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <h3>Thông tin danh mục</h3>
        </div>
        <div>
            <div class="form-group" style="display:flex; gap:16px;">
                <div style="flex:1;">
                    <label class="form-label">Mô tả</label>
                    <div style="padding:12px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; min-height:48px;">{{ $category->description ?: '—' }}</div>
                </div>
                <div style="width:160px;">
                    <label class="form-label">Ảnh</label>
                    @if(!empty($category->image))
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" style="width:160px; height:160px; object-fit:cover; border:1px solid #e2e8f0; border-radius:8px;">
                    @else
                        <div style="width:160px; height:160px; background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#6c757d;">—</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Sản phẩm trong danh mục ({{ $productCount }})</h3>
            <a href="{{ route('products.index', ['category' => $category->id]) }}" class="btn btn-outline">Xem tất cả trong trang Sản phẩm</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>SKU</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ number_format($product->selling_price, 0, ',', '.') }} đ</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                <span class="px-2 py-1 rounded-md text-xs font-medium {{ $product->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $product->is_active ? 'Đang bán' : 'Không bán' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:#6c757d;">Chưa có sản phẩm nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:20px; border-top:1px solid #e2e8f0;">
            <div style="color:#6c757d; font-size:14px;">
                Từ {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} trên tổng {{ $products->total() }}
            </div>
            <div class="display-options" style="display:flex; align-items:center; gap:16px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:14px; color:#4a5568;">Hiển thị</span>
                    <form method="GET">
                        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                    <span style="font-size:14px; color:#4a5568;">Kết quả</span>
                </div>
                <div class="pagination-controls">{{ $products->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
@endsection


