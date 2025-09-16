@extends('layouts.app')

@section('title', 'Danh sách sản phẩm - PerfumeShop')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

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
                               placeholder="Tìm kiếm theo tên sản phẩm, SKU"
                               value="{{ request('search') }}"
                               style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <select name="category" class="filter-select">
                        <option value="">Danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="brand" class="filter-select">
                        <option value="">Thương hiệu</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                        @endforeach
                    </select>

                    <select name="tag" class="filter-select">
                        <option value="">Tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px;" onclick="openAdvancedFilter()">
                        <i class="fas fa-filter"></i>
                        Bộ lọc khác
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
                        <th>Thương hiệu</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Nồng độ</th>
                        <th>Nhóm hương</th>
                        <th>Trạng thái</th>
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
                                    @php
                                        $imgPath = null;
                                        if (!empty($product->image)) {
                                            $img = $product->image;
                                            $isAbsolute = \Illuminate\Support\Str::startsWith($img, ['http://','https://']);
                                            $isStorage = \Illuminate\Support\Str::startsWith($img, ['/storage/','storage/']);
                                            $imgPath = $isAbsolute ? $img : ($isStorage ? $img : Storage::url($img));
                                        }
                                    @endphp
                                    @if($imgPath)
                                        <img src="{{ $imgPath }}" alt="{{ $product->name }}" style="width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border:1px solid #e2e8f0;">
                                    @else
                                        <div style="width: 64px; height: 64px; background-color: #f8f9fa; border:1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div style="min-width: 0;">
                                        <div class="product-name">
                                            <a href="{{ route('products.show', $product->id) }}" style="color: inherit; text-decoration: none;">{{ $product->name }}</a>
                                        </div>
                                        <div class="product-sku">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">{{ $product->brand ?? '-' }}</span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#2c7a7b;">{{ number_format($product->selling_price, 0, ',', '.') }} đ</span>
                            </td>
                            <td>
                                @php $isLow = ($product->low_stock_threshold ?? 5) > 0 && $product->stock <= ($product->low_stock_threshold ?? 5); @endphp
                                <span class="px-2 py-1 rounded-md text-xs font-medium {{ $isLow ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">{{ $product->concentration ?? '-' }}</span>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">{{ $product->fragrance_family ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-md text-xs font-medium {{ $product->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $product->is_active ? 'Đang bán' : 'Không bán' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr style="height: 400px;">
                            <td colspan="8" style="text-align: center; vertical-align: middle; padding: 0; color: #6c757d;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px;">
                                    <div style="margin-bottom: 16px;">
                                        <i class="fas fa-shopping-bag" style="font-size: 48px; color: #dee2e6;"></i>
                                    </div>
                                    <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Chưa có sản phẩm nào</div>
                                    <div style="font-size: 14px; color: #6c757d;">Bắt đầu bằng cách thêm sản phẩm mới hoặc nhập danh sách từ file Excel.</div>
                                </div>
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
        <div class="modal-content" style="max-width: 640px;">
            <div class="modal-header">
                <h3>Bộ lọc khác</h3>
                <span class="close" onclick="closeAdvancedFilter()">&times;</span>
            </div>
            <form method="GET" action="{{ route('products.index') }}">
                
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Nồng độ</label>
                        <select name="concentration" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach(['Parfum','EDP','EDT','EDC','Mist'] as $opt)
                                <option value="{{ $opt }}" {{ request('concentration') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Nhóm hương</label>
                        <select name="fragrance_family" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($fragranceFamilies as $opt)
                                <option value="{{ $opt }}" {{ request('fragrance_family') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Giới tính</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($genders as $opt)
                                <option value="{{ $opt }}" {{ request('gender') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Trạng thái</label>
                        <select name="is_active" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>Đang bán</option>
                            <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>Không bán</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Thành phần</label>
                        <select name="ingredients" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($ingredients as $ingredient)
                                <option value="{{ $ingredient }}" {{ request('ingredients') == $ingredient ? 'selected' : '' }}>{{ $ingredient }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Tồn kho</label>
                        <select name="low_stock" class="form-control">
                            <option value="">-- Tất cả --</option>
                            <option value="1" {{ request('low_stock')==='1' ? 'selected' : '' }}>Sắp hết (≤ ngưỡng)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Từ ngày tạo</label>
                        <input type="date" name="created_from" value="{{ request('created_from') }}" class="form-control">
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Đến ngày tạo</label>
                        <input type="date" name="created_to" value="{{ request('created_to') }}" class="form-control">
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="form-label">Nhập hàng từ</label>
                        <input type="date" name="import_from" value="{{ request('import_from') }}" class="form-control">
                    </div>
                    <div style="flex:1;">
                        <label class="form-label">Nhập hàng đến</label>
                        <input type="date" name="import_to" value="{{ request('import_to') }}" class="form-control">
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

    .product-cell { min-width: 320px; }
    .product-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 2px;
        max-width: 520px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-sku {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 520px;
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
