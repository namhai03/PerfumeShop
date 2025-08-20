@extends('layouts.app')

@section('title', 'Chi tiết tài khoản - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.accounts.index') }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Chi tiết tài khoản</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.accounts.edit', $account->id) }}" class="btn btn-outline">Sửa</a>
            <a href="{{ route('cashbook.accounts.index') }}" class="btn btn-primary">Quay lại</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Thông tin tài khoản</h3>
        </div>
        
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Tên tài khoản:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $account->name }}</div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Loại tài khoản:</label>
                <div style="font-size: 16px; color: #2c3e50;">
                    @if($account->type == 'cash')
                        Tiền mặt
                    @elseif($account->type == 'bank')
                        Ngân hàng
                    @else
                        Khác
                    @endif
                </div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Trạng thái:</label>
                <div style="font-size: 16px; color: #2c3e50;">
                    {{ $account->is_active ? 'Hoạt động' : 'Ngừng' }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Số dư:</label>
                <div style="font-size: 18px; font-weight: 600; color: #2c3e50;">
                    {{ number_format((float)$account->balance, 0, ',', '.') }} đ
                </div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Số tài khoản:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $account->account_number ?? '-' }}</div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Ngân hàng:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $account->bank_name ?? '-' }}</div>
            </div>
        </div>

        @if($account->description)
        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Mô tả:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $account->description }}</div>
        </div>
        @endif

        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Ngày tạo:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $account->created_at?->format('d/m/Y H:i') }}</div>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Cập nhật lần cuối:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $account->updated_at?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
@endsection
