@extends('layouts.app')

@section('content')
<h1 class="page-title">Tổng quan báo cáo</h1>

<div class="card">
    <div class="card-header">
        <h3>Phân tích tổng quan</h3>
        <div class="period-selector">
            <select id="periodSelect" class="form-control" style="width: auto; display: inline-block;">
                <option value="7d">7 ngày qua</option>
                <option value="30d" selected>30 ngày qua</option>
                <option value="90d">90 ngày qua</option>
                <option value="1y">1 năm qua</option>
            </select>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('revenue')">
                <i class="fas fa-chart-line"></i>
                Phân tích doanh thu
            </button>
            <button class="tab-button" onclick="switchTab('customer')">
                <i class="fas fa-users"></i>
                Phân tích khách hàng
            </button>
            <button class="tab-button" onclick="switchTab('order')">
                <i class="fas fa-shopping-cart"></i>
                Phân tích đơn hàng
            </button>
        </div>

        <!-- Revenue Analysis Tab -->
        <div id="revenueTab" class="tab-content active">
            <div class="analysis-summary">
                <div class="summary-card">
                    <div class="summary-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tổng doanh thu</div>
                        <div class="summary-value" id="totalRevenue">0 VNĐ</div>
                        <div class="summary-change" id="revenueGrowth">+0%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tổng đơn hàng</div>
                        <div class="summary-value" id="totalOrders">0</div>
                        <div class="summary-change" id="ordersGrowth">+0%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon avg">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Giá trị đơn TB</div>
                        <div class="summary-value" id="avgOrderValue">0 VNĐ</div>
                        <div class="summary-change">Trung bình</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon growth">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tăng trưởng tuần</div>
                        <div class="summary-value" id="weeklyGrowth">+0%</div>
                        <div class="summary-change">So với tuần trước</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon daily">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Doanh thu/ngày</div>
                        <div class="summary-value" id="revenuePerDay">0 VNĐ</div>
                        <div class="summary-change">Trung bình</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon peak">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Giờ cao điểm</div>
                        <div class="summary-value" id="peakHour">N/A</div>
                        <div class="summary-change">Doanh thu cao nhất</div>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Biểu đồ phụ - Ngày trong tuần -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-calendar-week"></i> Doanh thu theo ngày trong tuần</h4>
                        <div class="chart-subtitle">Hiệu suất bán hàng</div>
                    </div>
                    <div id="revenueByDayChart"></div>
                </div>
            
            <!-- Biểu đồ phụ - Top sản phẩm -->
            <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-trophy"></i> Top sản phẩm bán chạy</h4>
                        <div class="chart-subtitle">Theo doanh thu</div>
                    </div>
                    <div id="topProductsChart"></div>
                </div>
            <!-- Biểu đồ chính - Doanh thu theo thời gian -->
                <div class="chart-container main-chart">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-line"></i> Doanh thu tích lũy theo thời gian</h4>
                        <div class="chart-legend">
                            <span class="legend-item">
                                <span class="legend-color" style="background: #0ea5e9;"></span>
                                Doanh thu tích lũy
                            </span>
                        </div>
                    </div>
                    <div id="revenueChart"></div>
                </div>

                

            

                

                <!-- Biểu đồ phụ - Giờ trong ngày -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-clock"></i> Doanh thu theo giờ trong ngày</h4>
                        <div class="chart-subtitle">Giờ cao điểm</div>
                    </div>
                    <div id="revenueByHourChart"></div>
                </div>

                <!-- Biểu đồ phụ - Tăng trưởng tuần -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-trending-up"></i> Tốc độ tăng trưởng theo tuần</h4>
                        <div class="chart-subtitle">Xu hướng phát triển</div>
                    </div>
                    <div id="weeklyGrowthChart"></div>
                </div>
            </div>
        </div>

        <!-- Customer Analysis Tab -->
        <div id="customerTab" class="tab-content">
            <div class="analysis-summary">
                <div class="summary-card">
                    <div class="summary-icon customers">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tổng khách hàng</div>
                        <div class="summary-value" id="totalCustomers">0</div>
                        <div class="summary-change" id="customersGrowth">+0%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon new">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Khách hàng mới</div>
                        <div class="summary-value" id="newCustomers">0</div>
                        <div class="summary-change">Trong kỳ</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon repeat">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tỷ lệ quay lại</div>
                        <div class="summary-value" id="repeatRate">0%</div>
                        <div class="summary-change">Khách hàng</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon vip">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Khách VIP</div>
                        <div class="summary-value" id="vipCustomers">0</div>
                        <div class="summary-change">Top 20%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon avg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Giá trị TB/khách</div>
                        <div class="summary-value" id="avgCustomerValue">0 ₫</div>
                        <div class="summary-change">Lifetime value</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon retention">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tỷ lệ giữ chân</div>
                        <div class="summary-value" id="retentionRate">0%</div>
                        <div class="summary-change">30 ngày</div>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Biểu đồ 1 - Chi tiêu theo khu vực -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-map-marked-alt"></i> Chi tiêu theo khu vực</h4>
                        <div class="chart-subtitle">Tổng doanh thu theo tỉnh thành</div>
                    </div>
                    <div id="spendingByRegionChart"></div>
                </div>

                <!-- Biểu đồ 2 - Top phân khúc khách hàng -->
                

                <!-- Biểu đồ 3 - Phân tích RFM -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-pie"></i> Phân tích RFM</h4>
                        <div class="chart-subtitle">Recency, Frequency, Monetary</div>
                    </div>
                    <div id="rfmChart"></div>
                </div>

                <!-- Biểu đồ 4 - Xu hướng khách hàng mới -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-user-plus"></i> Xu hướng khách hàng mới</h4>
                        <div class="chart-subtitle">Theo thời gian</div>
                    </div>
                    <div id="newCustomersChart"></div>
                </div>

                <!-- Biểu đồ 5 - Phân tích giá trị khách hàng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-dollar-sign"></i> Phân tích giá trị khách hàng</h4>
                        <div class="chart-subtitle">Lifetime Value Distribution</div>
                    </div>
                    <div id="customerValueChart"></div>
                </div>
            </div>
        </div>

        <!-- Order Analysis Tab -->
        <div id="orderTab" class="tab-content">
            <div class="analysis-summary">
                <div class="summary-card">
                    <div class="summary-icon total">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tổng đơn hàng</div>
                        <div class="summary-value" id="totalOrdersCount">0</div>
                        <div class="summary-change" id="ordersGrowthRate">+0%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Đơn hoàn thành</div>
                        <div class="summary-value" id="completedOrders">0</div>
                        <div class="summary-change" id="completionRate">0%</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Tỷ lệ hủy</div>
                        <div class="summary-value" id="cancellationRate">0%</div>
                        <div class="summary-change">Đơn hàng</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon avg">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Giá trị TB/đơn</div>
                        <div class="summary-value" id="avgOrderValue">0 ₫</div>
                        <div class="summary-change">Trung bình</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon time">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Thời gian xử lý TB</div>
                        <div class="summary-value" id="avgProcessingTime">0h</div>
                        <div class="summary-change">Từ tạo đến hoàn thành</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon peak">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-label">Giờ cao điểm</div>
                        <div class="summary-value" id="peakOrderHour">N/A</div>
                        <div class="summary-change">Nhiều đơn nhất</div>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <!-- Biểu đồ 1 - Xu hướng đơn hàng theo thời gian -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-line"></i> Xu hướng đơn hàng theo thời gian</h4>
                        <div class="chart-subtitle">Theo ngày</div>
                    </div>
                    <div id="ordersByDayChart"></div>
                </div>

                <!-- Biểu đồ 2 - Phân tích trạng thái đơn hàng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-pie"></i> Phân tích trạng thái đơn hàng</h4>
                        <div class="chart-subtitle">Tỷ lệ phần trăm</div>
                    </div>
                    <div id="orderStatusChart"></div>
                </div>

                <!-- Biểu đồ 3 - Đơn hàng theo giờ trong ngày -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-clock"></i> Đơn hàng theo giờ trong ngày</h4>
                        <div class="chart-subtitle">Giờ cao điểm</div>
                    </div>
                    <div id="ordersByHourChart"></div>
                </div>

                <!-- Biểu đồ 4 - Phân tích giá trị đơn hàng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-dollar-sign"></i> Phân tích giá trị đơn hàng</h4>
                        <div class="chart-subtitle">Phân bổ theo khoảng giá</div>
                    </div>
                    <div id="orderValueChart"></div>
                </div>

                <!-- Biểu đồ 5 - Hiệu suất xử lý đơn hàng -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h4><i class="fas fa-tachometer-alt"></i> Hiệu suất xử lý đơn hàng</h4>
                        <div class="chart-subtitle">Thời gian xử lý trung bình</div>
                    </div>
                    <div id="orderProcessingChart"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="preload" href="https://cdn.jsdelivr.net/npm/apexcharts" as="script" crossorigin>
