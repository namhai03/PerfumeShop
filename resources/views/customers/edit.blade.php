@extends('layouts.app')

@section('title', 'Sửa khách hàng - PerfumeShop')

@section('content')
    <h1 class="page-title">Sửa khách hàng</h1>

    <div class="card">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Tên khách hàng</label>
                <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $customer->email }}">
                </div>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-control">
                        <option value="" {{ $customer->gender==='' ? 'selected' : '' }}>-- Chọn --</option>
                        <option value="male" {{ $customer->gender==='male' ? 'selected' : '' }}>Nam</option>
                        <option value="female" {{ $customer->gender==='female' ? 'selected' : '' }}>Nữ</option>
                        <option value="other" {{ $customer->gender==='other' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control" value="{{ $customer->birthday?->format('Y-m-d') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control" value="{{ $customer->address }}" placeholder="Số nhà, đường">
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Tỉnh/Thành phố</label>
                    <input type="text" name="city" class="form-control" value="{{ $customer->city }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Quận/Huyện</label>
                    <input type="text" name="district" class="form-control" value="{{ $customer->district }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Phường/Xã</label>
                    <input type="text" name="ward" class="form-control" value="{{ $customer->ward }}">
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Loại khách hàng</label>
                    <input type="text" name="customer_type" class="form-control" value="{{ $customer->customer_type }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Nhóm khách hàng</label>
                    <select name="customer_group_id" class="form-control">
                        <option value="">-- Không chọn --</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ $customer->customer_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Nguồn</label>
                    <input type="text" name="source" class="form-control" value="{{ $customer->source }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">MST</label>
                    <input type="text" name="tax_number" class="form-control" value="{{ $customer->tax_number }}">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Công ty</label>
                    <input type="text" name="company" class="form-control" value="{{ $customer->company }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <textarea name="note" rows="3" class="form-control">{{ $customer->note }}</textarea>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="is_active" value="1" {{ $customer->is_active ? 'checked' : '' }}> Đang hoạt động
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
@endsection


