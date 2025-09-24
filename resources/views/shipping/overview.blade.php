@extends('layouts.app')

@section('title', 'Tổng quan vận chuyển - PerfumeShop')

@section('content')
    <div class="shipping-overview-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1 class="page-title">Tổng quan vận chuyển</h1>
        <div>
            <form method="GET" action="{{ route('shipping.overview') }}" style="display:flex; gap:8px; align-items:center;">
                <select name="range" class="form-control" onchange="this.form.submit()">
                    <option value="today" {{ ($range ?? '')==='today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="3d" {{ ($range ?? '')==='3d' ? 'selected' : '' }}>3 ngày qua</option>
                    <option value="7d" {{ ($range ?? '7d')==='7d' ? 'selected' : '' }}>1 tuần qua</option>
                    <option value="14d" {{ ($range ?? '')==='14d' ? 'selected' : '' }}>2 tuần qua</option>
                    <option value="30d" {{ ($range ?? '')==='30d' ? 'selected' : '' }}>1 tháng qua</option>
                    <option value="90d" {{ ($range ?? '')==='90d' ? 'selected' : '' }}>3 tháng qua</option>
                </select>
                <select name="province" class="form-control" onchange="this.form.submit()">
                    <option value="">Khu vực</option>
                    @foreach(($provinces ?? []) as $p)
                        <option value="{{ $p }}" {{ ($province ?? '')===$p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="border-bottom:none;">
            <div class="grid" style="display:grid; grid-template-columns: repeat(8, 1fr); gap:12px;">
                <div class="stat-box">
                    <div class="stat-title">Chờ lấy hàng</div>
                    <div class="stat-value">{{ number_format($summary['pending_pickup']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['pending_pickup']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã lấy hàng</div>
                    <div class="stat-value">{{ number_format($summary['picked_up']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['picked_up']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đang giao hàng</div>
                    <div class="stat-value">{{ number_format($summary['in_transit']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['in_transit']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đang hoàn hàng</div>
                    <div class="stat-value">{{ number_format($summary['returning']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['returning']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã giao</div>
                    <div class="stat-value">{{ number_format($summary['delivered']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['delivered']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Thất bại</div>
                    <div class="stat-value">{{ number_format($summary['failed']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['failed']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã hoàn</div>
                    <div class="stat-value">{{ number_format($summary['returned']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['returned']['cod'] ?? 0, 0) }}đ</div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Đã hủy</div>
                    <div class="stat-value">{{ number_format($summary['cancelled']['count'] ?? 0) }}</div>
                    <div class="stat-sub">COD: {{ number_format($summary['cancelled']['cod'] ?? 0, 0) }}đ</div>
                </div>
            </div>
        </div>

        <!-- Dashboard 2x2 -->
        <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 16px;">
            <!-- Biểu đồ 1: Phân bố trạng thái vận đơn -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Phân bố trạng thái vận đơn</h4>
                </div>
                <div id="chartStatusDistribution" style="height: 300px;"></div>
            </div>

            <!-- Biểu đồ 2: Doanh thu giao thành công theo ngày (Cột + đường MA7) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Doanh thu </h4>
                </div>
                <div id="chartDeliveredRevenue" style="height: 300px;"></div>
            </div>

            <!-- Biểu đồ 3: Số lượng vận đơn theo thời gian trong ngày -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Số lượng vận đơn theo giờ trong ngày</h4>
                </div>
                <div id="chartShipmentsByHour" style="height: 300px;"></div>
            </div>

            <!-- Biểu đồ 4: Thời gian giao trung bình theo hãng (giờ) - Bar ngang Top 5 -->
            <div class="chart-card">
                <div class="chart-header">
                    <h4>Thời gian giao trung bình theo hãng (giờ)</h4>
                </div>
                <div id="chartDeliveryTimeByCarrier" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:600; color:#334155;">Vận đơn gần đây</h3>
           
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
        .shipping-overview-page .stat-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; }
        .shipping-overview-page .stat-title { font-size:12px; color:#64748b; margin-bottom:8px; }
        .shipping-overview-page .stat-value { font-size:20px; font-weight:700; color:#0f172a; }
        .shipping-overview-page .stat-sub { font-size:12px; color:#94a3b8; }
        /* Chart styles đã bỏ */
        .shipping-overview-page .badge { background:#eef2ff; color:#3730a3; padding:4px 8px; border-radius:12px; font-size:12px; }
        .shipping-overview-page .charts-grid { display:grid; grid-template-columns: repeat(2, 1fr); grid-auto-rows: 320px; gap:16px; }
        .shipping-overview-page .chart-item { background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:8px; height:100%; }
        .shipping-overview-page .chart-item > div { height:100%; }
        
        /* Dashboard styles */
        .shipping-overview-page .dashboard-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 16px; 
            margin-top: 16px; 
        }
        .shipping-overview-page .chart-card { 
            background: #ffffff; 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 16px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .shipping-overview-page .chart-header { 
            margin-bottom: 16px; 
            border-bottom: 1px solid #f1f5f9; 
            padding-bottom: 12px; 
        }
        .shipping-overview-page .chart-header h4 { 
            font-size: 16px; 
            font-weight: 600; 
            color: #1e293b; 
            margin: 0; 
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .shipping-overview-page .dashboard-grid { 
                grid-template-columns: 1fr; 
            }
        }
    </style>
    <!-- ApexCharts -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/apexcharts" as="script" crossorigin>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer crossorigin></script>
    <script>
        async function loadShippingDashboard(){
            try{
                const params = new URLSearchParams({ 
                    range: '{{ $range ?? "7d" }}',
                    province: '{{ $province ?? "" }}'
                });
                const res = await fetch(`/shipping/overview-data?${params.toString()}`);
                if(!res.ok){ return; }
                const data = await res.json();

                // 1) Status Distribution - Pie Chart
                const statusChart = new ApexCharts(document.querySelector('#chartStatusDistribution'), {
                    chart: { 
                        type: 'pie', 
                        toolbar: { show: false },
                        height: 300
                    },
                    series: data.status_distribution?.map(item => item.value) || [],
                    labels: data.status_distribution?.map(item => item.label) || [],
                    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#22c55e'],
                    legend: { 
                        position: 'bottom',
                        fontSize: '12px'
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + " vận đơn"
                            }
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%'
                            }
                        }
                    }
                });
                statusChart.render();

                // 2) Doanh thu giao thành công theo ngày (Line) - Y: K/M/B, X: tick hợp lý
                const deliveredRevenue = data.delivered_revenue || [];
                const revCategories = deliveredRevenue.map(item => {
                    const d = new Date(item.date);
                    return d.toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
                });
                const revVals = deliveredRevenue.map(item => item.amount);
                const yAbbr = (v) => {
                    const abs = Math.abs(v);
                    if (abs >= 1_000_000_000) return (v/1_000_000_000).toFixed(1).replace(/\.0$/, '') + 'B';
                    if (abs >= 1_000_000) return (v/1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
                    if (abs >= 1_000) return (v/1_000).toFixed(1).replace(/\.0$/, '') + 'K';
                    return new Intl.NumberFormat('vi-VN').format(v);
                };
                const tickAmt = Math.min(10, Math.max(4, Math.floor(revCategories.length / 7)));
                const revenueChart = new ApexCharts(document.querySelector('#chartDeliveredRevenue'), {
                    chart: { type: 'line', toolbar: { show:false }, height: 300 },
                    series: [{ name: 'Doanh thu COD', data: revVals }],
                    xaxis: { 
                        categories: revCategories,
                        tickAmount: tickAmt,
                        labels: { rotate: -15, hideOverlappingLabels: true, trim: true }
                    },
                    yaxis: { labels: { formatter: yAbbr } },
                    colors: ['#0ea5e9'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 2 },
                    tooltip: { y: { formatter: v => new Intl.NumberFormat('vi-VN').format(v) + ' đ' } }
                });
                revenueChart.render();

                // 3) Số lượng vận đơn theo giờ trong ngày (Line 24 điểm)
                const byHour = data.shipments_by_hour || [];
                const hourLabels = Array.from({length:24}).map((_,i)=> `${i}h`);
                const hourChart = new ApexCharts(document.querySelector('#chartShipmentsByHour'), {
                    chart: { type:'line', toolbar:{ show:false }, height: 300 },
                    series: [{ name:'Vận đơn', data: byHour }],
                    xaxis: { categories: hourLabels, tickAmount: 12 },
                    yaxis: { min: 0, forceNiceScale: true },
                    colors: ['#10b981'],
                    dataLabels: { enabled:false },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 3 }
                });
                hourChart.render();

                // 4) Thời gian giao trung bình theo hãng (bar ngang top 5)
                const byCarrier = data.delivery_time_by_carrier || [];
                const carriers = byCarrier.map(x => x.carrier);
                const avgHours = byCarrier.map(x => x.avg_hours);
                const carrierChart = new ApexCharts(document.querySelector('#chartDeliveryTimeByCarrier'), {
                    chart: { type: 'bar', toolbar: { show:false }, height: 300 },
                    series: [{ name: 'Giờ', data: avgHours }],
                    xaxis: { categories: carriers },
                    colors: ['#f59e0b'],
                    dataLabels: { enabled: true },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4 } }
                });
                carrierChart.render();
            }catch(e){
                console.error('Load dashboard failed', e);
            }
        }
        document.addEventListener('DOMContentLoaded', loadShippingDashboard);
        
        // Reload dashboard when filters change
        document.querySelectorAll('select[name="range"], select[name="province"]').forEach(select => {
            select.addEventListener('change', function() {
                // Reload dashboard with new parameters
                setTimeout(() => {
                    loadShippingDashboard();
                }, 100);
            });
        });
    </script>
    </div>
@endsection


