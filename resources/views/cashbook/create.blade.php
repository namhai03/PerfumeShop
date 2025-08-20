@extends('layouts.app')

@section('title', 'Tạo phiếu - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">
                @if($type == 'receipt')
                    Tạo phiếu thu
                @elseif($type == 'payment')
                    Tạo phiếu chi
                @else
                    Chuyển quỹ nội bộ
                @endif
            </h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" form="voucherForm" class="btn btn-primary">Tạo phiếu</button>
        </div>
    </div>

    <form id="voucherForm" action="{{ route('cashbook.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

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

            <div class="form-group">
                <label class="form-label">Diễn giải*</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Nhập diễn giải" required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Ghi chú</label>
                <textarea name="note" rows="2" class="form-control" placeholder="Nhập ghi chú"></textarea>
            </div>
        </div>

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
