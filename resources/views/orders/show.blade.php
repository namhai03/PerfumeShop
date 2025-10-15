@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Chi tiết đơn hàng</h1>
            <p style="color: #718096; margin-top: 8px;">{{ $order->order_number }}</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('orders.edit', ['order' => $order->id, 'return' => request('return') ?? url()->previous()]) }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="{{ request('return') ?: route('orders.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Thông tin đơn hàng -->
        <div class="card">
            <div class="card-header">
                <h3>Thông tin đơn hàng</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Mã đơn hàng</label>
                        <div style="font-weight: 600; color: #2d3748;">{{ $order->order_number }}</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Khách hàng</label>
                        <div style="color: #4a5568;">{{ $order->customer->name ?? $order->customer_name ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <div style="color: #4a5568;">{{ $order->phone ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Loại đơn hàng</label>
                        <div>
                            <span class="badge {{ $order->type_badge_class }}">
                                {{ $order->type_text }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div>
                            <span class="badge {{ $order->status_badge_class }}">
                                {{ $order->status_text }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ngày tạo</label>
                        <div style="color: #4a5568;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ngày đơn hàng</label>
                        <div style="color: #4a5568;">{{ $order->order_date->format('d/m/Y') }}</div>
                    </div>
                    
                    @if($order->delivery_date)
                    <div class="form-group">
                        <label class="form-label">Ngày giao hàng</label>
                        <div style="color: #4a5568;">{{ $order->delivery_date->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($order->delivery_address)
            <div class="form-group">
                <label class="form-label">Địa chỉ giao hàng</label>
                <div style="color: #4a5568; background-color: #f7fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    {{ $order->delivery_address }}
                </div>
            </div>
            @endif
            
            @if($order->payment_method)
            <div class="form-group">
                <label class="form-label">Phương thức thanh toán</label>
                <div style="color: #4a5568;">
                    @switch($order->payment_method)
                        @case('cash')
                            Tiền mặt
                            @break
                        @case('bank_transfer')
                            Chuyển khoản
                            @break
                        @case('credit_card')
                            Thẻ tín dụng
                            @break
                        @default
                            {{ $order->payment_method }}
                    @endswitch
                </div>
            </div>
            @endif
            
            @if($order->notes)
            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <div style="color: #4a5568; background-color: #f7fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    {{ $order->notes }}
                </div>
            </div>
            @endif
        </div>

        <!-- Tổng kết -->
        <div class="card">
            <div class="card-header">
                <h3>Tổng kết</h3>
            </div>
            
            <div class="form-group">
                <label class="form-label">Tổng tiền hàng</label>
                <div style="font-size: 18px; font-weight: 600; color: #2d3748;">
                    {{ number_format($order->total_amount, 0, ',', '.') }} ₫
                </div>
            </div>
            
            @if($order->discount_amount > 0)
            <div class="form-group">
                <label class="form-label">Giảm giá</label>
                <div style="font-size: 16px; font-weight: 600; color: #e53e3e;">
                    -{{ number_format($order->discount_amount, 0, ',', '.') }} ₫
                </div>
            </div>
            @endif
            
            <div class="form-group" style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px;">
                <label class="form-label">Thành tiền</label>
                <div style="font-size: 20px; font-weight: 700; color: #2d3748;">
                    {{ number_format($order->final_amount, 0, ',', '.') }} ₫
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="card">
        <div class="card-header">
            <h3>Sản phẩm trong đơn hàng</h3>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @php
                                        $imgPath = \App\Helpers\ImageHelper::getImageUrl($item->product->image);
                                    @endphp
                                    @if($imgPath)
                                        <img src="{{ $imgPath }}" alt="{{ $item->product->name }}" width="40" height="40" loading="lazy" decoding="async"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div style="width: 40px; height: 40px; background-color: #f8f9fa; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                                            {{ $item->product->name }}
                                        </div>
                                        <div style="font-size: 12px; color: #6c757d;">{{ $item->variant->sku ?? $item->product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2d3748;">
                                    {{ $item->quantity }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2d3748;">
                                    {{ number_format($item->unit_price, 0, ',', '.') }} ₫
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2d3748;">
                                    {{ number_format($item->total_price, 0, ',', '.') }} ₫
                                </div>
                            </td>
                            <td>
                                <div style="color: #4a5568;">
                                    {{ $item->custom_notes ?? '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                <div style="margin-bottom: 16px;">
                                    <i class="fas fa-box" style="font-size: 32px; color: #dee2e6;"></i>
                                </div>
                                <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Không có sản phẩm nào</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 6px;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }

    .card-header {
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .card-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    .table-container {
        overflow-x: auto;
    }

    .table th {
        background-color: #f7fafc;
        color: #4a5568;
        font-weight: 600;
        font-size: 13px;
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .table td {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-outline {
        background: white;
        color: #4299e1;
        border: 1px solid #4299e1;
    }

    .btn-outline:hover {
        background: #4299e1;
        color: white;
    }
</style>
@endpush