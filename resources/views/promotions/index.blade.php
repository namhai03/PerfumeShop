@extends('layouts.app')

@section('content')
<h1 class="page-title">Chương trình khuyến mại</h1>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Danh sách</h3>
        <a href="{{ route('promotions.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tạo mới</a>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Loại</th>
                    <th>Phạm vi</th>
                    <th>Giá trị</th>
                    <th>Hiệu lực</th>
                    <th>Trạng thái</th>
                    <th class="text-right">Lượt áp dụng</th>
                    <th class="text-right">Tổng giảm</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promotion)
                    <tr class="promotion-row" onclick="viewPromotion({{ $promotion->id }})" style="cursor: pointer;">
                        <td>
                            <span class="promotion-code">{{ $promotion->code ?? '-' }}</span>
                        </td>
                        <td class="product-name">
                            <div class="promotion-name">{{ $promotion->name }}</div>
                            <div class="promotion-description">{{ Str::limit($promotion->description, 50) }}</div>
                        </td>
                        <td>
                            <span class="promotion-type-badge promotion-type-{{ $promotion->type }}">
                                @switch($promotion->type)
                                    @case('percent')
                                        Phần trăm
                                        @break
                                    @case('fixed_amount')
                                        Số tiền
                                        @break
                                    @case('free_shipping')
                                        Miễn phí ship
                                        @break
                                    @case('buy_x_get_y')
                                        Mua X tặng Y
                                        @break
                                    @default
                                        {{ $promotion->type }}
                                @endswitch
                            </span>
                        </td>
                        <td>
                            <span class="promotion-scope-badge">
                                @switch($promotion->scope)
                                    @case('order')
                                        Toàn đơn
                                        @break
                                    @case('product')
                                        Theo SP
                                        @break
                                    @case('category')
                                        Theo DM
                                        @break
                                    @default
                                        {{ $promotion->scope }}
                                @endswitch
                            </span>
                        </td>
                        <td>
                            @if($promotion->type === 'percent')
                                <span class="discount-value">{{ (float)$promotion->discount_value }}%</span>
                            @elseif($promotion->type === 'fixed_amount')
                                <span class="discount-value">{{ number_format((float)$promotion->discount_value, 0) }} VNĐ</span>
                            @elseif($promotion->type === 'free_shipping')
                                <span class="discount-value">Miễn phí</span>
                            @elseif($promotion->type === 'buy_x_get_y')
                                <span class="discount-value">Mua {{ $promotion->discount_value }} tặng {{ $promotion->max_discount_amount }}</span>
                            @else
                                <span class="discount-value">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="promotion-dates">
                                @if($promotion->start_at)
                                    <div class="date-item">
                                        <i class="fas fa-play-circle" style="color: #059669; margin-right: 4px;"></i>
                                        {{ $promotion->start_at->format('d/m/Y') }}
                                    </div>
                                @endif
                                @if($promotion->end_at)
                                    <div class="date-item">
                                        <i class="fas fa-stop-circle" style="color: #dc2626; margin-right: 4px;"></i>
                                        {{ $promotion->end_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($promotion->isCurrentlyActive())
                                <span class="badge badge-active">Đang hoạt động</span>
                            @else
                                <span class="badge badge-inactive">Không hoạt động</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="usage-count">{{ (int)($usageStats[$promotion->id] ?? 0) }}</span>
                        </td>
                        <td class="text-right">
                            <span class="discount-total">{{ number_format((float)($discountTotals[$promotion->id] ?? 0), 0) }} VNĐ</span>
                        </td>
                        <td class="actions" onclick="event.stopPropagation();">
                            <div class="order-actions">
                                <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-outline btn-sm" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('promotions.destroy', $promotion) }}" method="POST" style="display:inline" onsubmit="return confirm('Xóa chương trình khuyến mại này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-gift"></i></div>
                                <div class="empty-state-title">Chưa có chương trình khuyến mại</div>
                                <div class="empty-state-actions">
                                    <a href="{{ route('promotions.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tạo chương trình</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-controls">{{ $promotions->links('vendor.pagination.perfume') }}</div>
    
</div>

<script>
function viewPromotion(promotionId) {
    window.location.href = `/promotions/${promotionId}`;
}
</script>

<style>
/* Simple Promotion Table Styles */
.promotion-row:hover {
    background-color: #f7fafc;
}

.promotion-code {
    font-weight: 600;
    color: #2d3748;
}

.promotion-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.promotion-description {
    font-size: 12px;
    color: #718096;
}

.promotion-type-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.promotion-type-percent {
    background-color: #dbeafe;
    color: #1e40af;
}

.promotion-type-fixed_amount {
    background-color: #fef3c7;
    color: #d97706;
}

.promotion-type-free_shipping {
    background-color: #d1fae5;
    color: #059669;
}

.promotion-type-buy_x_get_y {
    background-color: #e0e7ff;
    color: #3730a3;
}

.promotion-scope-badge {
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 10px;
    background-color: #f3f4f6;
    color: #6b7280;
}

.discount-value {
    font-weight: 600;
}

.promotion-dates {
    font-size: 12px;
}

.date-item {
    margin-bottom: 2px;
    color: #4a5568;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-active {
    background-color: #d1fae5;
    color: #059669;
}

.badge-inactive {
    background-color: #fef2f2;
    color: #dc2626;
}

.usage-count {
    font-weight: 600;
}

.discount-total {
    font-weight: 600;
    color: #059669;
}

/* Simple Actions */
.order-actions {
    display: flex;
    gap: 8px;
}

.order-actions .btn {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid;
}

.order-actions .btn-outline {
    background-color: white;
    color: #4299e1;
    border-color: #4299e1;
}

.order-actions .btn-outline:hover {
    background: #4299e1;
    color: white;
}

.order-actions .btn-danger {
    background: #e53e3e;
    color: white;
    border-color: #e53e3e;
}

.order-actions .btn-danger:hover {
    background: #c53030;
    border-color: #c53030;
}
</style>
@endsection


