@extends('layouts.app')

@section('title', 'Sửa vận đơn - PerfumeShop')

@section('content')
    <div class="shipment-edit-page">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h1 class="page-title">Sửa vận đơn</h1>
        <div>
            <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline">Quay lại</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Thông tin vận đơn</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('shipments.update', $shipment) }}" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Hãng vận chuyển</label>
                    <input type="text" name="carrier" value="{{ old('carrier', $shipment->carrier) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Chi nhánh</label>
                    <input type="text" name="branch" value="{{ old('branch', $shipment->branch) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Khu vực</label>
                    <input type="text" name="region" value="{{ old('region', $shipment->region) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Số tiền COD</label>
                    <input type="number" step="0.01" name="cod_amount" value="{{ old('cod_amount', $shipment->cod_amount) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Phí vận chuyển</label>
                    <input type="number" step="0.01" name="shipping_fee" value="{{ old('shipping_fee', $shipment->shipping_fee) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Khối lượng (gram)</label>
                    <input type="number" name="weight_grams" value="{{ old('weight_grams', $shipment->weight_grams) }}" class="form-control">
                </div>

                <div style="grid-column: span 2; height:1px; background:#e5e7eb;"></div>

                <div class="form-group">
                    <label class="form-label">Người nhận</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $shipment->recipient_name) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">SĐT người nhận</label>
                    <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $shipment->recipient_phone) }}" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address_line" value="{{ old('address_line', $shipment->address_line) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tỉnh/Thành</label>
                    <input type="text" name="province" value="{{ old('province', $shipment->province) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Phường/Xã</label>
                    <input type="text" name="ward" value="{{ old('ward', $shipment->ward) }}" class="form-control">
                </div>

                <div style="grid-column: span 2; display:flex; justify-content:flex-end; gap:12px;">
                    <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline">Hủy</a>
                    <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection


