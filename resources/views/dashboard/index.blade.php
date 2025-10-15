@extends('layouts.app')

@section('title', 'Dashboard Tổng quan - PerfumeShop')

@section('content')
<div class="dashboard-page">
    <!-- Header với bộ lọc thời gian -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">Dashboard Tổng quan</h1>
        <div class="period-selector">
            <div class="period-buttons">
                <button class="period-btn" data-period="7d">7 ngày</button>
                <button class="period-btn active" data-period="30d">30 ngày</button>
                <button class="period-btn" data-period="90d">90 ngày</button>
                <button class="period-btn" data-period="1y">1 năm</button>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 32px;">
        <!-- Doanh thu -->
        <div class="kpi-card" id="kpi-revenue">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Doanh thu</div>
                <div class="kpi-value" id="revenue-value">0</div>
                <div class="kpi-change" id="revenue-change">
                    <span class="change-icon"></span>
                    <span class="change-text">0%</span>
                </div>
            </div>
        </div>

        <!-- Đơn hàng -->
        <div class="kpi-card" id="kpi-orders">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Đơn hàng</div>
                <div class="kpi-value" id="orders-value">0</div>
                <div class="kpi-change" id="orders-change">
                    <span class="change-icon"></span>
                    <span class="change-text">0%</span>
                </div>
            </div>
        </div>

        <!-- Khách hàng mới -->
        <div class="kpi-card" id="kpi-customers">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Khách hàng mới</div>
                <div class="kpi-value" id="customers-value">0</div>
                <div class="kpi-change" id="customers-change">
                    <span class="change-icon"></span>
                    <span class="change-text">0%</span>
                </div>
            </div>
        </div>

       

        <!-- Cảnh báo tồn kho -->
        <div class="kpi-card" id="kpi-inventory">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Cảnh báo tồn kho</div>
                <div class="kpi-value" id="inventory-value">0</div>
                <div class="kpi-change" id="inventory-change">
                    <span class="change-icon"></span>
                    <span class="change-text">Sản phẩm</span>
                </div>
            </div>
        </div>

        <!-- Đơn chờ xử lý -->
        <div class="kpi-card" id="kpi-pending">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="kpi-content">
                <div class="kpi-label">Đơn chờ xử lý</div>
                <div class="kpi-value" id="pending-value">0</div>
                <div class="kpi-change" id="pending-change">
                    <span class="change-icon"></span>
                    <span class="change-text">Đơn hàng</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ chính -->
    <div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
        <!-- Biểu đồ xu hướng doanh thu -->
        <div class="card">
            <div class="card-header">
                <h3>Xu hướng doanh thu</h3>
            </div>
            <div class="card-body">
                <div id="revenueTrendChart" style="height: 350px;"></div>
            </div>
        </div>

        <!-- Top sản phẩm bán chạy -->
        <div class="card">
            <div class="card-header">
                <h3>Top sản phẩm bán chạy</h3>
            </div>
            <div class="card-body">
                <div id="topProductsChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ phụ -->
    <div class="secondary-charts-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
        <!-- Doanh thu theo sản phẩm -->
        <div class="card">
            <div class="card-header">
                <h3>Doanh thu theo sản phẩm</h3>
            </div>
            <div class="card-body">
                <div id="revenueByProductChart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Tăng trưởng khách hàng -->
        <div class="card">
            <div class="card-header">
                <h3>Tăng trưởng khách hàng</h3>
            </div>
            <div class="card-body">
                <div id="customerGrowthChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Thông tin nhanh -->
    <div class="quick-info-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 24px;">
        <!-- Đơn hàng chờ xử lý -->
        <div class="card">
            <div class="card-header">
                <h3>Đơn hàng chờ xử lý</h3>
                <a href="{{ route('orders.index') }}?status=pending" class="btn btn-outline btn-sm">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div id="pendingOrdersList">
                    <div class="loading-spinner">Đang tải...</div>
                </div>
            </div>
        </div>

        <!-- Sản phẩm sắp hết hàng -->
        <div class="card">
            <div class="card-header">
                <h3>Sản phẩm sắp hết hàng</h3>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div id="lowStockList">
                    @if(isset($lowStockProducts) && count($lowStockProducts) > 0)
                        @foreach($lowStockProducts as $product)
                            @php
                                // Lấy số tồn kho đúng nhất, ưu tiên thuộc tính 'stock', sau đó đến 'quantity', ngược lại trả về '0'
                                $tonKho = 0;
                                if (isset($product->stock) && is_numeric($product->stock)) {
                                    $tonKho = $product->stock;
                                } elseif (isset($product->quantity) && is_numeric($product->quantity)) {
                                    $tonKho = $product->quantity;
                                }
                            @endphp
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 16px">
                                <div>
                                    <div style="font-weight: 600;">{{ $product->name }}</div>
                                    <div style="font-size: 13px; color: #797c86;">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="color: #ef4444; font-weight: 700;">
                                        {{ $tonKho }}
                                    </span>
                                    <div style="font-size: 12px; color: #797c86;">Tồn kho</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">Không có sản phẩm sắp hết hàng.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Khách hàng mới -->
        <div class="card">
            <div class="card-header">
                <h3>Khách hàng mới</h3>
                <a href="{{ route('customers.index') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div id="recentCustomersList">
                    <div class="loading-spinner">Đang tải...</div>
                </div>
            </div>
        </div>

        <!-- Tóm tắt dòng tiền -->
        <div class="card">
            <div class="card-header">
                <h3>Dòng tiền hôm nay</h3>
                <a href="{{ route('cashbook.index') }}" class="btn btn-outline btn-sm">Xem chi tiết</a>
            </div>
            <div class="card-body">
                <div id="cashFlowSummary">
                    <div class="loading-spinner">Đang tải...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts -->
