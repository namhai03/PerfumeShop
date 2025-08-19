@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">Thêm sản phẩm mới</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <div class="card">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục (gán nhiều)</label>
                        <select name="categories[]" class="form-control" multiple size="6">
                            @foreach(($categories ?? []) as $cat)
                                <option value="{{ $cat->id }}" {{ (collect(old('categories'))->contains($cat->id)) ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color:#6c757d;">Giữ Ctrl/Cmd để chọn nhiều danh mục.</small>
                        @error('categories')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand" class="form-label">Thương hiệu</label>
                        <input type="text" id="brand" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand') }}">
                        @error('brand')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">Mã SKU *</label>
                        <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required>
                        @error('sku')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="import_price" class="form-label">Giá nhập (VNĐ) *</label>
                        <input type="number" id="import_price" name="import_price" class="form-control @error('import_price') is-invalid @enderror" value="{{ old('import_price') }}" min="0" step="1000" required>
                        @error('import_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="selling_price" class="form-label">Giá bán (VNĐ) *</label>
                        <input type="number" id="selling_price" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" min="0" step="1000" required>
                        @error('selling_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock" class="form-label">Số lượng tồn kho *</label>
                        <input type="number" id="stock" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                        @error('stock')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="low_stock_threshold" class="form-label">Ngưỡng cảnh báo sắp hết</label>
                        <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', 5) }}" min="0">
                        @error('low_stock_threshold')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="volume" class="form-label">Dung tích (ml)</label>
                        <input type="text" id="volume" name="volume" class="form-control @error('volume') is-invalid @enderror" value="{{ old('volume') }}" placeholder="VD: 50ml, 100ml">
                        @error('volume')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="concentration" class="form-label">Nồng độ</label>
                        <input type="text" id="concentration" name="concentration" class="form-control @error('concentration') is-invalid @enderror" value="{{ old('concentration') }}" placeholder="VD: EDP, EDT, EDC">
                        @error('concentration')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tags" class="form-label">Tag (nhiều, cách nhau bằng dấu phẩy)</label>
                        <input type="text" id="tags" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags') }}" placeholder="VD: top, new, sale">
                        @error('tags')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="import_date" class="form-label">Ngày nhập hàng</label>
                        <input type="date" id="import_date" name="import_date" class="form-control @error('import_date') is-invalid @enderror" value="{{ old('import_date') }}">
                        @error('import_date')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="origin" class="form-label">Xuất xứ</label>
                        <input type="text" id="origin" name="origin" class="form-control @error('origin') is-invalid @enderror" value="{{ old('origin') }}" placeholder="VD: Pháp, Ý, Mỹ">
                        @error('origin')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <small style="color:#6c757d;">Ảnh sẽ được lưu trong thư mục storage/public/products</small>
                        @error('image')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                Hoạt động
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
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
                    Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-generate SKU if empty
    document.getElementById('name').addEventListener('input', function() {
        const skuField = document.getElementById('sku');
        if (!skuField.value) {
            const name = this.value.toLowerCase()
                .replace(/[^a-z0-9]/g, '')
                .substring(0, 8);
            const timestamp = Date.now().toString().slice(-4);
            skuField.value = name + timestamp;
        }
    });
</script>
@endpush
