@extends('layouts.app')

@section('title', 'Thêm danh mục - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title">Thêm danh mục</h1>
        <a href="{{ route('categories.index') }}" class="btn btn-outline">Quay lại</a>
    </div>

    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Tên danh mục</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Loại danh mục</label>
                <select name="type" class="form-control">
                    <option value="manual" {{ old('type','manual')=='manual'?'selected':'' }}>Thủ công</option>
                    <option value="smart" {{ old('type')=='smart'?'selected':'' }}>Thông minh</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kênh bán hàng</label>
                <select name="sales_channel" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="online" {{ old('sales_channel')=='online'?'selected':'' }}>Online</option>
                    <option value="offline" {{ old('sales_channel')=='offline'?'selected':'' }}>Offline</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Điều kiện (cho danh mục thông minh)</label>
                <textarea name="conditions" class="form-control" rows="4" placeholder='JSON điều kiện, ví dụ: {"category":"Nước hoa nam"}'>{{ old('conditions') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ old('is_active',1)==1?'selected':'' }}>Đang dùng</option>
                    <option value="0" {{ old('is_active')==='0'?'selected':'' }}>Ngừng</option>
                </select>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('categories.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
@endsection


