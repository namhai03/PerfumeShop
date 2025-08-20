@extends('layouts.app')

@section('title', 'Sửa nhóm khách hàng - PerfumeShop')

@section('content')
    <h1 class="page-title">Sửa nhóm khách hàng</h1>
    <div class="card">
        <form action="{{ route('customer-groups.update', $group->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Tên nhóm</label>
                <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Chiết khấu (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="discount_rate" class="form-control" value="{{ $group->discount_rate }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Ưu tiên</label>
                    <input type="number" min="0" name="priority" class="form-control" value="{{ $group->priority }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="3" class="form-control">{{ $group->description }}</textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('customer-groups.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
@endsection


