@extends('layouts.app')

@section('title', 'Chỉnh sửa sản phẩm - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">Chỉnh sửa sản phẩm</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <div class="card">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Danh mục *</label>
                        <select id="category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                            <option value="">Chọn danh mục</option>
                            <option value="Nước hoa nam" {{ old('category', $product->category) == 'Nước hoa nam' ? 'selected' : '' }}>Nước hoa nam</option>
                            <option value="Nước hoa nữ" {{ old('category', $product->category) == 'Nước hoa nữ' ? 'selected' : '' }}>Nước hoa nữ</option>
                            <option value="Nước hoa unisex" {{ old('category', $product->category) == 'Nước hoa unisex' ? 'selected' : '' }}>Nước hoa unisex</option>
                            <option value="Nước hoa trẻ em" {{ old('category', $product->category) == 'Nước hoa trẻ em' ? 'selected' : '' }}>Nước hoa trẻ em</option>
                        </select>
                        @error('category')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand" class="form-label">Thương hiệu</label>
                        <input type="text" id="brand" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand', $product->brand) }}">
                        @error('brand')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">Mã SKU *</label>
                        <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                        @error('sku')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="import_price" class="form-label">Giá nhập (VNĐ) *</label>
                        <input type="number" id="import_price" name="import_price" class="form-control @error('import_price') is-invalid @enderror" value="{{ old('import_price', $product->import_price) }}" min="0" step="1000" required>
                        @error('import_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="selling_price" class="form-label">Giá bán (VNĐ) *</label>
                        <input type="number" id="selling_price" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $product->selling_price) }}" min="0" step="1000" required>
                        @error('selling_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock" class="form-label">Số lượng tồn kho *</label>
                        <input type="number" id="stock" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" min="0" required>
                        @error('stock')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="volume" class="form-label">Dung tích (ml)</label>
                        <input type="text" id="volume" name="volume" class="form-control @error('volume') is-invalid @enderror" value="{{ old('volume', $product->volume) }}" placeholder="VD: 50ml, 100ml">
                        @error('volume')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="concentration" class="form-label">Nồng độ</label>
                        <input type="text" id="concentration" name="concentration" class="form-control @error('concentration') is-invalid @enderror" value="{{ old('concentration', $product->concentration) }}" placeholder="VD: EDP, EDT, EDC">
                        @error('concentration')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="import_date" class="form-label">Ngày nhập hàng</label>
                        <input type="date" id="import_date" name="import_date" class="form-control @error('import_date') is-invalid @enderror" value="{{ old('import_date', $product->import_date ? $product->import_date->format('Y-m-d') : '') }}">
                        @error('import_date')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="origin" class="form-label">Xuất xứ</label>
                        <input type="text" id="origin" name="origin" class="form-control @error('origin') is-invalid @enderror" value="{{ old('origin', $product->origin) }}" placeholder="VD: Pháp, Ý, Mỹ">
                        @error('origin')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                        @if($product->image)
                            <div style="margin-bottom: 12px;">
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ecef;">
                            </div>
                        @endif
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', $product->is_active) == '1' ? 'checked' : '' }}>
                                Hoạt động
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="0" {{ old('is_active', $product->is_active) == '0' ? 'checked' : '' }}>
                                Không hoạt động
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">Hủy</a>
                <button type="submit" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                    <i class="fas fa-save"></i>
                    Cập nhật sản phẩm
                </button>
            </div>
        </form>
    </div>
@endsection
