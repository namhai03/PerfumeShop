@extends('layouts.app')

@section('title', 'Tồn kho sản phẩm - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title" style="margin-bottom: 8px;">{{ $product->name }}</h1>
            <div style="color:#6b7280;"> {{ $product->sku }} </div>
        </div>
        <div style="display:flex; gap:12px;">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Quay lại tồn kho</a>
            <a href="{{ route('products.edit', ['product' => $product->id, 'from' => 'inventory']) }}" class="btn btn-primary"><i class="fas fa-pen-to-square"></i> Sửa sản phẩm</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap: 24px;">
        <div>
            <div class="card">
                <div class="card-header">
                    <h3>Thông tin tồn kho</h3>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <div style="color:#6b7280;">Tồn kho</div>
                        <div style="font-size: 24px; font-weight: 700;">{{ $product->stock }}</div>
                    </div>
                    <div>
                        <div style="color:#6b7280;">Ngưỡng cảnh báo</div>
                        <div style="font-size: 24px; font-weight: 700;">{{ $product->low_stock_threshold ?? 5 }}</div>
                    </div>
                    <div>
                        <div style="color:#6b7280;">Có thể bán</div>
                        <div style="font-size: 24px; font-weight: 700;">{{ $product->stock }}</div>
                    </div>
                </div>

                <div style="margin-top: 16px; display:flex; gap: 12px; flex-wrap: wrap; color:#6b7280;">
                    Chọn tên sản phẩm để chỉnh sửa chi tiết. Lịch sử biến động hiển thị ở bên dưới.
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3>Lịch sử biến động</h3>
                    
                </div>

                <!-- Bộ lọc -->
                <div id="filterSection" style="display:none; padding:20px; border-bottom:1px solid #e5e7eb; background:#f8fafc;">
                    <div style="margin-bottom:16px;">
                        <h4 style="margin:0; color:#374151; font-size:16px; font-weight:600;">Theo dõi tất cả giao dịch nhập xuất</h4>
                    </div>
                    <form method="GET" class="inventory-filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <div class="search-input-wrapper">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           class="search-input" placeholder="Tìm kiếm theo tên sản phẩm, SKU">
                                </div>
                            </div>
                            <div class="filter-group">
                                <select name="product_id" class="filter-select">
                                    <option value="">Sản phẩm</option>
                                    <!-- Sẽ được thêm sau nếu cần -->
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="type" class="filter-select">
                                    <option value="">Loại giao dịch</option>
                                    @foreach($movementTypes as $key => $label)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-group">
                                <select name="time_range" class="filter-select" onchange="handleTimeRangeChange(this)">
                                    <option value="">Khoảng thời gian</option>
                                    <option value="today" {{ request('time_range') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                                    <option value="yesterday" {{ request('time_range') == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                                    <option value="this_week" {{ request('time_range') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                                    <option value="last_week" {{ request('time_range') == 'last_week' ? 'selected' : '' }}>Tuần trước</option>
                                    <option value="this_month" {{ request('time_range') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                                    <option value="last_month" {{ request('time_range') == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                                    <option value="custom" {{ request('time_range') == 'custom' ? 'selected' : '' }}>Tùy chỉnh</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <button type="button" class="other-filters-btn" onclick="toggleOtherFilters()">
                                    <i class="fas fa-filter"></i>
                                    Bộ lọc khác
                                </button>
                            </div>
                        </div>
                        
                        <!-- Bộ lọc tùy chỉnh thời gian -->
                        <div id="customTimeRange" style="display:{{ request('time_range') == 'custom' ? 'block' : 'none' }}; margin-top:16px; padding:16px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="display:grid; grid-template-columns: 1fr auto 1fr; gap:12px; align-items:center;">
                                <div>
                                    <label class="form-label">Từ ngày</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                                </div>
                                <div style="text-align:center; color:#6b7280; font-weight:500;">đến</div>
                                <div>
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Bộ lọc khác -->
                        <div id="otherFilters" style="display:none; margin-top:16px; padding:16px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                                <div>
                                    <label class="form-label">Loại thay đổi</label>
                                    <select name="change_type" class="form-control">
                                        <option value="">Tất cả</option>
                                        @foreach($changeTypes as $key => $label)
                                            <option value="{{ $key }}" {{ request('change_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Nhà cung cấp</label>
                                    <input type="text" name="supplier" value="{{ request('supplier') }}" 
                                           class="form-control" placeholder="Tên nhà cung cấp">
                                </div>
                                <div>
                                    <label class="form-label">Mã tham chiếu</label>
                                    <input type="text" name="reference_id" value="{{ request('reference_id') }}" 
                                           class="form-control" placeholder="Mã đơn hàng, PO...">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:16px; display:flex; gap:8px; justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('inventory.show', $product->id) }}" class="btn btn-outline">
                                <i class="fas fa-times"></i> Xóa lọc
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Thống kê nhanh -->
                @if(isset($stats))
                <div style="padding:16px; border-bottom:1px solid #e5e7eb; background:#f8fafc;">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:16px; text-align:center;">
                        <div>
                            <div style="color:#6b7280; font-size:12px;">Tổng giao dịch</div>
                            <div style="font-weight:700; font-size:18px;">{{ $stats['total_movements'] }}</div>
                        </div>
                        <div>
                            <div style="color:#6b7280; font-size:12px;">Tăng kho</div>
                            <div style="font-weight:700; font-size:18px; color:#10b981;">+{{ $stats['total_increases'] }}</div>
                        </div>
                        <div>
                            <div style="color:#6b7280; font-size:12px;">Giảm kho</div>
                            <div style="font-weight:700; font-size:18px; color:#ef4444;">-{{ $stats['total_decreases'] }}</div>
                        </div>
                        <div>
                            <div style="color:#6b7280; font-size:12px;">Thay đổi ròng</div>
                            <div style="font-weight:700; font-size:18px; {{ $stats['net_change'] >= 0 ? 'color:#10b981;' : 'color:#ef4444;' }}">
                                {{ $stats['net_change'] >= 0 ? '+' : '' }}{{ $stats['net_change'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'transaction_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       style="text-decoration:none; color:inherit;">
                                        Thời gian
                                        @if(request('sort_by') == 'transaction_date')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Loại</th>
                                <th>Thay đổi</th>
                                <th>Tồn trước</th>
                                <th>Tồn sau</th>
                                <th>Ghi chú</th>
                                <th>Tham chiếu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $m)
                                <tr>
                                    <td>
                                        <div style="font-weight:500;">{{ $m->transaction_date_formatted }}</div>
                                        @if($m->supplier)
                                            <div style="font-size:11px; color:#6b7280;">{{ $m->supplier }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <i class="{{ $m->type_icon }}"></i>
                                            <span>{{ $m->type_text }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="{{ $m->is_increase ? 'qty-pos' : 'qty-neg' }}" style="font-weight:600;">
                                            {{ $m->quantity_change_formatted }}
                                        </div>
                                        @if($m->unit_cost)
                                            <div style="font-size:11px; color:#6b7280;">
                                                {{ number_format($m->unit_cost, 0, ',', '.') }}₫/sp
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($m->before_stock) }}</td>
                                    <td>{{ number_format($m->after_stock) }}</td>
                                    <td class="product-name" style="max-width:200px;">
                                        {{ $m->note }}
                                        @if($m->order)
                                            <div style="font-size:11px; color:#3b82f6;">
                                                <a href="{{ route('orders.show', $m->order->id) }}" style="text-decoration:none;">
                                                    Đơn hàng: {{ $m->order->order_number }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($m->reference_id)
                                            <span style="font-family:monospace; font-size:11px; background:#f3f4f6; padding:2px 6px; border-radius:4px;">
                                                {{ $m->reference_id }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" style="text-align:center; padding: 28px; color:#6b7280;">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:16px;">
                    <div style="color:#6b7280; font-size:14px;">
                        Hiển thị {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} 
                        trong tổng số {{ $movements->total() }} giao dịch
                    </div>
                    <div>{{ $movements->links() }}</div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3>Thông tin sản phẩm</h3></div>
                <div style="display:flex; gap: 16px;">
                    @if($product->image)
                        <img src="{{ $product->image }}" style="width:96px; height:96px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;" />
                    @else
                        <div style="width:96px; height:96px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <div>
                        <div style="color:#6b7280;">Danh mục</div>
                        <div style="font-weight:600;">{{ $product->category ?? '-' }}</div>
                        <div style="margin-top:8px; color:#6b7280;">Thương hiệu</div>
                        <div style="font-weight:600;">{{ $product->brand ?? '-' }}</div>
                    </div>
                </div>
                <div style="margin-top: 16px; display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <div style="color:#6b7280;">Giá bán</div>
                        <div style="font-weight:700;">{{ number_format((float)$product->selling_price, 0, ',', '.') }}₫</div>
                    </div>
                    <div>
                        <div style="color:#6b7280;">Giá vốn</div>
                        <div style="font-weight:700;">{{ number_format((float)$product->import_price, 0, ',', '.') }}₫</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="adjustModal" style="display:none;">
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
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeAdjustModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle bộ lọc
        function toggleFilters() {
            const filterSection = document.getElementById('filterSection');
            const isVisible = filterSection.style.display !== 'none';
            filterSection.style.display = isVisible ? 'none' : 'block';
        }

        // Toggle bộ lọc khác
        function toggleOtherFilters() {
            const otherFilters = document.getElementById('otherFilters');
            const isVisible = otherFilters.style.display !== 'none';
            otherFilters.style.display = isVisible ? 'none' : 'block';
        }

        // Xử lý thay đổi khoảng thời gian
        function handleTimeRangeChange(select) {
            const customTimeRange = document.getElementById('customTimeRange');
            const dateFromInput = document.querySelector('input[name="date_from"]');
            const dateToInput = document.querySelector('input[name="date_to"]');
            
            if (select.value === 'custom') {
                customTimeRange.style.display = 'block';
            } else {
                customTimeRange.style.display = 'none';
                
                // Tự động set ngày theo lựa chọn
                const today = new Date();
                let fromDate, toDate;
                
                switch(select.value) {
                    case 'today':
                        fromDate = toDate = today;
                        break;
                    case 'yesterday':
                        fromDate = toDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                        break;
                    case 'this_week':
                        fromDate = new Date(today.getTime() - (today.getDay() * 24 * 60 * 60 * 1000));
                        toDate = today;
                        break;
                    case 'last_week':
                        fromDate = new Date(today.getTime() - ((today.getDay() + 7) * 24 * 60 * 60 * 1000));
                        toDate = new Date(today.getTime() - (today.getDay() * 24 * 60 * 60 * 1000));
                        break;
                    case 'this_month':
                        fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        toDate = today;
                        break;
                    case 'last_month':
                        fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        toDate = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                }
                
                if (fromDate && toDate) {
                    dateFromInput.value = fromDate.toISOString().split('T')[0];
                    dateToInput.value = toDate.toISOString().split('T')[0];
                }
            }
        }

        // Auto-submit form khi thay đổi select
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select[name="type"], select[name="change_type"], select[name="time_range"]');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    if (this.name === 'time_range') {
                        handleTimeRangeChange(this);
                    }
                    this.form.submit();
                });
            });
        });

        // Modal functions
        function openAdjustModal(productId, productName, defaultType){
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
        
        document.getElementById('adjustType')?.addEventListener('change', updateQuantityLabel);
        
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
        
        window.onclick = function(event){
            const modal = document.getElementById('adjustModal');
            if (event.target == modal) { modal.style.display = 'none'; }
        }

        // Hiển thị bộ lọc nếu có tham số
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('type') || urlParams.has('search') || urlParams.has('date_from') || urlParams.has('date_to') || urlParams.has('change_type')) {
                document.getElementById('filterSection').style.display = 'block';
            }
        });
    </script>
    @endpush
@endsection


