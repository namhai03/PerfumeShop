@extends('layouts.app')

@section('title', 'Đơn hàng bán - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Đơn hàng bán</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('orders.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-plus"></i>
                Tạo đơn hàng mới
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

    <!-- Tab Navigation -->
    <div class="card" style="padding: 0; margin-bottom: 24px;">
        <div class="tab-navigation">
            <div class="tab-list">
                <a href="{{ route('orders.index') }}" class="tab-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                    Tất cả đơn hàng
                </a>
                <a href="{{ route('orders.sales') }}" class="tab-item {{ request()->routeIs('orders.sales') ? 'active' : '' }}">
                    Đơn bán
                </a>
                <a href="{{ route('orders.returns') }}" class="tab-item {{ request()->routeIs('orders.returns') ? 'active' : '' }}">
                    Đơn trả
                </a>
                <a href="{{ route('orders.drafts') }}" class="tab-item {{ request()->routeIs('orders.drafts') ? 'active' : '' }}">
                    Đơn nháp
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="{{ route('orders.sales') }}" id="filterForm">
        <div class="card">
            <div class="search-filter-section">
                <!-- Search Bar -->
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm theo mã đơn hàng, tên khách hàng"
                               value="{{ request('search') }}"
                               style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <!-- Date Filters -->
                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 13px; color: #4a5568; font-weight: 500; white-space: nowrap;">Từ ngày:</label>
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="filter-select" 
                               style="min-width: 150px;">
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label style="font-size: 13px; color: #4a5568; font-weight: 500; white-space: nowrap;">Đến ngày:</label>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="filter-select" 
                               style="min-width: 150px;">
                    </div>
                    
                    <button type="submit" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                        <i class="fas fa-filter"></i>
                        Lọc
                    </button>
                    
                    @if(request('date_from') || request('date_to') || request('search'))
                        <a href="{{ route('orders.sales') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px; color: #e53e3e;">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <!-- Orders Table -->
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', ['order' => $order->id, 'return' => request()->fullUrl()]) }}" class="order-number">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="customer-name">
                                    {{ $order->customer->name ?? $order->customer_name ?? 'N/A' }}
                                </div>
                                @if($order->customer->phone ?? $order->phone)
                                    <div style="font-size: 12px; color: #718096;">
                                        {{ $order->customer->phone ?? $order->phone }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $order->type_badge_class }}">
                                    {{ $order->type_text }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $order->status_badge_class }}">
                                    {{ $order->status_text }}
                                </span>
                            </td>
                            <td>
                                <div class="order-amount">
                                    {{ number_format($order->final_amount, 0, ',', '.') }} ₫
                                </div>
                            </td>
                            <td>
                                <div class="order-date">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="order-actions">
                                    <a href="{{ route('orders.edit', ['order' => $order->id, 'return' => request()->fullUrl()]) }}" class="btn btn-outline" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr style="height: 400px;">
                            <td colspan="6" style="text-align: center; vertical-align: middle; padding: 0; color: #6c757d;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px;">
                                    <div style="margin-bottom: 16px;">
                                        <i class="fas fa-shopping-cart" style="font-size: 48px; color: #dee2e6;"></i>
                                    </div>
                                    <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Chưa có đơn hàng bán nào</div>
                                    <div style="font-size: 14px; color: #6c757d;">Bắt đầu bằng cách tạo đơn hàng bán mới.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination and Display Options -->
        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <div class="pagination-info" style="color: #6c757d; font-size: 14px;">
                Từ {{ $orders->firstItem() ?? 0 }} đến {{ $orders->lastItem() ?? 0 }} trên tổng {{ $orders->total() }}
            </div>
            
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: #4a5568;">Hiển thị</span>
                    <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('per_page', 15) == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span style="font-size: 14px; color: #4a5568;">Kết quả</span>
                </div>
                
                <div class="pagination-controls">
                    @if($orders->hasPages())
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($orders->onFirstPage())
                                <span class="pagination-arrow disabled" style="color: #cbd5e0; cursor: not-allowed;">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $orders->previousPageUrl() }}" class="pagination-arrow">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif
                            
                            <span class="current-page" style="background-color: #4299e1; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                {{ $orders->currentPage() }}
                            </span>
                            
                            @if($orders->hasMorePages())
                                <a href="{{ $orders->nextPageUrl() }}" class="pagination-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="pagination-arrow disabled" style="color: #cbd5e0; cursor: not-allowed;">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const filterSelects = document.querySelectorAll('.filter-select');
        const filterForm = document.getElementById('filterForm');
        
        // Debounce search
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
        
        // Filter change
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .tab-navigation {
        border-bottom: 1px solid #e2e8f0;
    }

    .tab-list {
        display: flex;
        gap: 0;
    }

    .tab-item {
        padding: 12px 24px;
        text-decoration: none;
        color: #4a5568;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
    }

    .tab-item.active {
        color: #4299e1;
        border-bottom-color: #4299e1;
    }

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
        background: white;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 13px;
        color: #4a5568;
        min-width: 140px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
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

    .per-page-select {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        background: white;
        font-size: 13px;
        color: #4a5568;
    }

    .per-page-select:focus {
        outline: none;
        border-color: #4299e1;
    }

    .current-page {
        font-size: 14px;
        font-weight: 500;
    }
</style>
@endpush