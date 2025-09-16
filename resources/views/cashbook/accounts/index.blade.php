@extends('layouts.app')

@section('title', 'Tài khoản - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Tài khoản</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.accounts.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-plus"></i>
                Thêm tài khoản
            </a>
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
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

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên tài khoản</th>
                        <th>Loại</th>
                        <th>Số tài khoản</th>
                        <th>Ngân hàng</th>
                        <th>Số dư</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50;">{{ $account->name }}</div>
                                @if($account->description)
                                    <div style="font-size: 12px; color: #718096;">{{ $account->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; 
                                    {{ $account->type == 'cash' ? 'background-color: #dcfce7; color: #166534' : 
                                       ($account->type == 'bank' ? 'background-color: #dbeafe; color: #1e40af' : 'background-color: #f3e8ff; color: #7c3aed') }}">
                                    {{ $account->type == 'cash' ? 'Tiền mặt' : 
                                       ($account->type == 'bank' ? 'Ngân hàng' : 'Khác') }}
                                </span>
                            </td>
                            <td>{{ $account->account_number ?? '-' }}</td>
                            <td>{{ $account->bank_name ?? '-' }}</td>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50;">
                                    {{ number_format((float)$account->balance, 0, ',', '.') }} đ
                                </div>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-md text-xs font-medium {{ $account->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $account->is_active ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('cashbook.accounts.edit', $account->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                                <form action="{{ route('cashbook.accounts.destroy', $account->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Xóa tài khoản?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" style="padding:6px 10px; font-size:12px;">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr style="height: 400px;">
                            <td colspan="7" style="text-align: center; vertical-align: middle; padding: 0; color: #6c757d;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px;">
                                    <div style="margin-bottom: 16px;">
                                        <i class="fas fa-university" style="font-size: 48px; color: #dee2e6;"></i>
                                    </div>
                                    <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Chưa có tài khoản nào</div>
                                    <div style="font-size: 14px; color: #6c757d;">Thêm tài khoản tiền mặt hoặc ngân hàng để bắt đầu.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
            {{ $accounts->links('vendor.pagination.perfume') }}
        </div>
    </div>
@endsection
