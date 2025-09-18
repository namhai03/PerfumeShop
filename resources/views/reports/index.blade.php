@extends('layouts.app')

@section('content')
<h1 class="page-title">Báo cáo</h1>

<div class="card">
    <div class="card-header">
        <h3>Danh sách báo cáo</h3>
        <a href="{{ route('reports.overview') }}" class="btn btn-primary">
            <i class="fas fa-chart-bar"></i> Tổng quan báo cáo
        </a>
    </div>
    
    <div class="card-body">
        <div class="reports-grid">
            <!-- Revenue Reports -->
            <div class="report-category">
                <h4><i class="fas fa-dollar-sign"></i> Báo cáo doanh thu</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon revenue">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo doanh thu tổng hợp</h5>
                            <p>Phân tích doanh thu theo thời gian, sản phẩm và kênh bán hàng</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#revenue" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon sales">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo bán hàng</h5>
                            <p>Thống kê đơn hàng, sản phẩm bán chạy và hiệu suất bán hàng</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#order" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Reports -->
            <div class="report-category">
                <h4><i class="fas fa-users"></i> Báo cáo khách hàng</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon customer">
                            <i class="fas fa-user-chart"></i>
                        </div>
                        <div class="report-content">
                            <h5>Phân tích khách hàng</h5>
                            <p>RFM analysis, khách hàng mới, tỷ lệ quay lại và phân khúc khách hàng</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#customer" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon loyalty">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo khách hàng trung thành</h5>
                            <p>Phân tích khách hàng VIP, tỷ lệ retention và lifetime value</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#customer" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Reports -->
            <div class="report-category">
                <h4><i class="fas fa-boxes"></i> Báo cáo tồn kho</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon inventory">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo tồn kho</h5>
                            <p>Thống kê tồn kho, sản phẩm sắp hết hàng và turnover rate</p>
                            <div class="report-actions">
                                <a href="{{ route('inventory.index') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon movement">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo xuất nhập kho</h5>
                            <p>Lịch sử xuất nhập kho, biến động tồn kho theo thời gian</p>
                            <div class="report-actions">
                                <a href="{{ route('inventory.history') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Reports -->
            <div class="report-category">
                <h4><i class="fas fa-shipping-fast"></i> Báo cáo vận chuyển</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon shipping">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="report-content">
                            <h5>Tổng quan vận chuyển</h5>
                            <p>Thống kê vận đơn, tỷ lệ giao thành công và hiệu suất vận chuyển</p>
                            <div class="report-actions">
                                <a href="{{ route('shipping.overview') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon delivery">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo giao hàng</h5>
                            <p>Phân tích theo khu vực, thời gian giao hàng và tỷ lệ thành công</p>
                            <div class="report-actions">
                                <a href="{{ route('shipping.overview') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Reports -->
            <div class="report-category">
                <h4><i class="fas fa-calculator"></i> Báo cáo tài chính</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon cashbook">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo sổ quỹ</h5>
                            <p>Thu chi, dòng tiền và báo cáo tài chính tổng hợp</p>
                            <div class="report-actions">
                                <a href="{{ route('cashbook.index') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon profit">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo lợi nhuận</h5>
                            <p>Phân tích lợi nhuận theo sản phẩm, danh mục và thời gian</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#revenue" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marketing Reports -->
            <div class="report-category">
                <h4><i class="fas fa-bullhorn"></i> Báo cáo marketing</h4>
                <div class="report-list">
                    <div class="report-item">
                        <div class="report-icon promotion">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo khuyến mại</h5>
                            <p>Hiệu quả chương trình khuyến mại, tỷ lệ sử dụng và ROI</p>
                            <div class="report-actions">
                                <a href="{{ route('promotions.index') }}" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-item">
                        <div class="report-icon campaign">
                            <i class="fas fa-megaphone"></i>
                        </div>
                        <div class="report-content">
                            <h5>Báo cáo chiến dịch</h5>
                            <p>Phân tích hiệu quả các chiến dịch marketing và conversion rate</p>
                            <div class="report-actions">
                                <a href="{{ route('reports.overview') }}#customer" class="btn btn-outline btn-sm">Xem báo cáo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reports Index Styles */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.report-category {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
}

.report-category h4 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f7fafc;
}

.report-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.report-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.report-item:hover {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    transform: translateY(-1px);
}

.report-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.report-icon.revenue { background: linear-gradient(135deg, #10b981, #059669); }
.report-icon.sales { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.report-icon.customer { background: linear-gradient(135deg, #06b6d4, #0891b2); }
.report-icon.loyalty { background: linear-gradient(135deg, #ef4444, #dc2626); }
.report-icon.inventory { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.report-icon.movement { background: linear-gradient(135deg, #f59e0b, #d97706); }
.report-icon.shipping { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.report-icon.delivery { background: linear-gradient(135deg, #14b8a6, #0d9488); }
.report-icon.cashbook { background: linear-gradient(135deg, #84cc16, #65a30d); }
.report-icon.profit { background: linear-gradient(135deg, #ec4899, #db2777); }
.report-icon.promotion { background: linear-gradient(135deg, #f97316, #ea580c); }
.report-icon.campaign { background: linear-gradient(135deg, #64748b, #475569); }

.report-content {
    flex: 1;
}

.report-content h5 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
}

.report-content p {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #718096;
    line-height: 1.5;
}

.report-actions {
    display: flex;
    gap: 8px;
}

.report-actions .btn {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid;
    transition: all 0.2s ease;
}

.report-actions .btn-outline {
    background-color: white;
    color: #4299e1;
    border-color: #4299e1;
}

.report-actions .btn-outline:hover {
    background: #4299e1;
    color: white;
}

@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
    
    .report-item {
        flex-direction: column;
        text-align: center;
    }
    
    .report-icon {
        align-self: center;
    }
}
</style>
@endsection
