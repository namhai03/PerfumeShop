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
                <div class="form-group">
                    <label class="form-label">Mã đơn hàng</label>
                    <input type="text" name="order_code" value="{{ old('order_code') }}" class="form-control" placeholder="VD: DH20250916ABCD" onblur="autoFillRecipient(this.value)">
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
                <div class="form-group">
                    <label class="form-label">Số tiền COD (đ)</label>
                    <input type="number" step="0.01" name="cod_amount" value="{{ old('cod_amount', 0) }}" class="form-control">
                </div>

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
                    <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">SĐT người nhận</label>
                    <input type="text" name="recipient_phone" value="{{ old('recipient_phone') }}" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address_line" value="{{ old('address_line') }}" class="form-control" required>
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
                        <option value="pending_pickup" {{ old('status')==='pending_pickup' ? 'selected' : '' }}>Chờ lấy hàng</option>
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
        .shipment-create-page .page-title { font-size: 28px; font-weight: 600; color:#1e293b; display:flex; align-items:center; gap:12px; }
        .shipment-create-page .card { border-radius: 12px; border:1px solid #e2e8f0; }
        .shipment-create-page .card-header { background:#f8fafc; }
        .shipment-create-page .form-group .form-label { font-weight:500; color:#4b5563; }
    </style>
    <script>
        async function autoFillRecipient(orderNumber){
            if(!orderNumber){ return; }
            try{
                const res = await fetch(`/api/orders/by-number/${encodeURIComponent(orderNumber)}`);
                if(!res.ok){ return; }
                const data = await res.json();
                if(data && data.found){
                    document.querySelector('input[name="recipient_name"]').value = data.customer_name || '';
                    document.querySelector('input[name="recipient_phone"]').value = data.phone || '';
                    document.querySelector('input[name="address_line"]').value = data.address || '';
                    document.querySelector('input[name="ward"]').value = data.ward || '';
                    document.querySelector('input[name="province"]').value = data.city || '';
                }
            }catch(e){
                console.error('Auto-fill recipient failed', e);
            }
        }
    </script>
    </div>
@endsection


