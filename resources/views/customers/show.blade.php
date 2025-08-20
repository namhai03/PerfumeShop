@extends('layouts.app')

@section('title', 'Chi tiết khách hàng - PerfumeShop')

@section('content')
    <h1 class="page-title">Chi tiết khách hàng</h1>

    <div class="card" style="margin-bottom: 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
            <h3 style="font-size:18px; font-weight:600;">{{ $customer->name }}</h3>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Xóa khách hàng?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding:6px 10px; font-size:12px;">Xóa</button>
                </form>
            </div>
        </div>
        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:16px;">
            <div>
                <div><strong>SĐT:</strong> {{ $customer->phone ?? '-' }}</div>
                <div><strong>Email:</strong> {{ $customer->email ?? '-' }}</div>
                <div><strong>Giới tính:</strong> {{ $customer->gender ?? '-' }}</div>
                <div><strong>Ngày sinh:</strong> {{ $customer->birthday?->format('d/m/Y') ?? '-' }}</div>
            </div>
            <div>
                <div><strong>Địa chỉ:</strong> {{ $customer->address ?? '-' }}</div>
                <div><strong>Khu vực:</strong> {{ $customer->ward }} - {{ $customer->district }} - {{ $customer->city }}</div>
                <div><strong>Loại KH:</strong> {{ $customer->customer_type ?? '-' }}</div>
                <div><strong>Nhóm KH:</strong> {{ $customer->group?->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex; gap:16px;">
            <div><strong>Tổng đơn:</strong> {{ $customer->total_orders }}</div>
            <div><strong>Tổng chi tiêu:</strong> {{ number_format((float)$customer->total_spent, 0, ',', '.') }} đ</div>
            <div><strong>Trạng thái:</strong> {{ $customer->is_active ? 'Hoạt động' : 'Ngừng' }}</div>
            <div><strong>Ngày tạo:</strong> {{ $customer->created_at?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
@endsection


