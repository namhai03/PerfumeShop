@extends('layouts.app')

@section('title', 'Sửa danh mục - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title">Sửa danh mục</h1>
        <a href="{{ route('categories.index') }}" class="btn btn-outline">Quay lại</a>
    </div>

    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    <ul style="margin:0 0 0 16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-group">
                <label class="form-label">Tên danh mục</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', (int)$category->is_active)==1?'selected':'' }}>Đang dùng</option>
                    <option value="0" {{ old('is_active', (int)$category->is_active)==0?'selected':'' }}>Ngừng</option>
                </select>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('categories.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
    <script>(function(){})();</script>
@endsection


