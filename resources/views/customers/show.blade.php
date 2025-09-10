@extends('layouts.app')

@section('title', 'Chi tiết khách hàng - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Chi tiết khách hàng</h1>

        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('customers.index') }}" class="btn btn-outline" style="padding:8px 12px; font-size:13px;">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline" style="padding:8px 12px; font-size:13px;">Sửa</a>
            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Xóa khách hàng và các thông tin liên quan?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="padding:8px 12px; font-size:13px;">Xóa</button>
            </form>
        </div>
    </div>

    <div class="card customer-card">
        <div class="customer-header">
            <div class="customer-name">{{ $customer->name }}</div>
            <span class="status-pill {{ $customer->is_active ? 'pill-active' : 'pill-inactive' }}">{{ $customer->is_active ? 'Hoạt động' : 'Ngừng' }}</span>
        </div>

        <div class="customer-grid">
            <div class="info-block">
                <div class="info-row"><span class="label">SĐT</span><span class="value">{{ $customer->phone ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Email</span><span class="value">{{ $customer->email ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Giới tính</span><span class="value">{{ $customer->gender ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Ngày sinh</span><span class="value">{{ $customer->birthday?->format('d/m/Y') ?? '-' }}</span></div>
            </div>
            <div class="info-block">
                <div class="info-row"><span class="label">Địa chỉ</span><span class="value">{{ $customer->address ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Khu vực</span><span class="value">{{ $customer->ward ?: '-' }} {{ $customer->district ? ' - ' . $customer->district : '' }} {{ $customer->city ? ' - ' . $customer->city : '' }}</span></div>
                <div class="info-row"><span class="label">Nhóm KH</span><span class="value">{{ $customer->group?->name ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    <div class="card stats-card">
        <div class="stat"><div class="stat-label">Tổng đơn</div><div class="stat-value">{{ $customer->total_orders }}</div></div>
        <div class="divider"></div>
        <div class="stat"><div class="stat-label">Tổng chi tiêu</div><div class="stat-value">{{ number_format((float)$customer->total_spent, 0, ',', '.') }} đ</div></div>
        <div class="divider"></div>
        <div class="stat"><div class="stat-label">Ngày tạo</div><div class="stat-value">{{ $customer->created_at?->format('d/m/Y H:i') }}</div></div>
    </div>

    <div class="card">
        <div class="card-header" style="margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">
            <h3 style="font-size:16px; font-weight:600; color:#2d3748; margin:0;">Đơn hàng của khách</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->orders()->orderByDesc('created_at')->limit(10)->get() as $order)
                        <tr>
                            <td><a href="{{ route('orders.show', $order->id) }}" style="text-decoration:none; color:#2b6cb0;">{{ $order->order_number }}</a></td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span class="px-2 py-1 rounded-md text-xs font-medium {{ $order->status_badge_class }}">{{ $order->status_text }}</span></td>
                            <td>{{ number_format((float)$order->total_amount, 0, ',', '.') }} đ</td>
                            <td style="font-weight:600; color:#2d3748;">{{ number_format((float)$order->final_amount, 0, ',', '.') }} đ</td>
                            <td><a class="btn btn-outline" href="{{ route('orders.show', $order->id) }}" style="padding:6px 10px; font-size:12px;">Xem</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:#6c757d; padding:24px;">Khách hàng chưa có đơn hàng.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .subtle-text { color:#718096; margin-top:6px; font-size:13px; }
    .customer-card { padding: 20px; }
    .customer-header { display:flex; align-items:center; justify-content: space-between; margin-bottom: 12px; }
    .customer-name { font-size: 20px; font-weight: 700; color:#2d3748; }
    .status-pill { padding:6px 10px; border-radius: 999px; font-size:12px; font-weight:600; }
    .pill-active { background:#e6fffa; color:#047857; }
    .pill-inactive { background:#fff5f5; color:#c53030; }
    .customer-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .info-block { background:#f9fafb; border:1px solid #e2e8f0; border-radius:8px; padding:12px; }
    .info-row { display:flex; align-items:center; margin-bottom:10px; }
    .info-row:last-child { margin-bottom:0; }
    .label { width:120px; color:#4a5568; font-size:13px; }
    .value { color:#2d3748; font-weight:500; }
    .stats-card { display:flex; align-items:center; gap:16px; justify-content:flex-start; }
    .stat { min-width: 180px; }
    .stat-label { color:#718096; font-size:12px; margin-bottom:6px; }
    .stat-value { font-weight:700; color:#2d3748; }
    .divider { height:24px; width:1px; background:#e2e8f0; }
    @media (max-width: 768px) {
        .customer-grid { grid-template-columns: 1fr; }
        .divider { display:none; }
    }
</style>
@endpush


