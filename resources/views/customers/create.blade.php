@extends('layouts.app')

@section('title', 'Thêm khách hàng - PerfumeShop')

@section('content')
    <h1 class="page-title">Thêm khách hàng</h1>

    <div class="card">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Tên khách hàng</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-control">
                        <option value="">-- Chọn --</option>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="birthday" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control" placeholder="Số nhà, đường">
            </div>
            <div style="display:flex; gap:12px;">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Tỉnh/Thành phố</label>
                    <input type="text" name="city" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Quận/Huyện</label>
                    <input type="text" name="district" class="form-control">
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Phường/Xã</label>
                    <input type="text" name="ward" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nhóm khách hàng</label>
                <select name="customer_group_id" class="form-control">
                    <option value="">-- Không chọn --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Công ty</label>
                <input type="text" name="company" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <textarea name="note" rows="3" class="form-control"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
@endsection


