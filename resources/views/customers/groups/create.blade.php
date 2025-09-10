@extends('layouts.app')

@section('title', 'Thêm nhóm khách hàng - PerfumeShop')

@section('content')
    <h1 class="page-title">Thêm nhóm khách hàng</h1>
    <div class="card">
        <form action="{{ route('customer-groups.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Tên nhóm</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Chiết khấu (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="discount_rate" class="form-control" placeholder="VD: 5, 10">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Ưu tiên</label>
                    <input type="number" min="0" name="priority" class="form-control" value="0">
                </div>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Đơn tối thiểu áp dụng</label>
                    <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control" placeholder="VD: 500000">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Giới hạn mức giảm tối đa</label>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control" placeholder="VD: 100000">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="3" class="form-control"></textarea>
            </div>
            <div style="display:flex; gap:16px;">
                <label><input type="checkbox" name="is_active" value="1" checked> Kích hoạt</label>
                <label><input type="checkbox" name="is_default" value="1"> Đặt làm nhóm mặc định</label>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('customer-groups.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
@endsection