<script src="https://cdn.jsdelivr.net/npm/apexcharts" defer crossorigin></script>
<script>
let currentTab = 'revenue';
let currentPeriod = '30d';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    
    document.getElementById('periodSelect').addEventListener('change', function() {
        currentPeriod = this.value;
        loadData();
    });
});

function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tab + 'Tab').classList.add('active');
    document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
    
    currentTab = tab;
    loadData();
}

function loadData() {
    if (currentTab === 'revenue') {
        loadRevenueData();
    } else if (currentTab === 'customer') {
        loadCustomerData();
    } else if (currentTab === 'order') {
        loadOrderData();
    }
}

function loadRevenueData() {
    console.log('Loading revenue data for period:', currentPeriod);
    fetch(`/reports/revenue-analysis?period=${currentPeriod}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Revenue data loaded:', data);
            updateRevenueSummary(data.summary);
            renderRevenueChart(data.daily_revenue);
            renderTopProductsChart(data.top_products);
            renderRevenueByChannelChart(data.revenue_by_channel);
            renderRevenueByDayChart(data.revenue_by_day_of_week);
            renderRevenueByHourChart(data.revenue_by_hour);
            renderWeeklyGrowthChart(data.weekly_revenue);
        })
        .catch(error => {
            console.error('Error loading revenue data:', error);
            // Show error message to user
            document.querySelector('#revenueChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;">Lỗi tải dữ liệu: ' + error.message + '</div>';
        });
}

function loadCustomerData() {
    fetch(`/reports/customer-analysis?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            updateCustomerSummary(data.summary);
            renderSpendingByRegionChart(data.spending_by_region);
            renderCustomerSegmentsChart(data.segment_stats);
            renderRFMChart(data.rfm_analysis);
            renderNewCustomersChart(data.new_customers_daily);
            renderCustomerValueChart(data.customer_segments);
        })
        .catch(error => console.error('Error loading customer data:', error));
}

