@extends('layouts.app')

@section('title', 'Tạo chương trình khuyến mại - PerfumeShop')

@section('content')
<div class="promotion-create-page">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title">Tạo chương trình khuyến mại</h1>
        <div class="breadcrumb">
            <a href="{{ route('promotions.index') }}" class="text-blue-600 hover:text-blue-800">Danh sách khuyến mại</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">Tạo mới</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="font-medium">Có lỗi xảy ra:</span>
            </div>
            <ul class="mt-2 ml-6 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('promotions.store') }}" method="POST" id="promotionForm">
        @csrf
        
        <!-- Thông tin cơ bản -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Thông tin cơ bản
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">
                            Mã khuyến mại
                            <span class="text-gray-500 text-sm">(Tùy chọn)</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code') }}" 
                               class="form-control" placeholder="VD: SUMMER2024">
                        <small class="form-text">Để trống sẽ tự động tạo mã</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Tên chương trình</label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               required class="form-control" placeholder="VD: Giảm giá mùa hè">
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Mô tả chi tiết về chương trình khuyến mại...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Loại và phạm vi khuyến mại -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tags mr-2"></i>
                    Loại và phạm vi khuyến mại
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label required">Loại khuyến mại</label>
                        <select name="type" class="form-control" id="promotionType" onchange="updateDiscountFields()">
                            <option value="percent" {{ old('type')==='percent' ? 'selected' : '' }}>Giảm theo phần trăm</option>
                            <option value="fixed_amount" {{ old('type')==='fixed_amount' ? 'selected' : '' }}>Giảm số tiền cố định</option>
                            <option value="free_shipping" {{ old('type')==='free_shipping' ? 'selected' : '' }}>Miễn phí vận chuyển</option>
                            <option value="buy_x_get_y" {{ old('type')==='buy_x_get_y' ? 'selected' : '' }}>Mua X tặng Y</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Phạm vi áp dụng</label>
                        <select name="scope" class="form-control">
                            <option value="order" {{ old('scope')==='order' ? 'selected' : '' }}>Toàn bộ đơn hàng</option>
                            <option value="product" {{ old('scope')==='product' ? 'selected' : '' }}>Theo sản phẩm cụ thể</option>
                            <option value="category" {{ old('scope')==='category' ? 'selected' : '' }}>Theo danh mục</option>
                        </select>
                    </div>
                </div>
                
                <!-- Giá trị giảm giá -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4" id="discountFields">
                    <div class="form-group">
                        <label class="form-label required">Giá trị giảm</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value') }}" 
                                   class="form-control" placeholder="0" required>
                            <span class="input-group-text" id="discountUnit">%</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Giảm tối đa</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount') }}" 
                                   class="form-control" placeholder="0">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="form-text">Để trống = không giới hạn</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Đơn hàng tối thiểu</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount') }}" 
                                   class="form-control" placeholder="0">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Điều kiện áp dụng -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Điều kiện áp dụng
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Số lượng sản phẩm tối thiểu</label>
                        <input type="number" name="min_items" value="{{ old('min_items') }}" 
                               class="form-control" placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kênh bán hàng</label>
                        <select name="applicable_sales_channels[]" class="form-control" multiple>
                            <option value="online" {{ in_array('online', old('applicable_sales_channels', [])) ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ in_array('offline', old('applicable_sales_channels', [])) ? 'selected' : '' }}>Offline</option>
                        </select>
                        <small class="form-text">Chọn nhiều kênh bằng Ctrl+Click</small>
                    </div>
                </div>
                
                <!-- Sản phẩm/Danh mục áp dụng -->
                <div class="mt-4">
                    <div class="form-group">
                        <label class="form-label">Sản phẩm áp dụng</label>
                        <input type="text" name="applicable_product_ids[]" 
                               placeholder="Nhập ID sản phẩm, cách nhau bằng dấu phẩy (VD: 1,2,3)" 
                               class="form-control">
                        <small class="form-text">Để trống = áp dụng tất cả sản phẩm</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Danh mục áp dụng</label>
                        <input type="text" name="applicable_category_ids[]" 
                               placeholder="Nhập ID danh mục, cách nhau bằng dấu phẩy (VD: 1,2,3)" 
                               class="form-control">
                        <small class="form-text">Để trống = áp dụng tất cả danh mục</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nhóm khách hàng áp dụng</label>
                        <input type="text" name="applicable_customer_group_ids[]" 
                               placeholder="Nhập ID nhóm KH, cách nhau bằng dấu phẩy (VD: 1,2)" 
                               class="form-control">
                        <small class="form-text">Để trống = áp dụng tất cả khách hàng</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thiết lập thời gian và giới hạn -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog mr-2"></i>
                    Thiết lập thời gian và giới hạn
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Thời gian bắt đầu</label>
                        <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" 
                               class="form-control">
                        <small class="form-text">Để trống = bắt đầu ngay</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thời gian kết thúc</label>
                        <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" 
                               class="form-control">
                        <small class="form-text">Để trống = không giới hạn thời gian</small>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="form-group">
                        <label class="form-label">Giới hạn tổng lượt sử dụng</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" 
                               class="form-control" placeholder="0">
                        <small class="form-text">Để trống = không giới hạn</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Giới hạn theo khách hàng</label>
                        <input type="number" name="usage_limit_per_customer" value="{{ old('usage_limit_per_customer') }}" 
                               class="form-control" placeholder="0">
                        <small class="form-text">Để trống = không giới hạn</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Độ ưu tiên</label>
                        <input type="number" name="priority" value="{{ old('priority', 0) }}" 
                               class="form-control" placeholder="0">
                        <small class="form-text">Số càng cao = ưu tiên càng cao</small>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_stackable" value="1" 
                                   {{ old('is_stackable') ? 'checked' : '' }} class="mr-2">
                            <span>Cho phép kết hợp với khuyến mại khác</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }} class="mr-2">
                            <span>Kích hoạt ngay</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye mr-2"></i>
                    Xem trước khuyến mại
                </h3>
            </div>
            <div class="card-body">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-sm text-gray-600 mb-2">Mã khuyến mại:</div>
                    <div class="font-mono text-lg font-bold text-blue-600" id="previewCode">SUMMER2024</div>
                    
                    <div class="text-sm text-gray-600 mt-3 mb-1">Mô tả:</div>
                    <div class="text-gray-800" id="previewDescription">Giảm giá mùa hè</div>
                    
                    <div class="text-sm text-gray-600 mt-3 mb-1">Điều kiện:</div>
                    <div class="text-gray-800" id="previewConditions">Đơn hàng từ 500,000 VNĐ</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('promotions.index') }}" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Tạo khuyến mại
            </button>
        </div>
    </form>
