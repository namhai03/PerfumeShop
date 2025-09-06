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
                    <tr>
                        <td>{{ $promotion->code ?? '-' }}</td>
                        <td class="product-name">{{ $promotion->name }}</td>
                        <td>{{ $promotion->type }}</td>
                        <td>{{ $promotion->scope }}</td>
                        <td>
                            @if($promotion->type === 'percent')
                                {{ (float)$promotion->discount_value }}%
                            @elseif($promotion->type === 'fixed_amount')
                                {{ number_format((float)$promotion->discount_value, 0) }}
                            @else
                                Miễn phí vận chuyển
                            @endif
                        </td>
                        <td>{{ optional($promotion->start_at)->format('d/m/Y') }} - {{ optional($promotion->end_at)->format('d/m/Y') }}</td>
                        <td>{{ $promotion->isCurrentlyActive() ? 'Đang hoạt động' : 'Không hoạt động' }}</td>
                        <td class="text-right">{{ (int)($usageStats[$promotion->id] ?? 0) }}</td>
                        <td class="text-right">{{ number_format((float)($discountTotals[$promotion->id] ?? 0), 0) }}</td>
                        <td class="actions">
                            <a href="{{ route('promotions.show', $promotion) }}" class="btn btn-outline"><i class="fas fa-eye"></i> Xem</a>
                            <a href="{{ route('promotions.edit', $promotion) }}" class="btn"><i class="fas fa-edit"></i> Sửa</a>
                            <form action="{{ route('promotions.destroy', $promotion) }}" method="POST" style="display:inline" onsubmit="return confirm('Xóa CTKM?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button>
                            </form>
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
@endsection