function loadOrderData() {
    fetch(`/reports/order-analysis?period=${currentPeriod}`)
        .then(response => response.json())
        .then(data => {
            updateOrderSummary(data.summary);
            renderOrdersByStatusChart(data.orders_by_status);
            renderOrdersByDayChart(data.orders_by_day_of_week);
            renderOrdersByHourChart(data.orders_by_hour);
        })
        .catch(error => console.error('Error loading order data:', error));
}

// Revenue Summary Updates
function updateRevenueSummary(summary) {
    document.getElementById('totalRevenue').textContent = formatCurrency(summary.total_revenue);
    document.getElementById('totalOrders').textContent = formatNumber(summary.total_orders);
    document.getElementById('avgOrderValue').textContent = formatCurrency(summary.avg_order_value);
    document.getElementById('revenuePerDay').textContent = formatCurrency(summary.revenue_per_day);
    document.getElementById('peakHour').textContent = summary.peak_hour + 'h';
    
    const growthElement = document.getElementById('revenueGrowth');
    const growthRate = summary.revenue_growth_rate || 0;
    growthElement.textContent = (growthRate >= 0 ? '+' : '') + growthRate + '%';
    growthElement.className = 'summary-change ' + (growthRate >= 0 ? 'positive' : 'negative');
    
    const weeklyGrowthElement = document.getElementById('weeklyGrowth');
    const weeklyGrowthRate = summary.revenue_growth_rate || 0;
    weeklyGrowthElement.textContent = (weeklyGrowthRate >= 0 ? '+' : '') + weeklyGrowthRate + '%';
    weeklyGrowthElement.className = 'summary-change ' + (weeklyGrowthRate >= 0 ? 'positive' : 'negative');
}

// Customer Summary Updates
function updateCustomerSummary(summary) {
    document.getElementById('totalCustomers').textContent = formatNumber(summary.total_customers);
    document.getElementById('newCustomers').textContent = formatNumber(summary.new_customers);
    document.getElementById('repeatRate').textContent = summary.repeat_rate + '%';
    document.getElementById('vipCustomers').textContent = formatNumber(summary.vip_customers);
    document.getElementById('avgCustomerValue').textContent = formatCurrency(summary.avg_customer_value);
    document.getElementById('retentionRate').textContent = summary.retention_rate + '%';
    
    const growthElement = document.getElementById('customersGrowth');
    const growthRate = summary.growth_rate || 0;
    growthElement.textContent = (growthRate >= 0 ? '+' : '') + growthRate + '%';
    growthElement.className = 'summary-change ' + (growthRate >= 0 ? 'positive' : 'negative');
}

