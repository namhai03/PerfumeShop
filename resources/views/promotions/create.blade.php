@extends('layouts.app')

@section('content')
<h1 class="page-title">Tạo chương trình khuyến mại</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin-left:16px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('promotions.store') }}" method="POST">
        @csrf
    <div class="card">
        <div class="card-header"><h3>Thông tin chung</h3></div>
        <div class="form-group">
            <label class="form-label">Mã (tuỳ chọn)</label>
            <input type="text" name="code" value="{{ old('code') }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Tên</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Loại</label>
            <select name="type" class="form-control">
                <option value="percent" {{ old('type')==='percent' ? 'selected' : '' }}>Phần trăm</option>
                <option value="fixed_amount" {{ old('type')==='fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                <option value="free_shipping" {{ old('type')==='free_shipping' ? 'selected' : '' }}>Miễn phí vận chuyển</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Phạm vi</label>
            <select name="scope" class="form-control">
                <option value="order" {{ old('scope')==='order' ? 'selected' : '' }}>Toàn đơn</option>
                <option value="product" {{ old('scope')==='product' ? 'selected' : '' }}>Theo sản phẩm</option>
            </select>
        </div>
        <div class="card">
            <div class="card-header"><h3>Điều kiện & Phạm vi</h3></div>
            <div class="form-group">
                <label class="form-label">Giá trị giảm</label>
                <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Giảm tối đa</label>
                <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Đơn tối thiểu</label>
                <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Số lượng SP tối thiểu</label>
                <input type="number" name="min_items" value="{{ old('min_items') }}" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">ID sản phẩm áp dụng (CSV)</label>
                <input type="text" name="applicable_product_ids[]" placeholder="1,2,3" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">ID danh mục áp dụng (CSV)</label>
                <input type="text" name="applicable_category_ids[]" placeholder="1,2,3" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">ID nhóm KH áp dụng (CSV)</label>
                <input type="text" name="applicable_customer_group_ids[]" placeholder="1,2" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Kênh bán hàng (online,offline)</label>
                <input type="text" name="applicable_sales_channels[]" placeholder="online,offline" class="form-control">
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Thiết lập</h3></div>
            <div class="form-group">
                <label class="form-label">Stack nhiều CTKM</label>
                <input type="checkbox" name="is_stackable" value="1" {{ old('is_stackable') ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label class="form-label">Độ ưu tiên</label>
                <input type="number" name="priority" value="{{ old('priority', 0) }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Bắt đầu</label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Kết thúc</label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Kích hoạt</label>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label class="form-label">Giới hạn tổng lượt dùng</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Giới hạn theo khách</label>
                <input type="number" name="usage_limit_per_customer" value="{{ old('usage_limit_per_customer') }}" class="form-control">
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('promotions.index') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
    </div>
</form>
@endsection


