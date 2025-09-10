@extends('layouts.app')

@section('title', 'Sửa phiếu - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.show', $voucher->id) }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Sửa phiếu</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.show', $voucher->id) }}" class="btn btn-outline">Hủy</a>
            <button type="submit" form="voucherForm" class="btn btn-primary">Cập nhật</button>
        </div>
    </div>

    <form id="voucherForm" action="{{ route('cashbook.update', $voucher->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Thông tin chung -->
        <div class="card">
            <div class="card-header">
                <h3>Thông tin chung</h3>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Loại phiếu</label>
                    <input type="text" class="form-control" value="{{ $voucher->type_text }}" readonly>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Mã phiếu</label>
                    <input type="text" class="form-control" value="{{ $voucher->voucher_code }}" readonly>
                </div>
            </div>

                <div style="display: flex; gap: 12px;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Lý do</label>
                        <select name="reason" class="form-control">
                            <option value="">Chọn lý do</option>
                            @if($voucher->type == 'receipt')
                                <option value="thanh_toan_hang" {{ $voucher->reason == 'thanh_toan_hang' ? 'selected' : '' }}>Thanh toán hàng</option>
                                <option value="hoan_tien" {{ $voucher->reason == 'hoan_tien' ? 'selected' : '' }}>Hoàn tiền</option>
                                <option value="thu_no" {{ $voucher->reason == 'thu_no' ? 'selected' : '' }}>Thu nợ</option>
                            @else
                                <option value="mua_hang" {{ $voucher->reason == 'mua_hang' ? 'selected' : '' }}>Mua hàng</option>
                                <option value="luong" {{ $voucher->reason == 'luong' ? 'selected' : '' }}>Lương</option>
                                <option value="chi_phi" {{ $voucher->reason == 'chi_phi' ? 'selected' : '' }}>Chi phí</option>
                            @endif
                            <option value="khac" {{ $voucher->reason == 'khac' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Giá trị*</label>
                        <input type="number" name="amount" class="form-control" placeholder="Nhập giá trị" value="{{ $voucher->amount }}" required>
                    </div>
                </div>

            <div class="form-group">
                <label class="form-label">Diễn giải*</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Nhập diễn giải" required>{{ $voucher->description }}</textarea>
            </div>
        </div>

        <!-- Thông tin bổ sung -->
        <div class="card">
            <div class="card-header">
                <h3>Thông tin bổ sung</h3>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Ngày giao dịch*</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ $voucher->transaction_date?->format('Y-m-d') }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Tham chiếu</label>
                    <input type="text" name="reference" class="form-control" placeholder="Nhập tham chiếu" value="{{ $voucher->reference }}">
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Tên người gửi</label>
                    <input type="text" name="payer_name" class="form-control" value="{{ $voucher->payer_name }}" placeholder="Nhập tên người gửi">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Ghi chú</label>
                    <input type="text" name="note" class="form-control" value="{{ $voucher->note }}" placeholder="Nhập ghi chú">
                </div>
            </div>
        </div>
    </form>
@endsection
