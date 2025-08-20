@extends('layouts.app')

@section('title', 'Thêm tài khoản - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.accounts.index') }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Thêm tài khoản</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.accounts.index') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" form="accountForm" class="btn btn-primary">Thêm tài khoản</button>
        </div>
    </div>

    <form id="accountForm" action="{{ route('cashbook.accounts.store') }}" method="POST">
        @csrf
        
        <div class="card">
            <div class="card-header">
                <h3>Thông tin tài khoản</h3>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Tên tài khoản*</label>
                    <input type="text" name="name" class="form-control" placeholder="Nhập tên tài khoản" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Loại tài khoản*</label>
                    <select name="type" class="form-control" required>
                        <option value="">Chọn loại</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="bank">Ngân hàng</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Số tài khoản</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Nhập số tài khoản">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Tên ngân hàng</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="Nhập tên ngân hàng">
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Số dư ban đầu</label>
                    <input type="number" name="balance" class="form-control" placeholder="Nhập số dư" value="0" min="0" step="0.01">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Trạng thái</label>
                    <div style="margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="is_active" value="1" checked> Hoạt động
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Nhập mô tả tài khoản"></textarea>
            </div>
        </div>
    </form>
@endsection