// Order Summary Updates
function updateOrderSummary(summary) {
    document.getElementById('totalOrdersCount').textContent = formatNumber(summary.total_orders);
    document.getElementById('completedOrders').textContent = formatNumber(summary.completed_orders);
    document.getElementById('cancellationRate').textContent = summary.cancellation_rate + '%';
    document.getElementById('completionRate').textContent = summary.completion_rate + '%';
    document.getElementById('avgOrderValue').textContent = formatCurrency(summary.avg_order_value);
    document.getElementById('avgProcessingTime').textContent = summary.avg_processing_time + 'h';
    document.getElementById('peakOrderHour').textContent = summary.peak_order_hour + 'h';
    
    const growthElement = document.getElementById('ordersGrowthRate');
    const growthRate = summary.growth_rate || 0;
    growthElement.textContent = (growthRate >= 0 ? '+' : '') + growthRate + '%';
    growthElement.className = 'summary-change ' + (growthRate >= 0 ? 'positive' : 'negative');
}

// Chart Rendering Functions
function renderRevenueChart(data) {
    console.log('Rendering revenue chart with data:', data);
    if (!data || data.length === 0) {
        console.warn('No revenue data available');
        document.querySelector('#revenueChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #64748b;">Không có dữ liệu doanh thu</div>';
        return;
    }
    
    const categories = data.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
    });
    const series = data.map(item => item.revenue);

    console.log('Creating revenue chart with categories:', categories, 'series:', series);
    const chart = new ApexCharts(document.querySelector('#revenueChart'), {
        chart: { 
            type: 'area', 
            height: 350, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{ 
            name: 'Doanh thu tích lũy', 
            data: series 
        }],
        xaxis: { 
            categories: categories,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return formatCurrency(value);
                }
            }
        },
        colors: ['#0ea5e9'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#0ea5e9'],
                inverseColors: false,
                opacityFrom: 0.8,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        stroke: { 
            curve: 'smooth', 
            width: 3,
            lineCap: 'round'
        },
        markers: { 
            size: 5,
            strokeWidth: 3,
            strokeColors: '#ffffff',
            fillColors: '#0ea5e9',
            hover: {
                size: 7
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: v => formatCurrency(v) 
            },
            marker: {
                show: true
            }
        },
        dataLabels: { enabled: false }
    });
    console.log('Rendering revenue chart...');
    chart.render();
    console.log('Revenue chart rendered successfully');
}

function renderTopProductsChart(data) {
    console.log('Rendering top products chart with data:', data);
    if (!data || data.length === 0) {
        console.warn('No top products data available');
        document.querySelector('#topProductsChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #64748b;">Không có dữ liệu sản phẩm</div>';
        return;
    }
    
    const categories = data.map(item => item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name);
    const series = data.map(item => item.total_quantity);
    const revenue = data.map(item => item.total_revenue);

    const chart = new ApexCharts(document.querySelector('#topProductsChart'), {
        chart: { 
            type: 'bar', 
            height: 300, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{ 
            name: 'Số lượng bán', 
            data: series 
        }],
        xaxis: { 
            categories: categories,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '11px'
                },
                rotate: -45
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        colors: ['#10b981'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.3,
                gradientToColors: ['#10b981'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.8,
                stops: [0, 100]
            }
        },
        plotOptions: { 
            bar: { 
                horizontal: false,
                borderRadius: 4,
                columnWidth: '60%'
            } 
        },
        dataLabels: { 
            enabled: true,
            style: {
                colors: ['#ffffff'],
                fontSize: '11px',
                fontWeight: 'bold'
            },
            formatter: function(val) {
                return val > 0 ? val : '';
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: function(val, { seriesIndex, dataPointIndex }) {
                    const product = data[dataPointIndex];
                    return `${formatNumber(val)} sản phẩm<br/>Doanh thu: ${formatCurrency(product.total_revenue)}`;
                }
            }
        }
    });
    chart.render();
}

function renderRevenueByChannelChart(data) {
    const labels = data.map(item => item.sales_channel || 'Không xác định');
    const series = data.map(item => item.revenue);
    const totalRevenue = series.reduce((sum, val) => sum + val, 0);

    const chart = new ApexCharts(document.querySelector('#revenueByChannelChart'), {
        chart: { 
            type: 'donut', 
            height: 300, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: series,
        labels: labels,
        colors: ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#f97316'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'radial',
                shadeIntensity: 0.4,
                gradientToColors: ['#a855f7', '#0891b2', '#059669', '#d97706', '#dc2626', '#ea580c'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.8,
                stops: [0, 100]
            }
        },
        dataLabels: { 
            enabled: true, 
            formatter: (val) => val.toFixed(1) + '%',
            style: {
                colors: ['#ffffff'],
                fontSize: '12px',
                fontWeight: 'bold'
            }
        },
        legend: { 
            position: 'bottom',
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif',
            labels: {
                colors: '#64748b'
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Tổng doanh thu',
                            formatter: function() {
                                return formatCurrency(totalRevenue);
                            },
                            fontSize: '14px',
                            fontWeight: 'bold',
                            color: '#2d3748'
                        }
                    }
                }
            }
        },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: function(val, { seriesIndex, dataPointIndex }) {
                    const percentage = ((val / totalRevenue) * 100).toFixed(1);
                    return `${formatCurrency(val)} (${percentage}%)`;
                }
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['#ffffff']
        }
    });
    chart.render();
}