</div>

<style>
/* Promotion Create Page Styles - Đồng nhất với layout chung */
.promotion-create-page .form-label.required::after {
    content: " *";
    color: #e53e3e;
}

.promotion-create-page .input-group {
    display: flex;
    align-items: stretch;
}

.promotion-create-page .input-group .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

.promotion-create-page .input-group-text {
    background-color: #f7fafc;
    border: 1px solid #e2e8f0;
    border-left: none;
    color: #718096;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
}

.promotion-create-page .grid {
    display: grid;
    gap: 20px;
}

.promotion-create-page .grid-cols-1 {
    grid-template-columns: 1fr;
}

.promotion-create-page .grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

.promotion-create-page .grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 768px) {
    .promotion-create-page .grid-cols-2,
    .promotion-create-page .grid-cols-3 {
        grid-template-columns: 1fr;
    }
}

.promotion-create-page .mb-6 {
    margin-bottom: 24px;
}

.promotion-create-page .mt-4 {
    margin-top: 16px;
}

.promotion-create-page .space-x-4 > * + * {
    margin-left: 16px;
}

.promotion-create-page .space-x-6 > * + * {
    margin-left: 24px;
}

.promotion-create-page .flex {
    display: flex;
}

.promotion-create-page .items-center {
    align-items: center;
}

