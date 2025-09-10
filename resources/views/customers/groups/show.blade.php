@extends('layouts.app')

@section('title', 'Chi tiết nhóm khách hàng - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title">Nhóm: {{ $group->name }}</h1>
        <div>
            <a href="{{ route('customer-groups.edit', $group->id) }}" class="btn btn-outline" style="font-size:12px; padding:6px 10px;">Sửa</a>
            <a href="{{ route('customer-groups.index') }}" class="btn" style="font-size:12px; padding:6px 10px;">Quay lại</a>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px;">
            <div><div class="text-muted">Trạng thái</div><div>{!! $group->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge">Inactive</span>' !!}</div></div>
            <div><div class="text-muted">Mặc định</div><div>{!! $group->is_default ? '<span class="badge badge-primary">Default</span>' : '-' !!}</div></div>
            <div><div class="text-muted">Chiết khấu (%)</div><div>{{ $group->discount_rate ?? '-' }}</div></div>
            <div><div class="text-muted">Đơn tối thiểu áp dụng</div><div>{{ $group->min_order_amount ?? '-' }}</div></div>
            <div><div class="text-muted">Giới hạn mức giảm tối đa</div><div>{{ $group->max_discount_amount ?? '-' }}</div></div>
            <div style="grid-column: 1 / -1;"><div class="text-muted">Mô tả</div><div>{{ $group->description ?? '-' }}</div></div>
        </div>
    </div>

    <h2 class="page-title" style="font-size:18px; margin-bottom:12px;">Khách hàng thuộc nhóm</h2>
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{!! $c->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge">Inactive</span>' !!}</td>
                        <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                        <td class="actions">
                            <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#6c757d;">Chưa có khách hàng trong nhóm</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            {{ $customers->links('vendor.pagination.perfume') }}
        </div>
    </div>
@endsection