<link rel="preload" href="https://cdn.jsdelivr.net/npm/apexcharts" as="script" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/apexcharts" defer crossorigin></script>

<style>
/* KPI Cards */
.kpi-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.kpi-content {
    flex: 1;
}

.kpi-label {
    font-size: 14px;
    color: #718096;
    margin-bottom: 8px;
    font-weight: 500;
}

.kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
    line-height: 1;
}

.kpi-change {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 500;
}

.kpi-change.positive {
    color: #10b981;
}

.kpi-change.negative {
    color: #ef4444;
}

.kpi-change.neutral {
    color: #718096;
}

.change-icon {
    font-size: 12px;
}

.change-icon.positive::before {
    content: "↗";
}

.change-icon.negative::before {
    content: "↘";
}

.change-icon.neutral::before {
    content: "→";
}

/* Loading spinner */
.loading-spinner {
    text-align: center;
    color: #718096;
    padding: 20px;
    font-style: italic;
}

/* Quick info lists */
.quick-info-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.quick-info-item:last-child {
    border-bottom: none;
}

.quick-info-item-left {
    flex: 1;
}

.quick-info-item-title {
    font-weight: 500;
    color: #2d3748;
    margin-bottom: 4px;
}

.quick-info-item-subtitle {
    font-size: 13px;
    color: #718096;
}

.quick-info-item-right {
    text-align: right;
}

.quick-info-item-value {
    font-weight: 600;
    color: #2d3748;
}

.quick-info-item-date {
    font-size: 12px;
    color: #718096;
}

/* Cash flow summary */
.cash-flow-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.cash-flow-item:last-child {
    border-bottom: none;
    font-weight: 600;
    font-size: 16px;
    color: #2d3748;
}

.cash-flow-label {
    color: #4a5568;
}

.cash-flow-value {
    font-weight: 600;
}

.cash-flow-value.positive {
    color: #10b981;
}

.cash-flow-value.negative {
    color: #ef4444;
}

