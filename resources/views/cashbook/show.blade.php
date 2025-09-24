@extends('layouts.app')

@section('title', 'Chi tiết phiếu - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline" style="padding: 8px 12px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Chi tiết phiếu</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            @if($voucher->status === 'pending')
                <form action="{{ route('cashbook.approve', $voucher->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-primary" onclick="return confirm('Duyệt phiếu này?');">Duyệt</button>
                </form>
                <form action="{{ route('cashbook.cancel', $voucher->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-danger" onclick="return confirm('Hủy phiếu này?');">Hủy</button>
                </form>
            @elseif($voucher->status === 'approved')
                <form action="{{ route('cashbook.cancel', $voucher->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-danger" onclick="return confirm('Hủy phiếu đã duyệt? Sẽ hoàn tác số dư.');">Hủy</button>
                </form>
            @endif
            <a href="{{ route('cashbook.edit', $voucher->id) }}" class="btn btn-outline">Sửa</a>
            <a href="{{ route('cashbook.index') }}" class="btn btn-outline">Quay lại</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Thông tin phiếu</h3>
        </div>
        
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Mã phiếu:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->voucher_code }}</div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Loại phiếu:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->type_text }}</div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Trạng thái:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->status_text }}</div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Số tiền:</label>
                @if($voucher->type == 'receipt')
                    <div style="font-size: 18px; font-weight: 600; color: #16a34a;">+{{ number_format((float)$voucher->amount, 0, ',', '.') }} đ</div>
                @else
                    <div style="font-size: 18px; font-weight: 600; color: #dc2626;">-{{ number_format((float)$voucher->amount, 0, ',', '.') }} đ</div>
                @endif
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Ngày giao dịch:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->transaction_date?->format('d/m/Y') }}</div>
            </div>
            <div style="flex: 1;">
                <label style="font-weight: 600; color: #4a5568;">Ngày tạo:</label>
                <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->created_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Diễn giải:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->description }}</div>
        </div>

        @if($voucher->reason)
        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Lý do:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->reason }}</div>
        </div>
        @endif

        @if($voucher->payer_name)
        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Tên người gửi:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->payer_name }}</div>
        </div>
        @endif

        

        

        @if($voucher->note)
        <div style="margin-bottom: 16px;">
            <label style="font-weight: 600; color: #4a5568;">Ghi chú:</label>
            <div style="font-size: 16px; color: #2c3e50;">{{ $voucher->note }}</div>
        </div>
        @endif
    </div>

    @if($voucher->attachments->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3>Ảnh chứng từ</h3>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            @foreach($voucher->attachments as $attachment)
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center;">
                <img src="{{ $attachment->file_path }}" alt="{{ $attachment->file_name }}" loading="lazy" decoding="async" style="max-width: 100%; height: auto; border-radius: 4px;">
                <div style="margin-top: 8px; font-size: 12px; color: #718096;">{{ $attachment->file_name }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
@endsection
