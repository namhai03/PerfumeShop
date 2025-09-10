@extends('layouts.app')

@section('title', 'Nhóm khách hàng - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title">Nhóm khách hàng</h1>
        <a href="{{ route('customer-groups.create') }}" class="btn btn-primary" style="font-size:13px; padding:8px 16px;">
            <i class="fas fa-plus"></i> Thêm nhóm
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Tên nhóm</th>
                    <th>Trạng thái</th>
                    <th>Mặc định</th>
                    <th>Chiết khấu (%)</th>
                    <th>Mô tả</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $g)
                    <tr>
                        <td><a href="{{ route('customer-groups.show', $g->id) }}" class="link" style="text-decoration:none;">{{ $g->name }}</a></td>
                        <td>{!! $g->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge">Inactive</span>' !!}</td>
                        <td>{!! $g->is_default ? '<span class="badge badge-primary">Default</span>' : '-' !!}</td>
                        <td>{{ $g->discount_rate ?? '-' }}</td>
                        <td>{{ $g->description ?? '-' }}</td>
                        <td class="actions">
                            <a href="{{ route('customer-groups.edit', $g->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px; height:28px;">Sửa</a>
                            <form action="{{ route('customer-groups.destroy', $g->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Xóa nhóm khách hàng?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" style="padding:6px 10px; font-size:12px; height:28px;">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#6c757d;">Chưa có nhóm khách hàng</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            {{ $groups->links('vendor.pagination.perfume') }}
        </div>
    </div>
@endsection


