@extends('layouts.app')

@section('title', 'Chi tiết danh mục - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <a href="{{ route('categories.index') }}" class="btn btn-outline" style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; padding:0; text-align:center; pointer-events:auto; position:relative; z-index:2;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>
                <h1 class="page-title">{{ $category->name }}</h1>
            </div>
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
            <button onclick="openAddProductModal()" class="btn btn-primary" style="width: 140px; height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; border-radius: 6px; font-weight: 500; transition: all 0.2s ease; white-space: nowrap;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Thêm sản phẩm
            </button>
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
                        <th>Hành động</th>
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
                            <td>
                                <form action="{{ route('categories.remove-product', $category->id) }}" method="POST" onsubmit="return confirm('Xóa sản phẩm khỏi danh mục này?')" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" style="width:80px; height:32px; display:inline-flex; align-items:center; justify-content:center; padding:0; font-size:12px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                            <path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                        Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:24px; color:#6c757d;">Chưa có sản phẩm nào.</td>
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

    <!-- Modal thêm sản phẩm vào danh mục -->
    <div id="addProductModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 0; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Thêm sản phẩm vào danh mục</h3>
                <span class="close" onclick="closeAddProductModal()" style="color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="addProductForm" action="{{ route('categories.add-product', $category->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Chọn sản phẩm</label>
                        <select name="product_id" id="productSelect" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($availableProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                </form>
            </div>
            <div class="modal-footer" style="padding: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeAddProductModal()" class="btn btn-outline" style="width: 100px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">Hủy</button>
                <button type="button" onclick="submitAddProductForm()" class="btn btn-primary" style="width: 120px; height: 40px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">Thêm sản phẩm</button>
            </div>
        </div>
    </div>

    <script>
        function openAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').style.display = 'none';
            document.getElementById('addProductForm').reset();
        }

        function submitAddProductForm() {
            const productId = document.getElementById('productSelect').value;
            if (!productId) {
                alert('Vui lòng chọn sản phẩm');
                return;
            }
            document.getElementById('addProductForm').submit();
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addProductModal');
            if (event.target == modal) {
                closeAddProductModal();
            }
        }
    </script>
@endsection


