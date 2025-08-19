@extends('layouts.app')

@section('title', 'Quản lý kho - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title">Quản lý kho: Cửa hàng chính</h1>
        
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
        <div class="card-header inv-header">
            <div class="tab-navigation" style="border-bottom:none;">
                <div class="tab-list inv-tabs">
                    <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'tat_ca'])) }}" class="tab-item tab-all {{ $tab==='tat_ca' ? 'active' : '' }}">Tất cả</a>
                    <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'con_hang'])) }}" class="tab-item tab-in {{ $tab==='con_hang' ? 'active' : '' }}">Còn hàng</a>
                    <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'low_stock'])) }}" class="tab-item tab-low {{ $tab==='low_stock' ? 'active' : '' }}">Sắp hết</a>
                    <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['tab' => 'het_hang'])) }}" class="tab-item tab-out {{ $tab==='het_hang' ? 'active' : '' }}">Hết hàng</a>
                </div>
                
            </div>
        </div>

        <form method="GET" action="{{ route('inventory.index') }}" id="inventoryFilterForm" class="inv-toolbar">
            <div class="search-bar inv-search">
                <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                <input type="text" name="search" placeholder="Tìm kiếm theo tên sản phẩm, SKU" value="{{ request('search') }}" />
            </div>
            <div class="inv-controls">
                <label>Danh mục</label>
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    @foreach(($categories ?? []) as $cat)
                        <option value="{{ $cat }}" {{ request('category')==$cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sort-group inv-controls">
                <label>Sắp xếp</label>
                <select name="sort_by" class="form-control" onchange="this.form.submit()">
                    <option value="sku" {{ ($sortBy ?? '')==='sku' ? 'selected' : '' }}>SKU</option>
                    <option value="name" {{ ($sortBy ?? '')==='name' ? 'selected' : '' }}>Tên</option>
                    <option value="stock" {{ ($sortBy ?? '')==='stock' ? 'selected' : '' }}>Tồn kho</option>
                    <option value="selling_price" {{ ($sortBy ?? '')==='selling_price' ? 'selected' : '' }}>Giá bán</option>
                    <option value="import_price" {{ ($sortBy ?? '')==='import_price' ? 'selected' : '' }}>Giá vốn</option>
                </select>
                <select name="sort_order" class="form-control" onchange="this.form.submit()">
                    <option value="asc" {{ ($sortOrder ?? '')==='asc' ? 'selected' : '' }}>Tăng dần</option>
                    <option value="desc" {{ ($sortOrder ?? '')==='desc' ? 'selected' : '' }}>Giảm dần</option>
                </select>
            </div>
            <div class="per-page inv-controls">
                <label>Hiển thị</label>
                <select name="per_page" class="form-control" onchange="this.form.submit()">
                    <option value="20" {{ ($perPage ?? 20)==20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ ($perPage ?? 20)==50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? 20)==100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </form>

        <div class="table-container">
            <table class="table inv-table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th class="number">Tồn kho</th>
                        <th class="number">Ngưỡng</th>
                        <th class="number">Có thể bán</th>
                        <th class="number">Giá bán</th>
                        <th class="number">Giá vốn</th>
                        <th class="number">Tổng giá vốn</th>
                        <th class="number">Tổng giá bán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                @else
                                    <div style="width: 40px; height: 40px; background-color: #f8f9fa; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="product-name">
                                <a href="{{ route('inventory.show', $product->id) }}" style="text-decoration:none; color:inherit; font-weight:600;">{{ $product->name }}</a>
                                <div class="subtitle">SKU: {{ $product->sku }}</div>
                            </td>
                            <td class="number">
                                @php
                                    $isLow = ($product->low_stock_threshold ?? 5) > 0 && $product->stock > 0 && $product->stock <= ($product->low_stock_threshold ?? 5);
                                @endphp
                                <span class="chip {{ $product->stock > 0 ? ($isLow ? 'chip-warning' : 'chip-success') : 'chip-danger' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="number">{{ $product->low_stock_threshold ?? 5 }}</td>
                            <td class="number">{{ $product->stock }}</td>
                            <td class="number">{{ number_format((float)$product->selling_price, 0, ',', '.') }}₫</td>
                            <td class="number">{{ number_format((float)$product->import_price, 0, ',', '.') }}₫</td>
                            @php
                                $rowCost = (float)($product->import_price ?? 0) * (int)($product->stock ?? 0);
                                $rowRetail = (float)($product->selling_price ?? 0) * (int)($product->stock ?? 0);
                            @endphp
                            <td class="number">{{ number_format($rowCost, 0, ',', '.') }}₫</td>
                            <td class="number">{{ number_format($rowRetail, 0, ',', '.') }}₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding: 40px; color:#6c757d;">Chưa có sản phẩm</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"></td>
                        <td class="number"><strong>{{ number_format($pageTotals['total_qty'] ?? 0, 0, ',', '.') }}</strong></td>
                        <td></td>
                        <td class="number"><strong>{{ number_format($pageTotals['total_qty'] ?? 0, 0, ',', '.') }}</strong></td>
                        <td></td>
                        <td></td>
                        <td class="number"><strong>{{ number_format($pageTotals['total_cost'] ?? 0, 0, ',', '.') }}₫</strong></td>
                        <td class="number"><strong>{{ number_format($pageTotals['total_retail'] ?? 0, 0, ',', '.') }}₫</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="table-footer inv-footer">
            <div class="inv-count">Từ {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} trên tổng {{ $products->total() }}</div>
            <div class="inv-summary">
                <span>Toàn bộ kết quả: SL {{ number_format($overallTotals->total_qty ?? 0, 0, ',', '.') }}</span>
                <span>• Tổng giá vốn {{ number_format($overallTotals->total_cost ?? 0, 0, ',', '.') }}₫</span>
                <span>• Tổng giá bán {{ number_format($overallTotals->total_retail ?? 0, 0, ',', '.') }}₫</span>
            </div>
            <div class="pagination-controls">{{ $products->links('vendor.pagination.perfume') }}</div>
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
    <div class="modal" id="globalModal" style="display:none;">
        <div class="modal-content" style="max-width:560px;">
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
                <div class="form-group" id="globalCostGroup" style="display:none;">
                    <label class="form-label">Giá nhập (đơn vị)</label>
                    <input type="number" step="0.01" name="unit_cost" class="form-control" />
                </div>
                <div class="form-group" id="globalSupplierGroup" style="display:none;">
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
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeGlobalModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
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
    function openGlobalModal(type){
        const modal = document.getElementById('globalModal');
        const form = document.getElementById('globalForm');
        document.getElementById('globalModalTitle').innerText = 'Giao dịch tồn kho';
        document.getElementById('globalType').value = type || 'import';
        form.action = `/inventory/${document.getElementById('globalProduct').value}/adjust`;
        toggleGlobalFields();
        modal.style.display = 'block';
    }
    function closeGlobalModal(){ document.getElementById('globalModal').style.display='none'; }
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
    window.onclick = function(event){
        const modal = document.getElementById('adjustModal');
        if (event.target == modal) { modal.style.display = 'none'; }
    }
</script>
@endpush

@push('styles')
<style>
    .inv-header{padding:12px 0 0 0;border:none}
    .inv-tabs{gap:4px}
    /* Tabs UI */
    .tab-navigation{display:flex;align-items:center;justify-content:space-between}
    .tab-list{display:flex;gap:8px;align-items:center}
    .tab-item{display:inline-flex;align-items:center;padding:8px 12px;border:1px solid #e5e7eb;border-radius:9999px;background:#fff;color:#374151;text-decoration:none;font-weight:600;transition:all .15s ease}
    .tab-item:hover{background:#f3f4f6;border-color:#d1d5db}
    .tab-item.active{box-shadow:0 0 0 2px rgba(59,130,246,0.08) inset}
    .tab-all.active{background:#e5e7eb;color:#111827;border-color:#d1d5db}
    .tab-in.active{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
    .tab-low.active{background:#fffbeb;color:#92400e;border-color:#fde68a}
    .tab-out.active{background:#fee2e2;color:#991b1b;border-color:#fecaca}

    .btn-sm{padding:6px 10px;font-size:12px}
    .inv-toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:12px 0}
    .inv-toolbar .inv-search{flex:0 1 360px;max-width:360px;min-width:260px}
    .inv-toolbar .inv-controls{display:flex;gap:8px;align-items:center;white-space:nowrap}
    .inv-toolbar .inv-controls label{color:#6b7280;font-size:12px;white-space:nowrap;margin-right:2px}
    .inv-toolbar select.form-control{height:36px;padding:6px 10px}
    .inv-footer{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0}
    .inv-count{color:#6b7280}
    .inv-summary{display:flex;gap:12px;color:#374151;font-weight:600}
    .table td.product-name a{font-weight:600}
    .table td.product-name .subtitle{color:#6b7280;font-size:12px;margin-top:4px}

    .inv-table thead th{position:sticky;top:0;z-index:5;background:#f7fafc}
    .inv-table th.number,.inv-table td.number{text-align:right}
    .chip{padding:4px 8px;border-radius:14px;font-size:12px;font-weight:600;display:inline-block}
    .chip-success{background:#ecfdf5;color:#065f46}
    .chip-warning{background:#fffbeb;color:#92400e}
    .chip-danger{background:#fee2e2;color:#991b1b}
    .inv-table tfoot td{background:#f9fafb;border-top:1px solid #e5e7eb}
</style>
@endpush


