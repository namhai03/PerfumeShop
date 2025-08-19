@extends('layouts.app')

@section('title', 'Tồn kho sản phẩm - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title" style="margin-bottom: 8px;">{{ $product->name }}</h1>
            <div style="color:#6b7280;">SKU: {{ $product->sku }} • Barcode: {{ $product->barcode ?? '-' }}</div>
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
                <div class="card-header">
                    <h3>Lịch sử biến động</h3>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Loại</th>
                                <th>Thay đổi</th>
                                <th>Trước</th>
                                <th>Sau</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $m)
                                <tr>
                                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ ucfirst($m->type) }}</td>
                                    <td class="{{ $m->quantity_change >= 0 ? 'qty-pos' : 'qty-neg' }}" style="font-weight:600;">{{ $m->quantity_change >= 0 ? '+' : '' }}{{ $m->quantity_change }}</td>
                                    <td>{{ $m->before_stock }}</td>
                                    <td>{{ $m->after_stock }}</td>
                                    <td class="product-name">{{ $m->note }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align:center; padding: 28px; color:#6b7280;">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end;">{{ $movements->links() }}</div>
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
    </script>
    @endpush
@endsection