function renderRevenueByDayChart(data) {
    const categories = data.map(item => item.day);
    const series = data.map(item => item.revenue);
    const orders = data.map(item => item.orders_count);

    const chart = new ApexCharts(document.querySelector('#revenueByDayChart'), {
        chart: { 
            type: 'bar', 
            height: 300, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{ 
            name: 'Doanh thu', 
            data: series 
        }],
        xaxis: { 
            categories: categories,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return formatCurrency(value);
                }
            }
        },
        colors: ['#f59e0b'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.3,
                gradientToColors: ['#f59e0b'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.8,
                stops: [0, 100]
            }
        },
        plotOptions: { 
            bar: { 
                borderRadius: 6,
                columnWidth: '70%'
            } 
        },
        dataLabels: { 
            enabled: true,
            style: {
                colors: ['#ffffff'],
                fontSize: '11px',
                fontWeight: 'bold'
            },
            formatter: function(val) {
                return val > 0 ? formatCurrency(val) : '';
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: function(val, { seriesIndex, dataPointIndex }) {
                    const dayData = data[dataPointIndex];
                    return `${formatCurrency(val)}<br/>${dayData.orders_count} đơn hàng`;
                }
            }
        }
    });
    chart.render();
}

function renderRevenueByHourChart(data) {
    const categories = Array.from({length: 24}, (_, i) => `${i}h`);
    const series = Array.from({length: 24}, (_, i) => {
        const hourData = data.find(item => item.hour == i);
        return hourData ? hourData.revenue : 0;
    });

    const chart = new ApexCharts(document.querySelector('#revenueByHourChart'), {
        chart: { 
            type: 'area', 
            height: 300, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{ 
            name: 'Doanh thu', 
            data: series 
        }],
        xaxis: { 
            categories: categories, 
            tickAmount: 12,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '11px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return formatCurrency(value);
                }
            }
        },
        colors: ['#ef4444'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#ef4444'],
                inverseColors: false,
                opacityFrom: 0.8,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        stroke: { 
            curve: 'smooth', 
            width: 3,
            lineCap: 'round'
        },
        markers: { 
            size: 4,
            strokeWidth: 2,
            strokeColors: '#ffffff',
            fillColors: '#ef4444',
            hover: {
                size: 6
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        dataLabels: { enabled: false },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: function(val, { seriesIndex, dataPointIndex }) {
                    const hourData = data.find(item => item.hour == dataPointIndex);
                    const orders = hourData ? hourData.orders_count : 0;
                    return `${formatCurrency(val)}<br/>${orders} đơn hàng`;
                }
            }
        }
    });
    chart.render();
}

