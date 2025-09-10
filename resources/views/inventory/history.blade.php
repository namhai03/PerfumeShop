@extends('layouts.app')

@section('title', 'Lịch sử tồn kho - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
        <h1 class="page-title">Lịch sử tồn kho</h1>
            
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

    <!-- Filter Section -->
    <form method="GET" action="{{ route('inventory.history') }}" id="filterForm">
        <input type="hidden" name="per_page" id="perPageHidden" value="{{ request('per_page', 20) }}">
        <div class="card">
            <div class="search-filter-section" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <!-- Search Bar -->
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm theo tên sản phẩm, SKU, ghi chú"
                               value="{{ request('search') }}"
                               style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <select name="product_id" class="filter-select">
                        <option value="">Sản phẩm</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>

                    <select name="type" class="filter-select">
                        <option value="">Loại giao dịch</option>
                        @foreach(['import'=>'Nhập kho','export'=>'Xuất kho','adjust'=>'Điều chỉnh','stocktake'=>'Kiểm kê','return'=>'Trả về','damage'=>'Hư hỏng'] as $k=>$v)
                            <option value="{{ $k }}" {{ request('type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>

                    <select name="date_range" class="filter-select" onchange="toggleCustomDates(this.value)">
                        <option value="">Khoảng thời gian</option>
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                        <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                        <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                        <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Tuần trước</option>
                        <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                        <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
                    </select>

                    <div id="customDates" style="display: {{ request('date_range') === 'custom' ? 'flex' : 'none' }}; gap: 12px; align-items: center;">
                        <input type="date" name="from" value="{{ request('from') }}" class="filter-select" style="min-width: 160px;">
                        <input type="date" name="to" value="{{ request('to') }}" class="filter-select" style="min-width: 160px;">
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Statistics Cards -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng nhập</div>
                <div class="stat-value">{{ $stats['total_import'] ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng xuất</div>
                <div class="stat-value">{{ $stats['total_export'] ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Giao dịch hôm nay</div>
                <div class="stat-value">{{ $stats['today_transactions'] ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce7f3; color: #be185d;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Sản phẩm có giao dịch</div>
                <div class="stat-value">{{ $stats['products_with_movements'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Sản phẩm</th>
                        <th>Loại giao dịch</th>
                        <th>Thay đổi</th>
                        <th>Tồn kho</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #2c3e50;">{{ $m->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 12px; color: #6c757d;">{{ $m->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="product-cell">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @php
                                        $imgPath = null;
                                        if (!empty($m->product->image)) {
                                            $img = $m->product->image;
                                            $isAbsolute = \Illuminate\Support\Str::startsWith($img, ['http://','https://']);
                                            $isStorage = \Illuminate\Support\Str::startsWith($img, ['/storage/','storage/']);
                                            $imgPath = $isAbsolute ? $img : ($isStorage ? $img : Storage::url($img));
                                        }
                                    @endphp
                                    @if($imgPath)
                                        <img src="{{ $imgPath }}" alt="{{ $m->product->name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border:1px solid #e2e8f0;">
                                    @else
                                        <div style="width: 40px; height: 40px; background-color: #f8f9fa; border:1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div style="min-width: 0;">
                                        <div class="product-name">
                                            <a href="{{ route('inventory.show', $m->product->id) }}" style="color: inherit; text-decoration: none;">{{ $m->product->name }}</a>
                                        </div>
                                        <div class="product-sku">{{ $m->product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeConfig = [
                                        'import' => ['icon' => 'fas fa-arrow-up', 'color' => '#16a34a', 'bg' => '#dcfce7', 'label' => 'Nhập kho'],
                                        'export' => ['icon' => 'fas fa-arrow-down', 'color' => '#dc2626', 'bg' => '#fef2f2', 'label' => 'Xuất kho'],
                                        'adjust' => ['icon' => 'fas fa-edit', 'color' => '#2563eb', 'bg' => '#dbeafe', 'label' => 'Điều chỉnh'],
                                        'stocktake' => ['icon' => 'fas fa-clipboard-check', 'color' => '#7c3aed', 'bg' => '#ede9fe', 'label' => 'Kiểm kê'],
                                        'return' => ['icon' => 'fas fa-undo', 'color' => '#059669', 'bg' => '#d1fae5', 'label' => 'Trả về'],
                                        'damage' => ['icon' => 'fas fa-exclamation-triangle', 'color' => '#d97706', 'bg' => '#fef3c7', 'label' => 'Hư hỏng']
                                    ];
                                    $config = $typeConfig[$m->type] ?? $typeConfig['adjust'];
                                @endphp
                                <span class="transaction-type" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
                                    <i class="{{ $config['icon'] }}"></i>
                                    {{ $config['label'] }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: {{ $m->quantity_change >= 0 ? '#16a34a' : '#dc2626' }}; font-weight: 700; font-size: 16px;">
                                        {{ $m->quantity_change >= 0 ? '+' : '' }}{{ $m->quantity_change }}
                                    </span>
                                    @if($m->quantity_change > 0)
                                        <i class="fas fa-arrow-up" style="color: #16a34a; font-size: 12px;"></i>
                                    @elseif($m->quantity_change < 0)
                                        <i class="fas fa-arrow-down" style="color: #dc2626; font-size: 12px;"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12px; color: #6c757d;">Trước:</span>
                                    <span style="font-weight: 600; color: #4a5568;">{{ $m->before_stock }}</span>
                                    <i class="fas fa-arrow-right" style="color: #6c757d; font-size: 10px;"></i>
                                    <span style="font-weight: 600; color: #2c3e50;">{{ $m->after_stock }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="note-content" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $m->note ?? '-' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                <div style="margin-bottom: 16px;">
                                    <i class="fas fa-history" style="font-size: 32px; color: #dee2e6;"></i>
                                </div>
                                <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Chưa có giao dịch nào</div>
                                <div style="font-size: 14px;">Bắt đầu bằng cách thực hiện giao dịch nhập/xuất kho.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination and Display Options -->
        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <div class="pagination-info" style="color: #6c757d; font-size: 14px;">
                Từ {{ $movements->firstItem() ?? 0 }} đến {{ $movements->lastItem() ?? 0 }} trên tổng {{ $movements->total() }}
            </div>
            
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: #4a5568;">Hiển thị</span>
                    <select id="perPageSelect" class="per-page-select">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="font-size: 14px; color: #4a5568;">Kết quả</span>
                </div>
                
                <div class="pagination-controls">
                    @if($movements->hasPages())
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($movements->onFirstPage())
                                <span class="pagination-arrow disabled" style="color: #cbd5e0; cursor: not-allowed;">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $movements->previousPageUrl() }}" class="pagination-arrow">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif
                            
                            <span class="current-page" style="background-color: #4299e1; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                {{ $movements->currentPage() }}
                            </span>
                            
                            @if($movements->hasMorePages())
                                <a href="{{ $movements->nextPageUrl() }}" class="pagination-arrow">
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
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const searchInput = filterForm.querySelector('input[name="search"]');
        const filterSelects = filterForm.querySelectorAll('.filter-select');
        const perPageSelect = document.getElementById('perPageSelect');
        const perPageHidden = document.getElementById('perPageHidden');

        // Debounce search
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => filterForm.submit(), 500);
            });
        }

        // Auto submit on filter change
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Per page change -> sync to hidden on filter form to preserve current filters
        if (perPageSelect && perPageHidden) {
            perPageSelect.addEventListener('change', function() {
                perPageHidden.value = this.value;
                filterForm.submit();
            });
        }
    });

    function toggleCustomDates(value) {
        const wrap = document.getElementById('customDates');
        if (!wrap) return;
        wrap.style.display = value === 'custom' ? 'flex' : 'none';
    }
</script>
@endpush

@push('styles')
<style>
    .search-filter-section { padding: 20px; }
    .search-bar { display:flex; align-items:center; background:#f7fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; transition:all 0.2s ease; }
    .search-bar:focus-within { border-color:#4299e1; box-shadow:0 0 0 3px rgba(66,153,225,0.1); background:white; }
    .filter-select { padding:8px 12px; border:1px solid #e2e8f0; border-radius:6px; background:white; font-size:13px; color:#4a5568; min-width:140px; }
    .filter-select:focus { outline:none; border-color:#4299e1; box-shadow:0 0 0 3px rgba(66,153,225,0.1); }
    .per-page-select { padding:6px 12px; border:1px solid #e2e8f0; border-radius:4px; background:white; font-size:13px; color:#4a5568; }
    .per-page-select:focus { outline:none; border-color:#4299e1; }
    .table th { background-color:#f7fafc; color:#4a5568; font-weight:600; font-size:13px; padding:16px; border-bottom:1px solid #e2e8f0; }
    .table td { padding:16px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
</style>
@endpush
