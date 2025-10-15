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
            <div class="search-filter-section">
                <!-- Search Bar -->
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text"
                               name="search"
                               placeholder="Tìm theo mã phiếu, mô tả, người nộp"
                               value="{{ request('search') }}"
                               style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                    <select name="type" class="filter-select">
                        <option value="">Loại phiếu</option>
                        <option value="receipt" {{ request('type') == 'receipt' ? 'selected' : '' }}>Phiếu thu</option>
                        <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Phiếu chi</option>
                    </select>
                    <select name="status" class="filter-select">
                        <option value="">Trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                    

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 13px; color: #4a5568; font-weight: 500; white-space: nowrap;">Từ ngày:</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="filter-select" style="min-width: 150px;">
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 13px; color: #4a5568; font-weight: 500; white-space: nowrap;">Đến ngày:</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="filter-select" style="min-width: 150px;">
                    </div>

                    <button type="submit" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                        <i class="fas fa-filter"></i>
                        Lọc
                    </button>

                    @if(request('type') || request('status') || request('account_id') || request('from_date') || request('to_date') || request('search'))
                        <a href="{{ route('cashbook.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px; color: #e53e3e;">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    @endif
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
                        <th>Người gửi</th>
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
                                @if($voucher->type == 'receipt')
                                    <div style="font-weight: 600; color: #16a34a;">
                                        +{{ number_format((float)$voucher->amount, 0, ',', '.') }} đ
                                    </div>
                                @else
                                    <div style="font-weight: 600; color: #dc2626;">
                                        -{{ number_format((float)$voucher->amount, 0, ',', '.') }} đ
                                    </div>
                                @endif
                            </td>
                            <td class="product-name">
                                <div style="font-weight: 500; color: #4a5568;">{{ $voucher->description }}</div>
                                @if($voucher->reason)
                                    <div style="font-size: 12px; color: #718096;">{{ $voucher->reason }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #4a5568;">{{ $voucher->payer_name ?? '-' }}</div>
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

@push('styles')
<style>
    .search-filter-section {
        padding: 20px;
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-bar {
        display: flex;
        align-items: center;
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }

    .search-bar:focus-within {
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        background: #fff;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        font-size: 13px;
        color: #4a5568;
        min-width: 140px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .per-page-select {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: #fff;
        font-size: 13px;
        color: #4a5568;
    }

    .per-page-select:focus {
        outline: none;
        border-color: #4299e1;
    }

    .pagination-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        color: #4a5568;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination-arrow:hover:not(.disabled) {
        background-color: #f7fafc;
        color: #4299e1;
    }

    .pagination-arrow.disabled {
        cursor: not-allowed;
    }
</style>
@endpush
