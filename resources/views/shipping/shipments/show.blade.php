@extends('layouts.app')

@section('title', 'Chi tiết vận đơn - PerfumeShop')

@section('content')
    <div class="shipment-show-page">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h1 class="page-title">Chi tiết vận đơn</h1>
        <div style="display:flex; gap:8px;">
            <form method="POST" action="{{ route('shipments.destroy', $shipment) }}" onsubmit="return confirm('Xóa vận đơn này? Thao tác không thể hoàn tác.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit" style="width:120px; height:40px; display:inline-flex; align-items:center; justify-content:center; padding:0;">Xóa</button>
            </form>
            <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-primary">Sửa vận đơn</a>
            <a href="{{ route('shipments.index') }}" class="btn btn-outline">Quay lại</a>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 class="card-title">Thông tin chung</h3></div>
        <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div><strong>Mã đơn hàng:</strong> {{ $shipment->order_code }}</div>
            <div><strong>Mã vận đơn:</strong> {{ $shipment->tracking_code }}</div>
            <div><strong>Hãng vận chuyển:</strong> {{ $shipment->carrier }}</div>
            <div><strong>Trạng thái:</strong> {{ $shipment->status }}</div>
            <div><strong>COD:</strong> {{ number_format($shipment->cod_amount,0) }}đ</div>
            <div><strong>Phí vận chuyển:</strong> {{ number_format($shipment->shipping_fee,0) }}đ</div>
            <div><strong>Cập nhật:</strong> {{ ($shipment->updated_at ?? $shipment->created_at)->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h3 class="card-title">Đơn hàng liên kết</h3></div>
        <div class="card-body">
            @if($shipment->order)
                <a href="{{ route('orders.show', $shipment->order->id) }}">{{ $shipment->order->order_number }}</a> - {{ $shipment->order->customer_name ?? 'N/A' }} - {{ $shipment->order->status_text ?? $shipment->order->status }}
            @else
                <p style="color:#94a3b8;">Chưa có liên kết đơn hàng.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Người nhận</h3></div>
        <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div><strong>Họ tên:</strong> {{ $shipment->recipient_name }}</div>
            <div><strong>SĐT:</strong> {{ $shipment->recipient_phone }}</div>
            <div style="grid-column: span 2;"><strong>Địa chỉ:</strong> {{ $shipment->address_line }}, {{ $shipment->ward }}, {{ $shipment->province }}</div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-header"><h3 class="card-title">Lịch sử</h3></div>
        <div class="card-body">
            <ul>
                @forelse(($shipment->events ?? []) as $ev)
                    <li>{{ $ev->event_at?->format('d/m/Y H:i') }} - {{ $ev->status }} - {{ $ev->note }}</li>
                @empty
                    <li>Chưa có lịch sử</li>
                @endforelse
            </ul>
        </div>
    </div>
    </div>
@endsection


