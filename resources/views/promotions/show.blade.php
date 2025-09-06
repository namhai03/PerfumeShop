@extends('layouts.app')

@section('content')
<h1 class="page-title">{{ $promotion->name }}</h1>

<div class="card">
    <div class="card-header">
        <h3>Thông tin chương trình</h3>
        <a href="{{ route('promotions.edit', $promotion) }}" class="btn"><i class="fas fa-edit"></i> Sửa</a>
    </div>
    <div class="grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:14px;">
        <div><strong>Mã:</strong> {{ $promotion->code ?? '-' }}</div>
        <div><strong>Loại:</strong> {{ $promotion->type }}</div>
        <div><strong>Phạm vi:</strong> {{ $promotion->scope }}</div>
        <div><strong>Giá trị:</strong>
            @if($promotion->type==='percent')
                {{ (float)$promotion->discount_value }}%
            @elseif($promotion->type==='fixed_amount')
                {{ number_format((float)$promotion->discount_value,0) }}
            @else
                Miễn phí vận chuyển
            @endif
        </div>
        <div><strong>Hiệu lực:</strong> {{ optional($promotion->start_at)->format('d/m/Y H:i') }} - {{ optional($promotion->end_at)->format('d/m/Y H:i') }}</div>
        <div><strong>Trạng thái:</strong> {{ $promotion->isCurrentlyActive() ? 'Đang hoạt động' : 'Không hoạt động' }}</div>
    </div>
    <div style="margin-top:12px;">{{ $promotion->description }}</div>
    </div>

<div class="card">
    <div class="card-header"><h3>Lịch sử áp dụng</h3></div>
    <table class="table">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Mã đơn</th>
                <th class="text-right">Giảm</th>
                <th>Khách hàng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usages as $usage)
                <tr>
                    <td>{{ $usage->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $usage->order_code ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float)$usage->discount_amount,0) }}</td>
                    <td>{{ optional($usage->customer)->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state-title">Chưa có lượt áp dụng</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-controls">{{ $usages->links('vendor.pagination.perfume') }}</div>
</div>
@endsection


