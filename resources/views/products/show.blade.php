@extends('layouts.app')

@section('title', $product->name . ' - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">{{ $product->name }}</h1>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px;">
        <!-- Product Image -->
        <div class="card">
            <div style="text-align: center;">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 100%; max-width: 300px; height: auto; border-radius: 12px;">
                @else
                    <div style="width: 300px; height: 300px; background-color: #f8f9fa; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #6c757d; margin: 0 auto;">
                        <i class="fas fa-image" style="font-size: 64px;"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="card">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">Thông tin sản phẩm</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div>
                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Tên sản phẩm</label>
                        <div style="color: #2c3e50;">{{ $product->name }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Mã SKU</label>
                        <div style="color: #2c3e50; font-family: monospace;">{{ $product->sku }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Danh mục</label>
                        <div style="color: #2c3e50;">{{ $product->category }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Thương hiệu</label>
                        <div style="color: #2c3e50;">{{ $product->brand ?? 'N/A' }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Giá nhập</label>
                        <div style="color: #dc3545; font-size: 18px; font-weight: 600;">
                            {{ number_format($product->import_price, 0, ',', '.') }} VNĐ
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Giá bán</label>
                        <div style="color: #28a745; font-size: 20px; font-weight: 600;">
                            {{ number_format($product->selling_price, 0, ',', '.') }} VNĐ
                        </div>
                    </div>
                </div>

                <div>
                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Tồn kho</label>
                        <div style="color: #2c3e50;">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $product->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->stock }} sản phẩm
                        </span>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Dung tích</label>
                        <div style="color: #2c3e50;">{{ $product->volume ?? 'N/A' }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Nồng độ</label>
                        <div style="color: #2c3e50;">{{ $product->concentration ?? 'N/A' }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Xuất xứ</label>
                        <div style="color: #2c3e50;">{{ $product->origin ?? 'N/A' }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Ngày nhập hàng</label>
                        <div style="color: #2c3e50;">{{ $product->import_date ? $product->import_date->format('d/m/Y') : 'N/A' }}</div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #495057; display: block; margin-bottom: 4px;">Trạng thái</label>
                        <div style="color: #2c3e50;">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($product->description)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <label style="font-weight: 500; color: #4a5568; display: block; margin-bottom: 8px;">Mô tả</label>
                    <div style="color: #2d3748; line-height: 1.6;">{{ $product->description }}</div>
                </div>
            @endif

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <label style="font-weight: 500; color: #4a5568; display: block; margin-bottom: 8px;">Thông tin bổ sung</label>
                <div style="color: #718096; font-size: 13px;">
                    <div>Ngày tạo: {{ $product->created_at->format('d/m/Y H:i') }}</div>
                    <div>Cập nhật lần cuối: {{ $product->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 28px; display: flex; gap: 12px; justify-content: center;">
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px; min-width: 140px; justify-content: center;">
            <i class="fas fa-edit"></i>
            Chỉnh sửa
        </a>
        <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" style="font-size: 13px; padding: 8px 16px; min-width: 140px; justify-content: center;">
                <i class="fas fa-trash"></i>
                Xóa sản phẩm
            </button>
        </form>
    </div>
@endsection
