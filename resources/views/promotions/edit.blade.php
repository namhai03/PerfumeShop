@extends('layouts.app')

@section('content')
<h1 class="page-title">Sửa chương trình khuyến mại</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin-left:16px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('promotions.update', $promotion) }}" method="POST">
        @csrf
        @method('PUT')
    <div class="card">
        <div class="card-header"><h3>Thông tin chung</h3></div>
        <div class="form-group">
            <label class="form-label">Mã (tuỳ chọn)</label>
            <input type="text" name="code" value="{{ old('code', $promotion->code) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Tên</label>
            <input type="text" name="name" value="{{ old('name', $promotion->name) }}" required class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $promotion->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Loại</label>
            <select name="type" class="form-control">
                <option value="percent" {{ old('type', $promotion->type)==='percent' ? 'selected' : '' }}>Phần trăm</option>
                <option value="fixed_amount" {{ old('type', $promotion->type)==='fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                <option value="free_shipping" {{ old('type', $promotion->type)==='free_shipping' ? 'selected' : '' }}>Miễn phí vận chuyển</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Phạm vi</label>
            <select name="scope" class="form-control">
                <option value="order" {{ old('scope', $promotion->scope)==='order' ? 'selected' : '' }}>Toàn đơn</option>
                <option value="product" {{ old('scope', $promotion->scope)==='product' ? 'selected' : '' }}>Theo sản phẩm</option>
            </select>
        </div>
    <div class="card">
        <div class="card-header"><h3>Điều kiện & Phạm vi</h3></div>
        <div class="form-group">
            <label class="form-label">Giá trị giảm</label>
            <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value', $promotion->discount_value) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Giảm tối đa</label>
            <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount', $promotion->max_discount_amount) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Đơn tối thiểu</label>
            <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $promotion->min_order_amount) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Số lượng SP tối thiểu</label>
            <input type="number" name="min_items" value="{{ old('min_items', $promotion->min_items) }}" class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">ID sản phẩm áp dụng (CSV)</label>
            <input type="text" value="{{ implode(',', (array)($promotion->applicable_product_ids ?? [])) }}" class="form-control" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">ID danh mục áp dụng (CSV)</label>
            <input type="text" value="{{ implode(',', (array)($promotion->applicable_category_ids ?? [])) }}" class="form-control" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">ID nhóm KH áp dụng (CSV)</label>
            <input type="text" value="{{ implode(',', (array)($promotion->applicable_customer_group_ids ?? [])) }}" class="form-control" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">Kênh bán hàng (CSV)</label>
            <input type="text" value="{{ implode(',', (array)($promotion->applicable_sales_channels ?? [])) }}" class="form-control" disabled>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Thiết lập</h3></div>
        <div class="form-group">
            <label class="form-label">Stack nhiều CTKM</label>
            <input type="checkbox" name="is_stackable" value="1" {{ old('is_stackable', $promotion->is_stackable) ? 'checked' : '' }}>
        </div>
        <div class="form-group">
            <label class="form-label">Độ ưu tiên</label>
            <input type="number" name="priority" value="{{ old('priority', $promotion->priority) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Bắt đầu</label>
            <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($promotion->start_at)->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Kết thúc</label>
            <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($promotion->end_at)->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Kích hoạt</label>
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
        </div>
        <div class="form-group">
            <label class="form-label">Giới hạn tổng lượt dùng</label>
            <input type="number" name="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit) }}" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Giới hạn theo khách</label>
            <input type="number" name="usage_limit_per_customer" value="{{ old('usage_limit_per_customer', $promotion->usage_limit_per_customer) }}" class="form-control">
        </div>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
        <a href="{{ route('promotions.index') }}" class="btn btn-outline">Hủy</a>
        <button type="submit" class="btn btn-primary">Lưu</button>
    </div>
 </form>
@endsection