.promotion-create-page .justify-end {
    justify-content: flex-end;
}

.promotion-create-page .text-blue-600 {
    color: #4299e1;
}

.promotion-create-page .text-gray-400 {
    color: #a0aec0;
}

.promotion-create-page .text-gray-500 {
    color: #718096;
}

.promotion-create-page .text-gray-600 {
    color: #718096;
}

.promotion-create-page .text-gray-800 {
    color: #2d3748;
}

.promotion-create-page .text-sm {
    font-size: 12px;
}

.promotion-create-page .font-medium {
    font-weight: 500;
}

.promotion-create-page .font-bold {
    font-weight: 600;
}

.promotion-create-page .font-mono {
    font-family: 'Courier New', monospace;
}

.promotion-create-page .text-lg {
    font-size: 18px;
}

.promotion-create-page .mx-2 {
    margin-left: 8px;
    margin-right: 8px;
}

.promotion-create-page .mr-2 {
    margin-right: 8px;
}

.promotion-create-page .mb-2 {
    margin-bottom: 8px;
}

.promotion-create-page .mb-1 {
    margin-bottom: 4px;
}

.promotion-create-page .mt-3 {
    margin-top: 12px;
}

.promotion-create-page .mt-2 {
    margin-top: 8px;
}

.promotion-create-page .ml-6 {
    margin-left: 24px;
}

.promotion-create-page .bg-gray-50 {
    background-color: #f7fafc;
}

.promotion-create-page .p-4 {
    padding: 16px;
}

.promotion-create-page .rounded-lg {
    border-radius: 8px;
}

.promotion-create-page .hover\:text-blue-800:hover {
    color: #2b6cb0;
}

.promotion-create-page .hover\:underline:hover {
    text-decoration: underline;
}

.promotion-create-page .list-disc {
    list-style-type: disc;
}

.promotion-create-page .breadcrumb a {
    text-decoration: none;
    color: #4299e1;
}

.promotion-create-page .breadcrumb a:hover {
    text-decoration: underline;
    color: #2b6cb0;
}
</style>

<script>
function updateDiscountFields() {
    const type = document.getElementById('promotionType').value;
    const unit = document.getElementById('discountUnit');
    const discountValue = document.querySelector('input[name="discount_value"]');
    
    switch(type) {
        case 'percent':
            unit.textContent = '%';
            discountValue.placeholder = '0';
            break;
        case 'fixed_amount':
            unit.textContent = 'VNĐ';
            discountValue.placeholder = '0';
            break;
        case 'free_shipping':
            unit.textContent = '';
            discountValue.placeholder = '0';
            discountValue.value = '0';
            discountValue.readOnly = true;
            break;
        case 'buy_x_get_y':
            unit.textContent = 'SP';
            discountValue.placeholder = '0';
            break;
    }
}

// Update preview
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('promotionForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', updatePreview);
    });
    
    function updatePreview() {
        const code = document.querySelector('input[name="code"]').value || 'SUMMER2024';
        const name = document.querySelector('input[name="name"]').value || 'Giảm giá mùa hè';
        const description = document.querySelector('textarea[name="description"]').value || 'Mô tả khuyến mại';
        const minOrder = document.querySelector('input[name="min_order_amount"]').value || '0';
        
        document.getElementById('previewCode').textContent = code;
        document.getElementById('previewDescription').textContent = name + ' - ' + description;
        document.getElementById('previewConditions').textContent = 
            minOrder > 0 ? `Đơn hàng từ ${parseInt(minOrder).toLocaleString('vi-VN')} VNĐ` : 'Không có điều kiện';
    }
    
    updatePreview();
});
</script>
@endsection