function renderWeeklyGrowthChart(data) {
    const categories = data.map(item => `Tuần ${item.week.split('-')[1]}`);
    const series = data.map(item => item.revenue);

    const chart = new ApexCharts(document.querySelector('#weeklyGrowthChart'), {
        chart: { 
            type: 'area', 
            height: 300, 
            toolbar: { show: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [{ 
            name: 'Doanh thu', 
            data: series 
        }],
        xaxis: { 
            categories: categories,
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return formatCurrency(value);
                }
            }
        },
        colors: ['#10b981'],
        fill: { 
            type: 'gradient', 
            gradient: { 
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#10b981'],
                inverseColors: false,
                opacityFrom: 0.8,
                opacityTo: 0.1,
                stops: [0, 100]
            } 
        },
        stroke: { 
            curve: 'smooth', 
            width: 3,
            lineCap: 'round'
        },
        markers: { 
            size: 5,
            strokeWidth: 3,
            strokeColors: '#ffffff',
            fillColors: '#10b981',
            hover: {
                size: 7
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 5,
            xaxis: {
                lines: {
                    show: true
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        dataLabels: { enabled: false },
        tooltip: { 
            theme: 'light',
            style: {
                fontSize: '14px'
            },
            y: { 
                formatter: v => formatCurrency(v) 
            },
            marker: {
                show: true
            }
        }
    });
    chart.render();
}

function renderNewCustomersChart(data) {
    const categories = data.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('vi-VN', { month: 'short', day: 'numeric' });
    });
    const series = data.map(item => item.new_customers);

    const chart = new ApexCharts(document.querySelector('#newCustomersChart'), {
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        series: [{ name: 'Khách hàng mới', data: series }],
        xaxis: { categories: categories },
        colors: ['#06b6d4'],
        dataLabels: { enabled: false },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } },
        stroke: { curve: 'smooth', width: 3 },
        tooltip: { y: { formatter: v => formatNumber(v) + ' khách hàng' } }
    });
    chart.render();
}

