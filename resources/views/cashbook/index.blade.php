@extends('layouts.app')

@section('title', 'Sổ quỹ - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Sổ quỹ</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-plus"></i>
                Tạo phiếu
            </a>
            <a href="{{ route('cashbook.accounts.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-university"></i>
                Tài khoản
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

    <!-- Tab Navigation removed as yêu cầu: chỉ giữ danh sách + bộ lọc -->

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('cashbook.index') }}" id="filterForm">
        <div class="card">
            <div class="search-filter-section" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text" name="search" placeholder="Tìm theo mã phiếu, mô tả, người nộp" value="{{ request('search') }}" style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <select name="status" class="filter-select">
                        <option value="">Trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="filter-select" placeholder="Từ ngày">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="filter-select" placeholder="Đến ngày">
                </div>
            </div>
        </div>
    </form>

    <!-- Vouchers Table -->
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Loại</th>
                        <th>Số tiền</th>
                        <th>Mô tả</th>
                        <th>Người nộp</th>
                        <th>Tài khoản</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50;">{{ $voucher->voucher_code }}</div>
                                <div style="font-size: 12px; color: #718096;">{{ $voucher->reference ?? '-' }}</div>
                            </td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; background-color: #e3f2fd; color: #1976d2;">
                                    {{ $voucher->type_text }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50; {{ $voucher->type == 'receipt' ? 'color: #16a34a' : 'color: #dc2626' }}">
                                    {{ $voucher->type == 'receipt' ? '+' : '-' }}{{ number_format((float)$voucher->amount, 0, ',', '.') }} đ
                                </div>
                            </td>
                            <td class="product-name">
                                <div style="font-weight: 500; color: #4a5568;">{{ $voucher->description }}</div>
                                @if($voucher->reason)
                                    <div style="font-size: 12px; color: #718096;">{{ $voucher->reason }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #4a5568;">{{ $voucher->payer_name ?? '-' }}</div>
                                <div style="font-size: 12px; color: #718096;">{{ $voucher->payer_group ?? '-' }}</div>
                            </td>
                            <td>
                                @if($voucher->type == 'transfer')
                                    <div style="font-size: 12px; color: #718096;">Từ: {{ $voucher->fromAccount?->name ?? '-' }}</div>
                                    <div style="font-size: 12px; color: #718096;">Đến: {{ $voucher->toAccount?->name ?? '-' }}</div>
                                @else
                                    <div style="font-size: 14px; color: #4a5568;">{{ $voucher->fromAccount?->name ?? $voucher->toAccount?->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #4a5568;">{{ $voucher->transaction_date?->format('d/m/Y') }}</div>
                                <div style="font-size: 12px; color: #718096;">{{ $voucher->created_at?->format('H:i') }}</div>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-md text-xs font-medium 
                                    {{ $voucher->status == 'approved' ? 'bg-green-50 text-green-700' : 
                                       ($voucher->status == 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">
                                    {{ $voucher->status_text }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('cashbook.show', $voucher->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Xem</a>
                                <a href="{{ route('cashbook.edit', $voucher->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:40px; color:#6c757d;">
                                <div style="margin-bottom: 16px;">
                                    <i class="fas fa-wallet" style="font-size: 32px; color: #dee2e6;"></i>
                                </div>
                                <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Cửa hàng của bạn chưa có phiếu nào</div>
                                <div style="font-size: 14px;">Tạo mới phiếu thu chi của bạn.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <div class="pagination-info" style="color: #6c757d; font-size: 14px;">
                Từ {{ $vouchers->firstItem() ?? 0 }} đến {{ $vouchers->lastItem() ?? 0 }} trên tổng {{ $vouchers->total() }}
            </div>
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: #4a5568;">Hiển thị</span>
                    <select form="filterForm" name="per_page" class="per-page-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="font-size: 14px; color: #4a5568;">Kết quả</span>
                </div>
                <div class="pagination-controls">
                    {{ $vouchers->links('vendor.pagination.perfume') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const filterSelects = document.querySelectorAll('.filter-select');
        const filterForm = document.getElementById('filterForm');
        
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function(){
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => { filterForm.submit(); }, 500);
            });
        }
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function(){ filterForm.submit(); });
        });
    });
</script>
@endpush
