@extends('layouts.app')

@section('title', 'Thêm danh mục - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title">Thêm danh mục</h1>
        <a href="{{ route('categories.index') }}" class="btn btn-outline">Quay lại</a>
    </div>

    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:16px;">
                    <ul style="margin:0 0 0 16px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-group" style="display:flex; gap:16px;">
                <div style="flex:2;">
                    <label class="form-label">Tên danh mục</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div style="flex:1;">
                    <label class="form-label">Trạng thái</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active',1)==1?'selected':'' }}>Đang dùng</option>
                        <option value="0" {{ old('is_active')==='0'?'selected':'' }}>Ngừng</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn về danh mục">{{ old('description') }}</textarea>
            </div>
            

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <a href="{{ route('categories.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
    <script>(function(){})();</script>
@endsection


