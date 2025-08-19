@extends('layouts.app')

@section('title', 'Danh sách sản phẩm - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Danh sách sản phẩm</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <button onclick="openExportModal()" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-upload"></i>
                Xuất file
            </button>
            <button onclick="openImportModal()" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-download"></i>
                Nhập file
            </button>
            <div class="dropdown" style="position: relative;">
                <a href="{{ route('products.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                    <i class="fas fa-plus"></i>
                    Thêm sản phẩm
                    <i class="fas fa-chevron-down" style="margin-left: 8px; font-size: 10px;"></i>
                </a>
            </div>
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



    <!-- Search and Filter Section -->
    <form method="GET" action="{{ route('products.index') }}" id="filterForm">
        <div class="card">
            <div class="search-filter-section" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <!-- Search Bar -->
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm theo mã sản phẩm, tên sản phẩm, barcode"
                               value="{{ request('search') }}"
                               style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap;">
                    

                    <select name="category" class="filter-select">
                        <option value="">Loại sản phẩm</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tag" class="filter-select">
                        <option value="">Tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>
                                {{ $tag }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px;" onclick="openAdvancedFilter()">
                        <i class="fas fa-filter"></i>
                        Bộ lọc khác
                    </button>

                    <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; background-color: #6c757d; color: white;">
                        Lưu bộ lọc
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Products Table -->
    <div class="card">
        <div class="table-container">
            <form id="bulkDeleteForm" action="{{ route('products.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="select-all" style="margin: 0;">
                        </th>
                        <th>Sản phẩm</th>
                        <th>Có thể bán</th>
                        <th>Loại</th>
                        <th>Nhãn hiệu</th>
                        <th>Ngày khởi tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $product->id }}" style="margin: 0;">
                            </td>
                            <td class="product-cell">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if($product->image)
                                        <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div style="width: 40px; height: 40px; background-color: #f8f9fa; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">
                                            <a href="{{ route('products.show', $product->id) }}" style="color: inherit; text-decoration: none;">{{ $product->name }}</a>
                                        </div>
                                        <div style="font-size: 12px; color: #6c757d;">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                            <span class="px-2 py-1 rounded-md text-xs font-medium
                                {{ $product->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                {{ $product->is_active ? '1' : '0' }}
                            </span>
                            </td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; background-color: #e3f2fd; color: #1976d2;">
                                    {{ $product->category ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">
                                    {{ $product->brand ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">
                                    {{ $product->created_at ? $product->created_at->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                <div style="margin-bottom: 16px;">
                                    <i class="fas fa-shopping-bag" style="font-size: 32px; color: #dee2e6;"></i>
                                </div>
                                <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Chưa có sản phẩm nào</div>
                                <div style="font-size: 14px;">Bắt đầu bằng cách thêm sản phẩm mới hoặc nhập danh sách từ file Excel.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </form>
        </div>

        <!-- Pagination and Display Options -->
        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <div class="pagination-info" style="color: #6c757d; font-size: 14px;">
                Từ {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} trên tổng {{ $products->total() }}
            </div>
            
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
                <button type="button" id="bulkDeleteBtn" class="btn btn-danger" onclick="submitBulkDelete()" style="font-size:13px; padding:8px 16px;" disabled>Xóa đã chọn</button>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: #4a5568;">Hiển thị</span>
                    <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="font-size: 14px; color: #4a5568;">Kết quả</span>
                </div>
                
                <div class="pagination-controls">
                    @if($products->hasPages())
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($products->onFirstPage())
                                <span class="pagination-arrow disabled" style="color: #cbd5e0; cursor: not-allowed;">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="pagination-arrow">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif
                            
                            <span class="current-page" style="background-color: #4299e1; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                {{ $products->currentPage() }}
                            </span>
                            
                            @if($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="pagination-arrow">
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

    <!-- Advanced Filter Modal -->
    <div class="modal" id="advancedFilterModal" style="display: none;">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header">
                <h3>Bộ lọc khác</h3>
                <span class="close" onclick="closeAdvancedFilter()">&times;</span>
            </div>
            <form method="GET" action="{{ route('products.index') }}">
                <div class="form-group">
                    <label class="form-label">Nhãn hiệu</label>
                    <input type="text" name="brand" value="{{ request('brand') }}" class="form-control" placeholder="VD: Dior, Chanel">
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày tạo</label>
                    <input type="date" name="created_date" value="{{ request('created_date') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Kênh bán hàng</label>
                    <select name="sales_channel" class="form-control">
                        <option value="">-- Chọn --</option>
                        <option value="online" {{ request('sales_channel')=='online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('sales_channel')=='offline' ? 'selected' : '' }}>Offline</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <input type="text" name="category" value="{{ request('category') }}" class="form-control" placeholder="VD: Nước hoa nam">
                </div>
                <div class="form-group">
                    <label class="form-label">Loại sản phẩm</label>
                    <input type="text" name="product_type" value="{{ request('product_type') }}" class="form-control" placeholder="VD: Chính hãng, decant">
                </div>
                <div class="form-group">
                    <label class="form-label">Tag</label>
                    <input type="text" name="tags[]" value="{{ is_array(request('tags')) ? implode(',', request('tags')) : request('tags') }}" class="form-control" placeholder="Nhập nhiều tag, cách nhau bởi dấu phẩy">
                </div>
                <div class="form-group">
                    <label class="form-label">Hình thức sản phẩm</label>
                    <input type="text" name="product_form" value="{{ request('product_form') }}" class="form-control" placeholder="VD: Full box, Tester">
                </div>
                <div class="form-group">
                    <label class="form-label">Sản phẩm lô - HSD</label>
                    <select name="has_expiry" class="form-control">
                        <option value="">-- Tất cả --</option>
                        <option value="1" {{ request('has_expiry')=='1' ? 'selected' : '' }}>Có HSD</option>
                        <option value="0" {{ request('has_expiry')=='0' ? 'selected' : '' }}>Không HSD</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Bảng giá theo chi nhánh</label>
                        <select name="has_branch_price" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="1" {{ request('has_branch_price')=='1' ? 'selected' : '' }}>Có</option>
                            <option value="0" {{ request('has_branch_price')=='0' ? 'selected' : '' }}>Không</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Bảng giá theo nhóm KH</label>
                        <select name="has_customer_group_price" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="1" {{ request('has_customer_group_price')=='1' ? 'selected' : '' }}>Có</option>
                            <option value="0" {{ request('has_customer_group_price')=='0' ? 'selected' : '' }}>Không</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex; justify-content: space-between; gap: 12px;">
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Xóa hết bộ lọc</a>
                    <div>
                        <button type="button" class="btn btn-outline" onclick="closeAdvancedFilter()">Đóng</button>
                        <button type="submit" class="btn btn-primary" style="margin-left:8px;">Lọc</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal" id="importModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nhập file sản phẩm</h3>
                <span class="close" onclick="closeImportModal()">&times;</span>
            </div>
            
            <div style="background-color: #ebf8ff; padding: 12px; border-radius: 6px; margin-bottom: 16px; border-left: 3px solid #4299e1;">
                <p style="margin: 0; color: #2b6cb0; font-size: 13px;">
                    <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
                    <strong>Lưu ý:</strong> Khuyến nghị dùng CSV (.csv). Hệ thống sẽ cập nhật theo SKU nếu có, hoặc tạo mới nếu chưa có.
                </p>
            </div>
            
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file" class="form-label">Chọn file CSV hoặc Excel (.csv, .xlsx, .xls)</label>
                    <input type="file" id="file" name="file" class="form-control" accept=".csv,.xlsx,.xls,.txt" required>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeImportModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Nhập file</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal" id="exportModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xuất file sản phẩm</h3>
                <span class="close" onclick="closeExportModal()">&times;</span>
            </div>
            
            <form action="{{ route('products.export') }}" method="GET">
                <div class="form-group">
                    <label class="form-label">Chọn định dạng xuất file</label>
                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="format" value="xlsx" checked>
                            <span>Excel (.xlsx)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="format" value="csv">
                            <span>CSV (.csv)</span>
                        </label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeExportModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xuất file</button>
                </div>
            </form>
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

    // Select all functionality
    // Bulk select
    const selectAllEl = document.getElementById('select-all');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    function updateBulkDeleteState() {
        const checked = document.querySelectorAll('input[name="ids[]"]:checked');
        const count = checked.length;
        bulkDeleteBtn.disabled = count === 0;
        bulkDeleteBtn.textContent = count > 0 ? `Xóa đã chọn (${count})` : 'Xóa đã chọn';
    }

    if (selectAllEl) {
        selectAllEl.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteState();
        });
    }

    // Lắng nghe từng checkbox hàng
    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'ids[]') {
            updateBulkDeleteState();
        }
    });

    function submitBulkDelete() {
        const checked = document.querySelectorAll('input[name="ids[]"]:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất 1 sản phẩm.');
            return;
        }
        if (confirm('Bạn có chắc muốn xóa ' + checked.length + ' sản phẩm đã chọn?')) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }

    // Modal functions
    function openImportModal() {
        document.getElementById('importModal').style.display = 'block';
    }

    function closeImportModal() {
        document.getElementById('importModal').style.display = 'none';
    }

    function openExportModal() {
        document.getElementById('exportModal').style.display = 'block';
    }

    function closeExportModal() {
        document.getElementById('exportModal').style.display = 'none';
    }

    function openAdvancedFilter() {
        document.getElementById('advancedFilterModal').style.display = 'block';
    }

    function closeAdvancedFilter() {
        document.getElementById('advancedFilterModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const importModal = document.getElementById('importModal');
        const exportModal = document.getElementById('exportModal');
        const advancedFilterModal = document.getElementById('advancedFilterModal');
        if (event.target == importModal) {
            importModal.style.display = 'none';
        }
        if (event.target == exportModal) {
            exportModal.style.display = 'none';
        }
        if (event.target == advancedFilterModal) {
            advancedFilterModal.style.display = 'none';
        }
    }
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

    .product-cell {
        min-width: 250px;
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

    .dropdown {
        position: relative;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        min-width: 160px;
        z-index: 1000;
    }

    .dropdown-item {
        display: block;
        padding: 8px 16px;
        color: #4a5568;
        text-decoration: none;
        font-size: 13px;
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f7fafc;
    }
</style>
@endpush
