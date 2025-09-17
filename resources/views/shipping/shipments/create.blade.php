@extends('layouts.app')

@section('title', 'Tạo vận đơn - PerfumeShop')

@section('content')
    <div class="shipment-create-page">
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title"><i class="fas fa-truck-fast"></i> Tạo vận đơn</h1>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('shipments.index') }}" class="btn btn-outline">Quay lại</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin vận đơn</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('shipments.store') }}" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                @csrf
                <!-- Danh sách đơn hàng: có thể thêm nhiều dòng -->
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Đơn hàng trong vận đơn</label>
                    <div id="ordersList" style="display:flex; flex-direction:column; gap:8px;">
                        <div class="order-row" style="display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:8px; align-items:end;">
                            <div>
                                <label class="form-label">Mã đơn hàng</label>
                                <input type="text" name="orders[0][order_code]" value="{{ old('orders.0.order_code') }}" class="form-control order-code-input" placeholder="VD: DH20250916ABCD" onblur="handleOrderCodeBlur(this)" oninput="clearInputError(this)">
                                <small class="error-msg" style="display:none;"></small>
                            </div>
                            <div>
                                <label class="form-label">COD (đ)</label>
                                <input type="number" step="0.01" name="orders[0][cod_amount]" value="{{ old('orders.0.cod_amount', 0) }}" class="form-control cod-input" oninput="recalcTotalCod()">
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-outline" onclick="addOrderRow()">Thêm</button>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:8px; display:flex; gap:12px; align-items:center;">
                        <strong>Tổng COD:</strong>
                        <span id="totalCod" style="font-weight:600;">0</span>
                        <span>đ</span>
                    </div>
                </div>
                <!-- Mã vận đơn sinh tự động trong backend -->

                <div class="form-group">
                    <label class="form-label">Hãng vận chuyển</label>
                    <input type="text" name="carrier" value="{{ old('carrier') }}" class="form-control" placeholder="VD: GHN/GHTK/Nội bộ">
                </div>
                <div class="form-group">
                    <label class="form-label">Chi nhánh</label>
                    <input type="text" name="branch" value="{{ old('branch') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Khu vực</label>
                    <input type="text" name="region" value="{{ old('region') }}" class="form-control" placeholder="VD: HCM/HN">
                </div>
                <!-- Tổng COD sẽ tự tính từ các đơn, không nhập ở đây -->

                <div class="form-group">
                    <label class="form-label">Phí vận chuyển (đ)</label>
                    <input type="number" step="0.01" name="shipping_fee" value="{{ old('shipping_fee', 0) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Khối lượng (gram)</label>
                    <input type="number" name="weight_grams" value="{{ old('weight_grams', 0) }}" class="form-control">
                </div>

                <div style="grid-column: span 2; height:1px; background:#e5e7eb; margin:8px 0;"></div>

                <div class="form-group">
                    <label class="form-label">Người nhận</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">SĐT người nhận</label>
                    <input type="text" name="recipient_phone" value="{{ old('recipient_phone') }}" class="form-control">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address_line" value="{{ old('address_line') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Tỉnh/Thành</label>
                    <input type="text" name="province" value="{{ old('province') }}" class="form-control">
                </div>
                <!-- Bỏ trường Quận/Huyện theo yêu cầu -->
                <div class="form-group">
                    <label class="form-label">Phường/Xã</label>
                    <input type="text" name="ward" value="{{ old('ward') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái ban đầu</label>
                    <select name="status" class="form-control">
                        <option value="pending_pickup" {{ old('status','pending_pickup')==='pending_pickup' ? 'selected' : '' }}>Chờ lấy hàng</option>
                        <option value="picked_up" {{ old('status')==='picked_up' ? 'selected' : '' }}>Đã lấy hàng</option>
                        <option value="in_transit" {{ old('status')==='in_transit' ? 'selected' : '' }}>Đang giao hàng</option>
                    </select>
                </div>

                <div style="grid-column: span 2; display:flex; gap:12px; justify-content:flex-end; margin-top:8px;">
                    <button class="btn btn-outline" type="reset">Làm lại</button>
                    <button class="btn btn-primary" type="submit">Tạo vận đơn</button>
                </div>
            </form>
        </div>
    </div>
    <style>
        /* Đồng bộ form với layout chung, giới hạn phạm vi */
        .shipment-create-page .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 24px; }
        .shipment-create-page .page-title { font-size: 28px; font-weight: 700; color:#0f172a; display:flex; align-items:center; gap:12px; }
        .shipment-create-page .card { border-radius: 16px; border:1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(2,6,23,0.04); overflow:hidden; }
        .shipment-create-page .card-header { background:linear-gradient(135deg,#f8fafc,#eef2ff); padding:16px 20px; border-bottom:1px solid #e5e7eb; }
        .shipment-create-page .card-title { margin:0; font-weight:700; color:#111827; }
        .shipment-create-page .form-group .form-label { font-weight:600; color:#334155; font-size:13px; text-transform:uppercase; letter-spacing:.4px; }
        .shipment-create-page .form-control { border-radius:10px; border:1px solid #d1d5db; background:#f9fafb; transition:all .2s ease; height:40px; }
        .shipment-create-page .form-control:focus { background:#fff; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
        .shipment-create-page .btn { height:40px; border-radius:10px; font-weight:600; }
        .shipment-create-page .btn-outline { background:#fff; border:1px solid #e2e8f0; color:#1f2937; }
        .shipment-create-page .btn-outline:hover { background:#f1f5f9; }
        .shipment-create-page .btn-primary { background:#3b82f6; border-color:#3b82f6; }
        .shipment-create-page .btn-primary:hover { filter:brightness(0.95); }
        #ordersList .order-row { background:#f8fafc; border:1px dashed #e5e7eb; padding:12px; border-radius:12px; }
        #totalCod { color:#0f766e; font-size:16px; }
        .input-error { border-color:#ef4444 !important; box-shadow:0 0 0 3px rgba(239,68,68,.15) !important; }
        .error-msg { color:#ef4444; font-size:12px; margin-top:4px; display:block; }
    </style>
    <script>
        function addOrderRow(){
            const list = document.getElementById('ordersList');
            const index = list.querySelectorAll('.order-row').length;
            const row = document.createElement('div');
            row.className = 'order-row';
            row.style = 'display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:8px; align-items:end;';
            row.innerHTML = `
                <div>
                    <label class="form-label">Mã đơn hàng</label>
                    <input type="text" name="orders[${index}][order_code]" class="form-control order-code-input" placeholder="VD: DH20250916ABCD" onblur="handleOrderCodeBlur(this)" oninput="clearInputError(this)">
                    <small class="error-msg" style="display:none;"></small>
                </div>
                <div>
                    <label class="form-label">COD (đ)</label>
                    <input type="number" step="0.01" name="orders[${index}][cod_amount]" value="0" class="form-control cod-input" oninput="recalcTotalCod()">
                </div>
                <div>
                    <label class="form-label">Ghi chú</label>
                    <input type="text" class="form-control" placeholder="(tuỳ chọn)">
                </div>
                <div>
                    <button type="button" class="btn btn-outline" onclick="removeOrderRow(this)">Xoá</button>
                </div>
            `;
            list.appendChild(row);
            recalcTotalCod();
        }

        function removeOrderRow(btn){
            const row = btn.closest('.order-row');
            if(row){ row.remove(); }
            recalcTotalCod();
        }

        function recalcTotalCod(){
            let total = 0;
            document.querySelectorAll('#ordersList .cod-input').forEach(el => {
                const v = parseFloat(el.value || '0');
                if(!isNaN(v)) total += v;
            });
            document.getElementById('totalCod').textContent = Intl.NumberFormat('vi-VN').format(total);
        }

        function showInputError(inputEl, message){
            inputEl.classList.add('input-error');
            const msgEl = inputEl.parentElement.querySelector('.error-msg');
            if(msgEl){ msgEl.textContent = message || ''; msgEl.style.display = message ? 'block' : 'none'; }
        }

        function clearInputError(inputEl){
            inputEl.classList.remove('input-error');
            const msgEl = inputEl.parentElement.querySelector('.error-msg');
            if(msgEl){ msgEl.textContent = ''; msgEl.style.display = 'none'; }
        }

        async function handleOrderCodeBlur(inputEl){
            const orderNumber = inputEl.value?.trim();
            if(!orderNumber){ return; }
            // Kiểm tra trùng mã đơn giữa các dòng
            const normalized = orderNumber.toUpperCase();
            const allCodes = Array.from(document.querySelectorAll('#ordersList .order-row .order-code-input'));
            const duplicates = allCodes.filter(el => el !== inputEl && (el.value || '').trim().toUpperCase() === normalized);
            if(duplicates.length > 0){
                showInputError(inputEl, 'Mã đơn đã tồn tại ở dòng khác');
                inputEl.focus();
                if(inputEl.select) inputEl.select();
                return;
            }
            try{
                const res = await fetch(`/api/orders/by-number/${encodeURIComponent(orderNumber)}`);
                if(!res.ok){ return; }
                const data = await res.json();
                if(data && data.found){
                    clearInputError(inputEl);
                    // Nếu là dòng đầu tiên, auto-fill thông tin người nhận nếu đang trống
                    const first = document.querySelector('#ordersList .order-row input[name^="orders[0][order_code]"]');
                    if(first && first === inputEl){
                        const n = document.querySelector('input[name="recipient_name"]');
                        const p = document.querySelector('input[name="recipient_phone"]');
                        const a = document.querySelector('input[name="address_line"]');
                        const w = document.querySelector('input[name="ward"]');
                        const c = document.querySelector('input[name="province"]');
                        if(n && !n.value) n.value = data.customer_name || '';
                        if(p && !p.value) p.value = data.phone || '';
                        if(a && !a.value) a.value = data.address || '';
                        if(w && !w.value) w.value = data.ward || '';
                        if(c && !c.value) c.value = data.city || '';
                    }
                    // Auto-fill COD của chính dòng đang nhập nếu trống hoặc 0
                    const row = inputEl.closest('.order-row');
                    if(row){
                        const codInput = row.querySelector('input[name$="[cod_amount]"]');
                        if(codInput){
                            const cur = parseFloat(codInput.value || '0');
                            if(isNaN(cur) || cur === 0){
                                codInput.value = data.final_amount ?? 0;
                                recalcTotalCod();
                            }
                        }
                    }
                }
            }catch(e){
                showInputError(inputEl, 'Không tìm thấy đơn hàng');
            }
        }

        // Khởi tạo tổng COD lần đầu
        document.addEventListener('DOMContentLoaded', recalcTotalCod);
    </script>
    </div>
@endsection


