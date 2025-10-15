@extends('layouts.app')

@section('title', 'Quản lý kho - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Quản lý kho</h1>
            
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
    <div class="card" style="margin-bottom: 20px;">
        <div class="tab-navigation">
            <div class="tab-list">
                <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'tat_ca'])) }}" class="tab-item {{ $tab==='tat_ca' ? 'active' : '' }}">
                    <i class="fas fa-list" style="margin-right: 6px;"></i>
                    Tất cả
                </a>
                <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'con_hang'])) }}" class="tab-item {{ $tab==='con_hang' ? 'active' : '' }}">
                    <i class="fas fa-check-circle" style="margin-right: 6px;"></i>
                    Còn hàng
                </a>
                <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'low_stock'])) }}" class="tab-item {{ $tab==='low_stock' ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i>
                    Sắp hết
                </a>
                <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'het_hang'])) }}" class="tab-item {{ $tab==='het_hang' ? 'active' : '' }}">
                    <i class="fas fa-times-circle" style="margin-right: 6px;"></i>
                    Hết hàng
                </a>
            </div>
            </div>
        </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="{{ route('inventory.index') }}" id="filterForm">
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

                    <select name="sort_by" class="filter-select">
                        <option value="sku" {{ request('sort_by', 'sku') == 'sku' ? 'selected' : '' }}>Sắp xếp theo SKU</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Sắp xếp theo tên</option>
                        <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Sắp xếp theo tồn kho</option>
                        <option value="selling_price" {{ request('sort_by') == 'selling_price' ? 'selected' : '' }}>Sắp xếp theo giá bán</option>
                        <option value="import_price" {{ request('sort_by') == 'import_price' ? 'selected' : '' }}>Sắp xếp theo giá vốn</option>
                </select>

                    <select name="sort_order" class="filter-select">
                        <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>Tăng dần</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Giảm dần</option>
                </select>

                    <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px;" onclick="openAdvancedFilter()">
                        <i class="fas fa-filter"></i>
                        Bộ lọc khác
                    </button>
                </div>
            </div>
            </div>
        </form>

    <!-- Inventory Table -->
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Tồn kho</th>
                        <th>Giá bán</th>
                        <th>Giá vốn</th>
                        <th>Tổng giá vốn</th>
                        <th>Tổng giá bán</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="product-cell">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @php
                                        $imgPath = \App\Helpers\ImageHelper::getImageUrl($product->image);
                                    @endphp
                                    @if($imgPath)
                                        <img src="{{ $imgPath }}" alt="{{ $product->name }}" width="64" height="64" loading="lazy" decoding="async" style="width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border:1px solid #e2e8f0;">
                                @else
                                        <div style="width: 64px; height: 64px; background-color: #f8f9fa; border:1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                                    <div style="min-width: 0;">
                                        <div class="product-name">
                                            <a href="{{ route('inventory.show', $product->id) }}" style="color: inherit; text-decoration: none;">{{ $product->name }}</a>
                                        </div>
                                        <div class="product-sku">{{ $product->sku }}</div>
                                        @if($product->barcode)
                                            <div class="product-barcode" style="font-size: 12px; color: #6c757d; margin-top: 2px;">Barcode: {{ $product->barcode }}</div>
                                        @endif
                                        @if($product->categories->count() > 0)
                                            <div class="product-category" style="font-size: 12px; color: #4299e1; margin-top: 2px;">
                                                <i class="fas fa-tag" style="margin-right: 4px;"></i>
                                                {{ $product->categories->pluck('name')->join(', ') }}
                                            </div>
                                        @endif
                                        @if($product->concentration)
                                            <div class="product-concentration" style="font-size: 12px; color: #6c757d; margin-top: 2px;">
                                                <i class="fas fa-flask" style="margin-right: 4px;"></i>
                                                {{ $product->concentration }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #4a5568;">{{ $product->brand ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $isLow = ($product->low_stock_threshold ?? 5) > 0 && $product->stock > 0 && $product->stock <= ($product->low_stock_threshold ?? 5);
                                @endphp
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="px-2 py-1 rounded-md text-xs font-medium {{ $product->stock > 0 ? ($isLow ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700') : 'bg-red-50 text-red-700' }}">
                                        {{ $product->stock }}
                                    </span>
                                    @if($isLow)
                                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 12px;" title="Sắp hết hàng (ngưỡng: {{ $product->low_stock_threshold ?? 5 }})"></i>
                                    @elseif($product->stock <= 0)
                                        <i class="fas fa-times-circle" style="color: #ef4444; font-size: 12px;" title="Hết hàng"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#2c7a7b;">{{ number_format($product->selling_price, 0, ',', '.') }} đ</span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#4a5568;">{{ number_format($product->import_price, 0, ',', '.') }} đ</span>
                            </td>
                            @php
                                $rowCost = (float)($product->import_price ?? 0) * (int)($product->stock ?? 0);
                                $rowRetail = (float)($product->selling_price ?? 0) * (int)($product->stock ?? 0);
                            @endphp
                            <td>
                                <span style="font-weight:600; color:#4a5568;">{{ number_format($rowCost, 0, ',', '.') }} đ</span>
                            </td>
                            <td>
                                <span style="font-weight:600; color:#2c7a7b;">{{ number_format($rowRetail, 0, ',', '.') }} đ</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <button onclick="openAdjustModal({{ $product->id }}, '{{ str_replace("'", "\\'", $product->name) }}', 'import')" class="btn btn-sm btn-outline" title="Nhập kho">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button onclick="openAdjustModal({{ $product->id }}, '{{ str_replace("'", "\\'", $product->name) }}', 'export')" class="btn btn-sm btn-outline" title="Xuất kho">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button onclick="openAdjustModal({{ $product->id }}, '{{ str_replace("'", "\\'", $product->name) }}', 'adjust')" class="btn btn-sm btn-outline" title="Điều chỉnh">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr style="height: 400px;">
                            <td colspan="9" style="text-align: center; vertical-align: middle; padding: 0; color: #6c757d;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px;">
                                    <div style="margin-bottom: 16px;">
                                        <i class="fas fa-boxes" style="font-size: 48px; color: #dee2e6;"></i>
                                    </div>
                                    <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Chưa có sản phẩm nào</div>
                                    <div style="font-size: 14px; color: #6c757d;">Bắt đầu bằng cách thêm sản phẩm mới hoặc nhập danh sách từ file Excel.</div>
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
                Từ {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} trên tổng {{ $products->total() }}
            </div>
            
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
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

    </div>



    <!-- Adjust Modal -->
    <div class="modal" id="adjustModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="adjustModalTitle">Cập nhật tồn kho</h3>
                <span class="close" onclick="closeAdjustModal()">&times;</span>
            </div>
            <form id="adjustForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Loại giao dịch</label>
                    <select name="type" id="adjustType" class="form-control" required>
                        <option value="import">Nhập kho</option>
                        <option value="export">Xuất kho</option>
                        <option value="adjust">Điều chỉnh +/-</option>
                        <option value="stocktake">Đặt theo tồn thực tế</option>
                        <option value="return">Hàng trả về</option>
                        <option value="damage">Hàng hỏng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" id="quantityLabel">Số lượng</label>
                    <input type="number" name="quantity" id="adjustQuantity" class="form-control" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Ví dụ: Nhập bổ sung, kiểm kê, hàng hỏng..."></textarea>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeAdjustModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Global Modal -->
    <div class="modal" id="globalModal" style="display: none;">
        <div class="modal-content" style="max-width: 560px;">
            <div class="modal-header">
                <h3 id="globalModalTitle">Giao dịch tồn kho</h3>
                <span class="close" onclick="closeGlobalModal()">&times;</span>
            </div>
            <form id="globalForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Loại giao dịch</label>
                    <select name="type" id="globalType" class="form-control" required>
                        <option value="import">Nhập kho</option>
                        <option value="export">Xuất kho (bán/hủy)</option>
                        <option value="adjust">Điều chỉnh +/-</option>
                        <option value="stocktake">Đặt theo tồn thực tế</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Chọn sản phẩm</label>
                    <select name="product_id" id="globalProduct" class="form-control" required>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" id="globalQuantityLabel">Số lượng</label>
                    <input type="number" name="quantity" id="globalQuantity" class="form-control" required />
                </div>
                <div class="form-group" id="globalCostGroup" style="display: none;">
                    <label class="form-label">Giá nhập (đơn vị)</label>
                    <input type="number" step="0.01" name="unit_cost" class="form-control" />
                </div>
                <div class="form-group" id="globalSupplierGroup" style="display: none;">
                    <label class="form-label">Nhà cung cấp</label>
                    <input type="text" name="supplier" class="form-control" />
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày giao dịch</label>
                    <input type="datetime-local" name="transaction_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" />
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú/Lý do</label>
                    <input type="text" name="note" class="form-control" placeholder="VD: nhập NCC A, xuất hủy hết hạn..." />
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeGlobalModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Advanced Filter Modal -->
    <div class="modal" id="advancedFilterModal" style="display: none;">
        <div class="modal-content" style="max-width: 640px;">
            <div class="modal-header">
                <h3>Bộ lọc khác</h3>
                <span class="close" onclick="closeAdvancedFilter()">&times;</span>
            </div>
            <form method="GET" action="{{ route('inventory.index') }}">
                <div class="form-group" style="display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Nồng độ</label>
                        <select name="concentration" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach(['Parfum','EDP','EDT','EDC','Mist'] as $opt)
                                <option value="{{ $opt }}" {{ request('concentration') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label class="form-label">Nhóm hương</label>
                        <select name="fragrance_family" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($fragranceFamilies as $opt)
                                <option value="{{ $opt }}" {{ request('fragrance_family') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Giới tính</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Tất cả --</option>
                            @foreach($genders as $opt)
                                <option value="{{ $opt }}" {{ request('gender') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label class="form-label">Từ ngày tạo</label>
                        <input type="date" name="created_from" value="{{ request('created_from') }}" class="form-control">
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Đến ngày tạo</label>
                        <input type="date" name="created_to" value="{{ request('created_to') }}" class="form-control">
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 12px;">
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline">Xóa hết bộ lọc</a>
                    <div>
                        <button type="button" class="btn btn-outline" onclick="closeAdvancedFilter()">Đóng</button>
                        <button type="submit" class="btn btn-primary" style="margin-left: 8px;">Lọc</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal" id="importModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nhập file tồn kho</h3>
                <span class="close" onclick="closeImportModal()">&times;</span>
            </div>
            
            <div style="background-color: #ebf8ff; padding: 12px; border-radius: 6px; margin-bottom: 16px; border-left: 3px solid #4299e1;">
                <p style="margin: 0; color: #2b6cb0; font-size: 13px;">
                    <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
                    <strong>Lưu ý:</strong> Khuyến nghị dùng CSV (.csv). Hệ thống sẽ cập nhật tồn kho theo SKU nếu có, hoặc tạo mới nếu chưa có.
                </p>
            </div>
            
            <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file" class="form-label">Chọn file CSV hoặc Excel (.csv, .xlsx, .xls)</label>
                    <input type="file" id="file" name="file" class="form-control" accept=".csv,.xlsx,.xls,.txt" required>
                </div>
                <div class="form-group" style="display:flex; justify-content: space-between; align-items:center;">
                    <small style="color:#6c757d;">Bạn có thể tải file mẫu CSV tại đây để điền đúng định dạng.</small>
                    <a href="{{ route('inventory.import.template') }}" class="btn btn-outline">Tải file mẫu (.csv)</a>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeImportModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Nhập file</button>
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
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        }
        
        // Filter change
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });

    // Select all functionality removed - no more checkboxes

    // Adjust Modal functions
    let currentProductId = null;
    function openAdjustModal(productId, productName, defaultType){
        currentProductId = productId;
        document.getElementById('adjustModalTitle').innerText = 'Cập nhật tồn kho: ' + productName;
        const form = document.getElementById('adjustForm');
        form.action = `/inventory/${productId}/adjust`;
        document.getElementById('adjustType').value = defaultType || 'import';
        document.getElementById('adjustQuantity').value = '';
        document.getElementById('adjustModal').style.display = 'block';
        updateQuantityLabel();
    }
    
    function closeAdjustModal(){
        document.getElementById('adjustModal').style.display = 'none';
    }
    
    document.getElementById('adjustType').addEventListener('change', updateQuantityLabel);
    function updateQuantityLabel(){
        const type = document.getElementById('adjustType').value;
        const label = document.getElementById('quantityLabel');
        if(type === 'stocktake'){
            label.innerText = 'Tồn thực tế';
        } else if (type === 'adjust'){
            label.innerText = 'Số lượng (+/-)';
        } else if (type === 'export' || type === 'damage'){
            label.innerText = 'Số lượng (-)';
        } else {
            label.innerText = 'Số lượng (+)';
        }
    }

    // Global Modal functions
    function openGlobalModal(type){
        const modal = document.getElementById('globalModal');
        const form = document.getElementById('globalForm');
        document.getElementById('globalModalTitle').innerText = 'Giao dịch tồn kho';
        document.getElementById('globalType').value = type || 'import';
        form.action = `/inventory/${document.getElementById('globalProduct').value}/adjust`;
        toggleGlobalFields();
        modal.style.display = 'block';
    }
    
    function closeGlobalModal(){ 
        document.getElementById('globalModal').style.display='none'; 
    }
    
    document.getElementById('globalProduct')?.addEventListener('change', ()=>{
        const form = document.getElementById('globalForm');
        form.action = `/inventory/${document.getElementById('globalProduct').value}/adjust`;
    });
    
    document.getElementById('globalType')?.addEventListener('change', toggleGlobalFields);
    function toggleGlobalFields(){
        const type = document.getElementById('globalType').value;
        const label = document.getElementById('globalQuantityLabel');
        const cost = document.getElementById('globalCostGroup');
        const supplier = document.getElementById('globalSupplierGroup');
        if(type === 'import'){
            label.innerText = 'Số lượng (+)';
            cost.style.display = '';
            supplier.style.display = '';
        } else if(type === 'stocktake'){
            label.innerText = 'Tồn thực tế';
            cost.style.display = 'none';
            supplier.style.display = 'none';
        } else if(type === 'adjust'){
            label.innerText = 'Số lượng (+/-)';
            cost.style.display = 'none';
            supplier.style.display = 'none';
        } else {
            label.innerText = 'Số lượng (-)';
            cost.style.display = 'none';
            supplier.style.display = 'none';
        }
    }

    // Advanced Filter Modal functions
    function openAdvancedFilter() {
        document.getElementById('advancedFilterModal').style.display = 'block';
    }

    function closeAdvancedFilter() {
        document.getElementById('advancedFilterModal').style.display = 'none';
    }

    // Import/Export Modal functions
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

    // Close modal when clicking outside
    window.onclick = function(event) {
        const adjustModal = document.getElementById('adjustModal');
        const globalModal = document.getElementById('globalModal');
        const advancedFilterModal = document.getElementById('advancedFilterModal');
        const importModal = document.getElementById('importModal');
        const exportModal = document.getElementById('exportModal');
        
        if (event.target == adjustModal) {
            adjustModal.style.display = 'none';
        }
        if (event.target == globalModal) {
            globalModal.style.display = 'none';
        }
        if (event.target == advancedFilterModal) {
            advancedFilterModal.style.display = 'none';
        }
        if (event.target == importModal) {
            importModal.style.display = 'none';
        }
        if (event.target == exportModal) {
            exportModal.style.display = 'none';
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
        min-width: 320px; 
    }
    
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

    .product-barcode {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
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

    .btn-sm {
        padding: 6px 10px;
        font-size: 12px;
    }

    /* Stock status chips */
    .bg-green-50 { background-color: #f0fdf4; }
    .text-green-700 { color: #15803d; }
    .bg-yellow-50 { background-color: #fefce8; }
    .text-yellow-700 { color: #a16207; }
    .bg-red-50 { background-color: #fef2f2; }
    .text-red-700 { color: #dc2626; }

    /* Modal styles */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover {
        color: #000;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    /* Summary Card Styles */
    .summary-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
    }

    .summary-item {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .summary-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .summary-label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .summary-value {
        font-size: 24px;
        font-weight: 700;
        line-height: 1.2;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .summary-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .summary-item {
            padding: 16px;
        }
        
        .summary-value {
            font-size: 20px;
        }
    }

    /* Product detail styles */
    .product-category {
        font-size: 12px;
        color: #4299e1;
        margin-top: 2px;
        font-weight: 500;
    }

    .product-concentration {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
        font-weight: 500;
    }
</style>
@endpush


