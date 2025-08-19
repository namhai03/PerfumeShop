@extends('layouts.app')

@section('title', 'Lịch sử tồn kho - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom: 24px;">
        <h1 class="page-title">Lịch sử tồn kho</h1>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-warehouse"></i> Quản lý kho</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('inventory.history') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div style="min-width: 220px;">
                <label class="form-label">Sản phẩm</label>
                <select name="product_id" class="form-control">
                    <option value="">-- Tất cả --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id')==$p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 180px;">
                <label class="form-label">Loại giao dịch</label>
                <select name="type" class="form-control">
                    <option value="">-- Tất cả --</option>
                    @foreach(['import'=>'Nhập','export'=>'Xuất','adjust'=>'Điều chỉnh','stocktake'=>'Kiểm kê','return'=>'Trả về','damage'=>'Hư hỏng'] as $k=>$v)
                        <option value="{{ $k }}" {{ request('type')==$k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Từ ngày</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control" />
            </div>
            <div>
                <label class="form-label">Đến ngày</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control" />
            </div>
            <div>
                <button class="btn btn-outline" type="submit"><i class="fas fa-filter"></i> Lọc</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Sản phẩm</th>
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
                            <td>{{ $m->product->name }} ({{ $m->product->sku }})</td>
                            <td>{{ ucfirst($m->type) }}</td>
                            <td style="color: {{ $m->quantity_change >= 0 ? '#16a34a' : '#dc2626' }}; font-weight:600;">{{ $m->quantity_change >= 0 ? '+' : '' }}{{ $m->quantity_change }}</td>
                            <td>{{ $m->before_stock }}</td>
                            <td>{{ $m->after_stock }}</td>
                            <td class="product-name">{{ $m->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px; color:#6c757d;">Chưa có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer" style="display:flex; justify-content: flex-end;">
            {{ $movements->links() }}
        </div>
    </div>
@endsection


