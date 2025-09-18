@extends('layouts.app')

@section('title', 'Dashboard Tổng quan - PerfumeShop')

@section('content')
<div class="dashboard-page">
    <!-- Header với bộ lọc thời gian -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">Dashboard Tổng quan</h1>
        <div class="period-selector">
            <select id="periodSelect" class="form-control" style="width: auto; display: inline-block;">
                <option value="today">Hôm nay</option>
                <option value="week">Tuần này</option>
                <option value="month" selected>Tháng này</option>
                <option value="quarter">Quý này</option>
                <option value="year">Năm này</option>
            </select>
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

        <!-- Đơn custom -->
        <div class="kpi-card" id="kpi-custom-orders">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-flask"></i>
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
                <div class="chart-period-selector">
                    <select id="chartPeriodSelect" class="form-control" style="width: auto;">
                        <option value="7d">7 ngày qua</option>
                        <option value="30d" selected>30 ngày qua</option>
                        <option value="90d">90 ngày qua</option>
                        <option value="1y">1 năm qua</option>
                    </select>
                </div>
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
                    <div class="loading-spinner">Đang tải...</div>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPeriod = 'month';
    let currentChartPeriod = '30d';
    
    // Khởi tạo dashboard
    loadDashboardData();
    
    // Event listeners
    document.getElementById('periodSelect').addEventListener('change', function() {
        currentPeriod = this.value;
        loadKpiData();
    });
    
    document.getElementById('chartPeriodSelect').addEventListener('change', function() {
        currentChartPeriod = this.value;
        loadChartData();
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
        try {
            const response = await fetch(`/dashboard/chart-data?period=${currentChartPeriod}`);
            const data = await response.json();
            
            renderRevenueTrendChart(data.revenue_trend);
            renderTopProductsChart(data.top_products);
            renderRevenueByProductChart(data.revenue_by_product);
            renderCustomerGrowthChart(data.customer_growth);
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
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
    
    // Load all dashboard data
    async function loadDashboardData() {
        await Promise.all([
            loadKpiData(),
            loadChartData(),
            loadQuickInfo()
        ]);
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
            labels: data.labels,
            xaxis: {
                type: 'category'
            },
            yaxis: [{
                title: {
                    text: 'Doanh thu (VNĐ)',
                },
            }, {
                opposite: true,
                title: {
                    text: 'Số đơn hàng'
                }
            }],
            colors: ['#3b82f6', '#10b981'],
            legend: {
                position: 'top'
            }
        };
        
        const chart = new ApexCharts(document.querySelector("#revenueTrendChart"), options);
        chart.render();
    }
    
    // Render top products chart
    function renderTopProductsChart(data) {
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
        
        const chart = new ApexCharts(document.querySelector("#topProductsChart"), options);
        chart.render();
    }
    
    // Render revenue by product chart
    function renderRevenueByProductChart(data) {
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
                    return new Intl.NumberFormat('vi-VN', {
                        style: 'currency',
                        currency: 'VND',
                        minimumFractionDigits: 0
                    }).format(val);
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#304758"]
                }
            },
            xaxis: {
                categories: data.labels,
                labels: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND',
                            minimumFractionDigits: 0
                        }).format(val);
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            colors: ['#3b82f6'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(val);
                    }
                }
            }
        };
        
        const chart = new ApexCharts(document.querySelector("#revenueByProductChart"), options);
        chart.render();
    }
    
    // Render customer growth chart
    function renderCustomerGrowthChart(data) {
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
            labels: data.labels,
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
        
        const chart = new ApexCharts(document.querySelector("#customerGrowthChart"), options);
        chart.render();
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
