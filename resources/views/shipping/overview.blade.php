@extends('layouts.app')

@section('title', 'Tổng quan vận chuyển - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title">Tổng quan vận chuyển</h1>
        <div>
            <form method="GET" action="{{ route('shipping.overview') }}" style="display:flex; gap:8px; align-items:center;">
                <select name="range" class="form-control" onchange="this.form.submit()">
                    <option value="7d" {{ ($range ?? '7d')==='7d' ? 'selected' : '' }}>7 ngày qua</option>
                    <option value="30d" {{ ($range ?? '')==='30d' ? 'selected' : '' }}>30 ngày qua</option>
                    <option value="today" {{ ($range ?? '')==='today' ? 'selected' : '' }}>Hôm nay</option>
                </select>
                <select name="branch" class="form-control" onchange="this.form.submit()">
                    <option value="">Tất cả chi nhánh</option>
                </select>
                <select name="region" class="form-control" onchange="this.form.submit()">
                    <option value="">Khu vực</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="border-bottom:none;">
            <div class="grid" style="display:grid; grid-template-columns: repeat(6, 1fr); gap:12px;">
                <div class="stat-box">
                    <div class="stat-title">Chờ lấy hàng</div>
                    <div class="stat-value">{{ number_format($summary['pending_pickup']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã lấy hàng</div>
                    <div class="stat-value">{{ number_format($summary['picked_up']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đang giao hàng</div>
                    <div class="stat-value">{{ number_format($summary['in_transit']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Chờ giao lại</div>
                    <div class="stat-value">{{ number_format($summary['retry']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đang hoàn hàng</div>
                    <div class="stat-value">{{ number_format($summary['returning']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã hoàn</div>
                    <div class="stat-value">{{ number_format($summary['returned']) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cod_amount'], 0) }}đ</div>
                </div>
            </div>
        </div>

        <div class="card-body" style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
            <div class="chart-card">
                <div class="chart-title">Thời gian lấy hàng thành công trung bình</div>
                <div class="chart-empty">Chưa có dữ liệu báo cáo</div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Thời gian giao hàng thành công trung bình</div>
                <div class="chart-empty">Chưa có dữ liệu báo cáo</div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Tỉ lệ giao hàng thành công</div>
                <div class="chart-empty">Chưa có dữ liệu báo cáo</div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Tỉ trọng vận đơn</div>
                <div class="chart-empty">Chưa có dữ liệu báo cáo</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:600; color:#334155;">Vận đơn gần đây</h3>
            <a href="#" class="btn btn-primary" style="pointer-events:none; opacity:.6;">Quản lý vận đơn (sắp ra mắt)</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mã đơn</th>
                        <th>Mã vận đơn</th>
                        <th>Hãng VC</th>
                        <th>Trạng thái</th>
                        <th>COD</th>
                        <th>COD</th>
                        <th>Phí VC</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentShipments as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td>{{ $s->order_code }}</td>
                            <td>{{ $s->tracking_code }}</td>
                            <td>{{ $s->carrier }}</td>
                            <td><span class="badge">{{ $s->status }}</span></td>
                            <td>{{ number_format($s->cod_amount, 0) }}đ</td>
                            <td>{{ number_format($s->shipping_fee, 0) }}đ</td>
                            <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; color:#94a3b8;">Chưa có vận đơn</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .stat-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; }
        .stat-title { font-size:12px; color:#64748b; margin-bottom:8px; }
        .stat-value { font-size:20px; font-weight:700; color:#0f172a; }
        .stat-sub { font-size:12px; color:#94a3b8; }
        .chart-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:16px; min-height:180px; display:flex; align-items:center; justify-content:center; flex-direction:column; }
        .chart-title { align-self:flex-start; font-weight:600; color:#334155; margin-bottom:12px; }
        .chart-empty { color:#94a3b8; font-size:14px; }
        .badge { background:#eef2ff; color:#3730a3; padding:4px 8px; border-radius:12px; font-size:12px; }
    </style>
@endsection