/* Responsive */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .secondary-charts-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Period Buttons */
.period-buttons {
    display: flex;
    gap: 8px;
    background: #f8f9fa;
    padding: 4px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.period-btn {
    padding: 8px 16px;
    border: none;
    background: transparent;
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 80px;
}

.period-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.period-btn.active {
    background: #007bff;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.period-btn.active:hover {
    background: #0056b3;
}

@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-info-grid {
        grid-template-columns: 1fr;
    }
    
    .kpi-card {
        padding: 16px;
    }
    
    .kpi-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .kpi-value {
        font-size: 24px;
    }
    
    .period-buttons {
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .period-btn {
        min-width: 60px;
        padding: 6px 12px;
        font-size: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = '30d';
    
    // Lưu trữ các instance biểu đồ để có thể destroy
    let chartInstances = {
        revenueTrend: null,
        topProducts: null,
        revenueByProduct: null,
        customerGrowth: null
    };
    
    // Khởi tạo dashboard - đơn giản hóa
    initializeDashboard();
    
    // Event listeners cho period buttons
    document.querySelectorAll('.period-btn').forEach(button => {
        button.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            console.log('Period changed to:', period);
            
            // Update current period
            currentPeriod = period;
            
            // Update active button
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Load data
            loadAllData();
        });
    });
    
    // Load KPI data
    async function loadKpiData() {
        try {
            const response = await fetch(`/dashboard/kpi-data?period=${currentPeriod}`);
            const data = await response.json();
            
            updateKpiCard('revenue', data.revenue);
            updateKpiCard('orders', data.orders);
            updateKpiCard('customers', data.customers);
            updateKpiCard('custom-orders', data.custom_orders);
            updateKpiCard('inventory', data.inventory_alerts);
            updateKpiCard('pending', data.pending_orders);
        } catch (error) {
            console.error('Error loading KPI data:', error);
        }
    }
    
    // Load chart data
    async function loadChartData() {
        console.log('Loading chart data with period:', currentPeriod);
        
        try {
            // Hiển thị loading
            showChartLoading();
            
            // Gọi API
            const response = await fetch(`/dashboard/chart-data?period=${currentPeriod}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Chart data received:', data);
            
            // Render các biểu đồ
            if (data.revenue_trend) {
                renderRevenueTrendChart(data.revenue_trend);
            }
            if (data.top_products) {
                renderTopProductsChart(data.top_products);
            }
            if (data.revenue_by_product) {
                renderRevenueByProductChart(data.revenue_by_product);
            }
            if (data.customer_growth) {
                renderCustomerGrowthChart(data.customer_growth);
            }
            
        } catch (error) {
            console.error('Error loading chart data:', error);
            hideChartLoading();
        }
    }
    
    // Hiển thị loading cho biểu đồ
    function showChartLoading() {
        const chartContainers = [
            '#revenueTrendChart',
            '#topProductsChart', 
            '#revenueByProductChart',
            '#customerGrowthChart'
        ];
        
        chartContainers.forEach(selector => {
            const container = document.querySelector(selector);
            if (container) {
                container.innerHTML = '<div class="loading-spinner">Đang tải dữ liệu...</div>';
            }
        });
    }
    
    // Ẩn loading cho biểu đồ
    function hideChartLoading() {
        // Loading sẽ được ẩn khi biểu đồ được render
    }
    
    // Load quick info
    async function loadQuickInfo() {
        try {
            const response = await fetch('/dashboard/quick-info');
            const data = await response.json();
            
            renderPendingOrders(data.pending_orders);
            renderLowStockProducts(data.low_stock_products);
            renderRecentCustomers(data.recent_customers);
            renderCashFlowSummary(data.cash_flow_summary);
        } catch (error) {
            console.error('Error loading quick info:', error);
        }
    }
    
    // Khởi tạo dashboard
    async function initializeDashboard() {
        console.log('Initializing dashboard...');
        console.log('Initial period:', currentPeriod);
        
        // Load tất cả dữ liệu
        await loadAllData();
    }
    
    // Load tất cả dữ liệu
    async function loadAllData() {
        try {
            // Load KPI và quick info trước
            await Promise.all([
                loadKpiData(),
                loadQuickInfo()
            ]);
            
            // Đợi ApexCharts sẵn sàng rồi load chart data
            await waitForApexCharts();
            await loadChartData();
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }
    
    // Đợi ApexCharts sẵn sàng
    function waitForApexCharts() {
        return new Promise((resolve) => {
            if (typeof ApexCharts !== 'undefined') {
                resolve();
            } else {
                const checkInterval = setInterval(() => {
                    if (typeof ApexCharts !== 'undefined') {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 50);
                
                // Timeout sau 5 giây
                setTimeout(() => {
                    clearInterval(checkInterval);
                    resolve();
                }, 5000);
            }
        });
    }
    
    // Load all dashboard data (deprecated - use loadAllData instead)
    async function loadDashboardData() {
        await loadAllData();
    }
    
    // Update KPI card
    function updateKpiCard(type, data) {
        const valueElement = document.getElementById(`${type}-value`);
        const changeElement = document.getElementById(`${type}-change`);
        
        if (valueElement) {
            valueElement.textContent = data.value;
        }
        
        if (changeElement) {
            changeElement.className = `kpi-change ${data.change_type}`;
            
            const changeIcon = changeElement.querySelector('.change-icon');
            const changeText = changeElement.querySelector('.change-text');
            
            if (changeIcon) {
                changeIcon.className = `change-icon ${data.change_type}`;
            }
            
            if (changeText) {
                if (data.change !== 0) {
                    changeText.textContent = `${data.change > 0 ? '+' : ''}${data.change}%`;
                } else {
                    changeText.textContent = data.change_text || '0%';
                }
            }
        }
    }
    
    // Render revenue trend chart
    function renderRevenueTrendChart(data) {
        // Debug logging
        console.log('Rendering Revenue Trend Chart with period:', currentPeriod);
        console.log('Labels count:', data.labels.length);
        console.log('First 5 labels:', data.labels.slice(0, 5));
        console.log('Last 5 labels:', data.labels.slice(-5));
        console.log('Revenue data:', data.revenue);
        console.log('Orders data:', data.orders);
        
        // Destroy biểu đồ cũ nếu tồn tại
        if (chartInstances.revenueTrend) {
            chartInstances.revenueTrend.destroy();
        }
        
        const options = {
            series: [{
                name: 'Doanh thu',
                type: 'column',
                data: data.revenue
            }, {
                name: 'Số đơn hàng',
                type: 'line',
                data: data.orders
            }],
            chart: {
                height: 350,
                type: 'line',
                toolbar: {
                    show: false
                }
            },
            stroke: {
                width: [0, 4]
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [1]
            },
            xaxis: {
                type: 'category',
                categories: data.labels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px'
                    },
                    // Chia khoảng để không bị dày đặc
                    maxHeight: 120,
                    trim: false,
                    hideOverlappingLabels: true
                },
                // Chia khoảng hiển thị labels
                tickAmount: data.labels.length > 30 ? Math.ceil(data.labels.length / 7) : undefined
            },
            yaxis: [{
                title: {
                    text: 'Doanh thu (VNĐ)',
                },
                labels: {
                    formatter: function(value) {
                        if (value >= 1000000000) {
                            return (value / 1000000000).toFixed(1) + 'B';
                        } else if (value >= 1000000) {
                            return (value / 1000000).toFixed(1) + 'M';
                        } else if (value >= 1000) {
                            return (value / 1000).toFixed(1) + 'K';
                        }
                        return value.toString();
                    }
                }
            }, {
                opposite: true,
                title: {
                    text: 'Số đơn hàng'
                },
                labels: {
                    formatter: function(value) {
                        return Math.round(value).toString();
                    }
                }
            }],
            colors: ['#3b82f6', '#10b981'],
            legend: {
                position: 'top'
            }
        };
        
        chartInstances.revenueTrend = new ApexCharts(document.querySelector("#revenueTrendChart"), options);
        chartInstances.revenueTrend.render();
    }
    
    // Render top products chart
    function renderTopProductsChart(data) {
        // Destroy biểu đồ cũ nếu tồn tại
        if (chartInstances.topProducts) {
            chartInstances.topProducts.destroy();
        }
        
        const options = {
            series: data.map(item => item.total_quantity),
            chart: {
                height: 350,
                type: 'donut',
                toolbar: {
                    show: false
                }
            },
            labels: data.map(item => item.name),
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%'
                    }
                }
            }
        };
        
        chartInstances.topProducts = new ApexCharts(document.querySelector("#topProductsChart"), options);
        chartInstances.topProducts.render();
    }
    
    // Render revenue by product chart
    function renderRevenueByProductChart(data) {
        // Destroy biểu đồ cũ nếu tồn tại
        if (chartInstances.revenueByProduct) {
            chartInstances.revenueByProduct.destroy();
        }
        
        const options = {
            series: [{
                name: 'Doanh thu',
                data: data.revenue
            }],
            chart: {
                height: 300,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    if (val >= 1000000000) {
                        return (val / 1000000000).toFixed(1) + 'B ₫';
                    } else if (val >= 1000000) {
                        return (val / 1000000).toFixed(1) + 'M ₫';
                    } else if (val >= 1000) {
                        return (val / 1000).toFixed(1) + 'K ₫';
                    }
                    return val.toString() + ' ₫';
                },
                offsetY: -20,
                style: {
                    fontSize: '13px',
                    fontWeight: '600',
                    colors: ["#1e40af"],
                    fontFamily: 'Arial, sans-serif'
                }
            },
            xaxis: {
                categories: data.labels,
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: '500',
                        fontFamily: 'Arial, sans-serif',
                        colors: ['#6b7280']
                    },
                    formatter: function (val) {
                        if (val >= 1000000000) {
                            return (val / 1000000000).toFixed(1) + 'B ₫';
                        } else if (val >= 1000000) {
                            return (val / 1000000).toFixed(1) + 'M ₫';
                        } else if (val >= 1000) {
                            return (val / 1000).toFixed(1) + 'K ₫';
                        }
                        return val.toString() + ' ₫';
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '13px',
                        fontWeight: '500',
                        fontFamily: 'Arial, sans-serif',
                        colors: ['#374151']
                    },
                    formatter: function(value) {
                        // Hiển thị tên sản phẩm với độ dài tối đa
                        if (value.length > 25) {
                            return value.substring(0, 22) + '...';
                        }
                        return value;
                    }
                }
            },
            colors: ['#3b82f6'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        if (val >= 1000000000) {
                            return (val / 1000000000).toFixed(1) + 'B ₫';
                        } else if (val >= 1000000) {
                            return (val / 1000000).toFixed(1) + 'M ₫';
                        } else if (val >= 1000) {
                            return (val / 1000).toFixed(1) + 'K ₫';
                        }
                        return val.toString() + ' ₫';
                    }
                }
            }
        };
        
        chartInstances.revenueByProduct = new ApexCharts(document.querySelector("#revenueByProductChart"), options);
        chartInstances.revenueByProduct.render();
    }
    
    // Render customer growth chart
    function renderCustomerGrowthChart(data) {
        // Destroy biểu đồ cũ nếu tồn tại
        if (chartInstances.customerGrowth) {
            chartInstances.customerGrowth.destroy();
        }
        
        const options = {
            series: [{
                name: 'Khách hàng mới',
                data: data.customers
            }],
            chart: {
                height: 300,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            xaxis: {
                type: 'category',
                categories: data.labels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px'
                    },
                    // Chia khoảng để không bị dày đặc
                    maxHeight: 120,
                    trim: false,
                    hideOverlappingLabels: true
                },
                // Chia khoảng hiển thị labels
                tickAmount: data.labels.length > 30 ? Math.ceil(data.labels.length / 7) : undefined
            },
            yaxis: {
                title: {
                    text: 'Số khách hàng mới'
                },
                labels: {
                    formatter: function(value) {
                        return Math.round(value).toString();
                    }
                }
            },
            colors: ['#8b5cf6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                }
            }
        };
        
        chartInstances.customerGrowth = new ApexCharts(document.querySelector("#customerGrowthChart"), options);
        chartInstances.customerGrowth.render();
    }
    
    // Render pending orders
    function renderPendingOrders(orders) {
        const container = document.getElementById('pendingOrdersList');
        
        if (orders.length === 0) {
            container.innerHTML = '<div class="empty-state">Không có đơn hàng chờ xử lý</div>';
            return;
        }
        
        container.innerHTML = orders.map(order => `
            <div class="quick-info-item">
                <div class="quick-info-item-left">
                    <div class="quick-info-item-title">${order.order_number}</div>
                    <div class="quick-info-item-subtitle">${order.customer_name || 'Khách lẻ'}</div>
                </div>
                <div class="quick-info-item-right">
                    <div class="quick-info-item-value">${formatCurrency(order.total_amount)}</div>
                    <div class="quick-info-item-date">${formatDate(order.created_at)}</div>
                </div>
            </div>
        `).join('');
    }
    
    // Render low stock products
    function renderLowStockProducts(products) {
        const container = document.getElementById('lowStockList');
        
        if (products.length === 0) {
            container.innerHTML = '<div class="empty-state">Tất cả sản phẩm đều đủ hàng</div>';
            return;
        }
        
        container.innerHTML = products.map(product => `
            <div class="quick-info-item">
                <div class="quick-info-item-left">
                    <div class="quick-info-item-title">${product.name}</div>
                    <div class="quick-info-item-subtitle">SKU: ${product.sku}</div>
                </div>
                <div class="quick-info-item-right">
                    <div class="quick-info-item-value" style="color: #ef4444;">${product.stock_quantity}</div>
                    <div class="quick-info-item-date">Tồn kho</div>
                </div>
            </div>
        `).join('');
    }
    
    // Render recent customers
    function renderRecentCustomers(customers) {
        const container = document.getElementById('recentCustomersList');
        
        if (customers.length === 0) {
            container.innerHTML = '<div class="empty-state">Chưa có khách hàng mới</div>';
            return;
        }
        
        container.innerHTML = customers.map(customer => `
            <div class="quick-info-item">
                <div class="quick-info-item-left">
                    <div class="quick-info-item-title">${customer.name}</div>
                    <div class="quick-info-item-subtitle">${customer.phone || 'Chưa có SĐT'}</div>
                </div>
                <div class="quick-info-item-right">
                    <div class="quick-info-item-date">${formatDate(customer.created_at)}</div>
                </div>
            </div>
        `).join('');
    }
    
    // Render cash flow summary
    function renderCashFlowSummary(summary) {
        const container = document.getElementById('cashFlowSummary');
        
        container.innerHTML = `
            <div class="cash-flow-item">
                <span class="cash-flow-label">Thu vào</span>
                <span class="cash-flow-value positive">${formatCurrency(summary.today_income)}</span>
            </div>
            <div class="cash-flow-item">
                <span class="cash-flow-label">Chi ra</span>
                <span class="cash-flow-value negative">${formatCurrency(summary.today_expense)}</span>
            </div>
            <div class="cash-flow-item">
                <span class="cash-flow-label">Dòng tiền ròng</span>
                <span class="cash-flow-value ${summary.net_flow >= 0 ? 'positive' : 'negative'}">${formatCurrency(summary.net_flow)}</span>
            </div>
        `;
    }
    
    // Utility functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }
    
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN');
    }
});
</script>
@endsection