function renderRFMChart(data) {
    if (!data || Object.keys(data).length === 0) {
        document.querySelector('#rfmChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">Không có dữ liệu</div>';
        return;
    }

    // Chỉ lấy những key có dữ liệu > 0
    const filteredData = Object.entries(data).filter(([key, value]) => value > 0);
    
    if (filteredData.length === 0) {
        document.querySelector('#rfmChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">Không có dữ liệu</div>';
        return;
    }

    const labelMap = {
        'champions': 'Khách hàng VIP',
        'loyal_customers': 'Khách hàng trung thành',
        'potential_loyalists': 'Khách hàng tiềm năng',
        'new_customers': 'Khách hàng mới',
        'promising': 'Khách hàng hứa hẹn',
        'need_attention': 'Cần chú ý',
        'about_to_sleep': 'Sắp ngủ quên',
        'at_risk': 'Có nguy cơ',
        'cannot_lose_them': 'Không thể mất',
        'hibernating': 'Đang ngủ đông',
        'lost': 'Đã mất'
    };

    const labels = filteredData.map(([key, value]) => labelMap[key] || key);
    const series = filteredData.map(([key, value]) => value);

    const chart = new ApexCharts(document.querySelector('#rfmChart'), {
        chart: { 
            type: 'donut', 
            height: 300, 
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        series: series,
        labels: labels,
        colors: ['#10b981', '#06b6d4', '#8b5cf6', '#f59e0b', '#ef4444', '#84cc16', '#f97316', '#ec4899', '#6366f1', '#14b8a6'],
        dataLabels: { 
            enabled: true, 
            formatter: (val) => val.toFixed(1) + '%' 
        },
        legend: { 
            position: 'bottom',
            fontSize: '12px',
            itemMargin: { horizontal: 10, vertical: 5 }
        },
        tooltip: { 
            y: { 
                formatter: v => formatNumber(v) + ' khách hàng' 
            } 
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Tổng',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        }
    });
    chart.render();
}

function renderCustomersByProvinceChart(data) {
    const categories = data.map(item => item.city);
    const series = data.map(item => item.customer_count);

    const chart = new ApexCharts(document.querySelector('#customersByProvinceChart'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Số khách hàng', data: series }],
        xaxis: { categories: categories },
        colors: ['#8b5cf6'],
        dataLabels: { enabled: false },
        plotOptions: { bar: { horizontal: true } },
        tooltip: { y: { formatter: v => formatNumber(v) + ' khách hàng' } }
    });
    chart.render();
}

// Chi tiêu theo khu vực
function renderSpendingByRegionChart(data) {
    if (!data || data.length === 0) {
        document.querySelector('#spendingByRegionChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">Không có dữ liệu</div>';
        return;
    }

    const categories = data.map(item => item.city);
    const series = data.map(item => item.total_spending);

    const chart = new ApexCharts(document.querySelector('#spendingByRegionChart'), {
        chart: { 
            type: 'bar', 
            height: 300, 
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 800 }
        },
        series: [{ 
            name: 'Tổng chi tiêu', 
            data: series 
        }],
        xaxis: { 
            categories: categories,
            labels: { rotate: -45, style: { fontSize: '12px' } }
        },
        colors: ['#10b981'],
        dataLabels: { 
            enabled: true,
            formatter: function (val) {
                return formatCurrency(val);
            }
        },
        plotOptions: { 
            bar: { 
                horizontal: false,
                borderRadius: 4,
                columnWidth: '60%'
            } 
        },
        tooltip: { 
            y: { 
                formatter: function(val) {
                    return formatCurrency(val) + ' VNĐ';
                }
            }
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4
        }
    });
    chart.render();
}

// Top phân khúc khách hàng
function renderCustomerSegmentsChart(data) {
    if (!data || !data.value_segments) {
        document.querySelector('#customerSegmentsChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">Không có dữ liệu</div>';
        return;
    }

    const valueData = Object.entries(data.value_segments).map(([key, value]) => ({
        x: key,
        y: value
    }));

    const chart = new ApexCharts(document.querySelector('#customerSegmentsChart'), {
        chart: { 
            type: 'donut', 
            height: 300, 
            toolbar: { show: false }
        },
        series: valueData.map(item => item.y),
        labels: valueData.map(item => item.x),
        colors: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
        dataLabels: {
            enabled: true,
            formatter: function (val, opts) {
                return val.toFixed(1) + '%';
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '12px'
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + ' khách hàng';
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Tổng',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        }
    });
    chart.render();
}

// Phân tích giá trị khách hàng
function renderCustomerValueChart(data) {
    if (!data || data.length === 0) {
        document.querySelector('#customerValueChart').innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;">Không có dữ liệu</div>';
        return;
    }

    // Tạo histogram cho phân bổ giá trị
    const valueRanges = [
        { min: 0, max: 500000, label: '0-500K' },
        { min: 500000, max: 1000000, label: '500K-1M' },
        { min: 1000000, max: 2000000, label: '1M-2M' },
        { min: 2000000, max: 5000000, label: '2M-5M' },
        { min: 5000000, max: Infinity, label: '5M+' }
    ];

    const histogramData = valueRanges.map(range => {
        const count = data.filter(customer => 
            customer.total_value >= range.min && customer.total_value < range.max
        ).length;
        return count;
    });

    const chart = new ApexCharts(document.querySelector('#customerValueChart'), {
        chart: { 
            type: 'bar', 
            height: 300, 
            toolbar: { show: false }
        },
        series: [{ 
            name: 'Số khách hàng', 
            data: histogramData 
        }],
        xaxis: { 
            categories: valueRanges.map(range => range.label)
        },
        colors: ['#8b5cf6'],
        dataLabels: { enabled: false },
        plotOptions: { 
            bar: { 
                horizontal: false,
                borderRadius: 4,
                columnWidth: '70%'
            } 
        },
        tooltip: { 
            y: { 
                formatter: function(val) {
                    return val + ' khách hàng';
                }
            }
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4
        }
    });
    chart.render();
}

function renderOrdersByStatusChart(data) {
    const labels = data.map(item => {
        const statusLabels = {
            'pending': 'Chờ xử lý',
            'processing': 'Đang xử lý',
            'shipped': 'Đã gửi',
            'delivered': 'Đã giao',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy',
            'returned': 'Đã trả'
        };
        return statusLabels[item.status] || item.status;
    });
    const series = data.map(item => item.count);

    const chart = new ApexCharts(document.querySelector('#ordersByStatusChart'), {
        chart: { type: 'pie', height: 300, toolbar: { show: false } },
        series: series,
        labels: labels,
        colors: ['#10b981', '#06b6d4', '#8b5cf6', '#f59e0b', '#ef4444', '#84cc16', '#f97316'],
        dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
        legend: { position: 'bottom' },
        tooltip: { y: { formatter: v => formatNumber(v) + ' đơn hàng' } }
    });
    chart.render();
}

function renderOrdersByDayChart(data) {
    const categories = data.map(item => item.day);
    const series = data.map(item => item.count);

    const chart = new ApexCharts(document.querySelector('#ordersByDayChart'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Số đơn hàng', data: series }],
        xaxis: { categories: categories },
        colors: ['#f59e0b'],
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: v => formatNumber(v) + ' đơn hàng' } }
    });
    chart.render();
}

function renderOrdersByHourChart(data) {
    const categories = Array.from({length: 24}, (_, i) => `${i}h`);
    const series = Array.from({length: 24}, (_, i) => {
        const hourData = data.find(item => item.hour == i);
        return hourData ? hourData.count : 0;
    });

    const chart = new ApexCharts(document.querySelector('#ordersByHourChart'), {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [{ name: 'Số đơn hàng', data: series }],
        xaxis: { categories: categories, tickAmount: 12 },
        colors: ['#ef4444'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 3 },
        tooltip: { y: { formatter: v => formatNumber(v) + ' đơn hàng' } }
    });
    chart.render();
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatNumber(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}
</script>

<style>
/* Report Overview Styles */
.tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 30px;
}

.tab-button {
    background: none;
    border: none;
    padding: 15px 25px;
    font-size: 14px;
    font-weight: 600;
    color: #718096;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-button:hover {
    color: #4299e1;
    background-color: #f7fafc;
}

.tab-button.active {
    color: #4299e1;
    border-bottom-color: #4299e1;
    background-color: #f7fafc;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.period-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.period-selector select {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 14px;
    background: white;
}

.analysis-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.summary-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.summary-icon.revenue { background: linear-gradient(135deg, #10b981, #059669); }
.summary-icon.orders { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.summary-icon.avg { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.summary-icon.growth { background: linear-gradient(135deg, #f59e0b, #d97706); }
.summary-icon.daily { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.summary-icon.peak { background: linear-gradient(135deg, #ef4444, #dc2626); }
.summary-icon.customers { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.summary-icon.new { background: linear-gradient(135deg, #f59e0b, #d97706); }
.summary-icon.repeat { background: linear-gradient(135deg, #ef4444, #dc2626); }
.summary-icon.total { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.summary-icon.completed { background: linear-gradient(135deg, #10b981, #059669); }
.summary-icon.cancelled { background: linear-gradient(135deg, #ef4444, #dc2626); }
.summary-icon.vip { background: linear-gradient(135deg, #f59e0b, #d97706); }
.summary-icon.retention { background: linear-gradient(135deg, #ec4899, #db2777); }
.summary-icon.time { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

.summary-content {
    flex: 1;
}

.summary-label {
    font-size: 12px;
    color: #718096;
    font-weight: 500;
    margin-bottom: 4px;
}

.summary-value {
    font-size: 20px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
}

.summary-change {
    font-size: 12px;
    font-weight: 600;
}

.summary-change.positive {
    color: #10b981;
}

.summary-change.negative {
    color: #ef4444;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto auto;
    gap: 20px;
}

.charts-grid .chart-container:nth-child(1) {
    grid-column: 1;
    grid-row: 1;
}

.charts-grid .chart-container:nth-child(2) {
    grid-column: 2;
    grid-row: 1;
}

.charts-grid .chart-container:nth-child(3) {
    grid-column: 1 / -1; /* Biểu đồ thứ 3 chiếm cả 2 cột */
    grid-row: 2;
}

.charts-grid .chart-container:nth-child(4) {
    grid-column: 1;
    grid-row: 3;
}

.charts-grid .chart-container:nth-child(5) {
    grid-column: 2;
    grid-row: 3;
}

.chart-container {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.chart-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.chart-container.main-chart {
    grid-column: span 2;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 2px solid #e2e8f0;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.chart-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-header h4 i {
    color: #64748b;
    font-size: 14px;
}

.chart-subtitle {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-top: 4px;
}

.chart-legend {
    display: flex;
    gap: 15px;
    align-items: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }
    
    .charts-grid .chart-container:nth-child(1),
    .charts-grid .chart-container:nth-child(2),
    .charts-grid .chart-container:nth-child(3),
    .charts-grid .chart-container:nth-child(4),
    .charts-grid .chart-container:nth-child(5) {
        grid-column: 1;
        grid-row: auto;
    }
    
    .chart-container.main-chart {
        grid-column: span 1;
    }
    
    .analysis-summary {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        flex-direction: column;
    }
    
    .tab-button {
        justify-content: center;
    }
    
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .chart-legend {
        align-self: flex-end;
    }
}
</style>
@endsection

