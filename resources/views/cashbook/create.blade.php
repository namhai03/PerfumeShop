@extends('layouts.app')

@section('title', 'Tạo phiếu - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Tạo phiếu</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" form="voucherForm" class="btn btn-primary">Tạo phiếu</button>
        </div>
    </div>

    <form id="voucherForm" action="{{ route('cashbook.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3>Loại phiếu</h3>
            </div>
            <div class="form-group" style="max-width: 360px;">
                <label class="form-label">Chọn loại phiếu*</label>
                <select class="form-control" name="type" id="voucherType" required>
                    <option value="">-- Chọn --</option>
                    <option value="receipt" {{ ($type ?? request('type')) == 'receipt' ? 'selected' : '' }}>Phiếu thu</option>
                    <option value="payment" {{ ($type ?? request('type')) == 'payment' ? 'selected' : '' }}>Phiếu chi</option>
                    <option value="transfer" {{ ($type ?? request('type')) == 'transfer' ? 'selected' : '' }}>Chuyển quỹ nội bộ</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Thông tin phiếu</h3>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Giá trị*</label>
                    <input type="number" name="amount" class="form-control" placeholder="Nhập giá trị" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Ngày giao dịch*</label>
                    <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Tên người gửi</label>
                    <input type="text" name="payer_name" class="form-control" placeholder="Nhập tên người gửi">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Lý do</label>
                    <select name="reason" id="reason" class="form-control">
                        <option value="">Chọn lý do</option>
                    </select>
                </div>
            </div>

            

            <div class="form-group">
                <label class="form-label">Diễn giải*</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Nhập diễn giải" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <textarea name="note" rows="2" class="form-control" placeholder="Nhập ghi chú"></textarea>
            </div>

            
        </div>

        <!-- Bỏ hạch toán tài khoản theo yêu cầu -->

        <div class="card">
            <div class="card-header">
                <h3>Ảnh chứng từ</h3>
            </div>
            
            <div style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 40px; text-align: center; background: #fafbfc;">
                <input type="file" name="attachments[]" multiple accept="image/*">
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const typeSelect = document.getElementById('voucherType');
    const reasonSelect = document.getElementById('reason');

    function populateReasons(type){
        const map = {
            receipt: [
                {v:'thanh_toan_hang', t:'Thanh toán hàng'},
                {v:'hoan_tien', t:'Hoàn tiền'},
                {v:'thu_no', t:'Thu nợ'},
                {v:'khac', t:'Khác'}
            ],
            payment: [
                {v:'mua_hang', t:'Mua hàng'},
                {v:'luong', t:'Lương'},
                {v:'chi_phi', t:'Chi phí'},
                {v:'khac', t:'Khác'}
            ]
        };
        reasonSelect.innerHTML = '<option value="">Chọn lý do</option>' + (map[type]||[]).map(o=>`<option value="${o.v}">${o.t}</option>`).join('');
    }

    function applyTypeUI(){
        const type = typeSelect.value;
        // chỉ còn receipt/payment
        populateReasons(type || 'receipt');
    }

    typeSelect.addEventListener('change', applyTypeUI);
    applyTypeUI();
});
</script>
@endpush
