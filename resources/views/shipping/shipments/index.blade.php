@extends('layouts.app')

@section('title', 'Danh sách vận đơn - PerfumeShop')

@section('content')
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">
                <i class="fas fa-truck-fast"></i>
                Danh sách vận đơn
            </h1>
            <p class="page-subtitle">Quản lý và theo dõi tất cả vận đơn vận chuyển</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-outline btn-filter-toggle" onclick="openFilterModal()">
                <i class="fas fa-filter"></i>
                Bộ lọc
            </button>
            <a href="#" class="btn btn-primary btn-create">
                <i class="fas fa-plus"></i>
                Tạo vận đơn mới
            </a>
        </div>
    </div>

    <!-- Filter Modal -->
    <div id="filterModal" class="filter-modal">
        <div class="filter-modal-content">
            <div class="filter-modal-header">
                <h3 class="filter-modal-title">
                    <i class="fas fa-filter"></i>
                    Bộ lọc tìm kiếm
                </h3>
                <button class="filter-modal-close" onclick="closeFilterModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('shipments.index') }}" class="filter-form">
                <div class="filter-modal-body">
                    <div class="filter-group">
                        <label class="filter-label">Tìm kiếm</label>
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   class="form-control search-input" 
                                   placeholder="Mã đơn, mã vận đơn, tên/SĐT khách hàng...">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Trạng thái</label>
                        <div class="status-dropdown">
                            <button type="button" class="status-dropdown-toggle" onclick="toggleStatusDropdown()">
                                <span class="status-dropdown-text">
                                    @if(request('status') && count(request('status')) > 0)
                                        {{ count(request('status')) }} trạng thái đã chọn
                                    @else
                                        Chọn trạng thái
                                    @endif
                                </span>
                                <i class="fas fa-chevron-down status-dropdown-icon"></i>
                            </button>
                            <div class="status-dropdown-menu" id="statusDropdown">
                                <div class="status-dropdown-header">
                                    <span>Chọn trạng thái</span>
                                    <button type="button" class="clear-all-status" onclick="clearAllStatus()">
                                        Xóa tất cả
                                    </button>
                                </div>
                                <div class="status-options">
                                    @php($statuses=[
                                        'pending_pickup'=>'Chờ lấy hàng',
                                        'picked_up'=>'Đã lấy hàng',
                                        'in_transit'=>'Đang giao hàng',
                                        'retry'=>'Giao lại',
                                        'returning'=>'Đang hoàn hàng',
                                        'returned'=>'Đã hoàn hàng',
                                        'delivered'=>'Đã giao thành công',
                                        'failed'=>'Giao hàng thất bại'
                                    ])
                                    @foreach($statuses as $value=>$label)
                                        <label class="status-option">
                                            <input type="checkbox" name="status[]" value="{{ $value }}" 
                                                   {{ collect(request('status',[]))->contains($value) ? 'checked' : '' }}
                                                   onchange="updateStatusText()">
                                            <span class="status-checkbox"></span>
                                            <span class="status-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Chi nhánh</label>
                        <select name="branch" class="form-control">
                            <option value="">Tất cả chi nhánh</option>
                            <option value="Cửa hàng chính" {{ request('branch')==='Cửa hàng chính' ? 'selected' : '' }}>
                                Cửa hàng chính
                            </option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Khu vực</label>
                        <select name="region" class="form-control">
                            <option value="">Tất cả khu vực</option>
                            <option value="HCM" {{ request('region')==='HCM' ? 'selected' : '' }}>TP. Hồ Chí Minh</option>
                            <option value="HN" {{ request('region')==='HN' ? 'selected' : '' }}>Hà Nội</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Sắp xếp</label>
                        <div class="sort-controls">
                            <select name="sort_by" class="form-control sort-select">
                                @php($sorts=[
                                    'created_at'=>'Ngày tạo',
                                    'status'=>'Trạng thái',
                                    'carrier'=>'Hãng vận chuyển',
                                    'shipping_fee'=>'Phí vận chuyển',
                                    'cod_amount'=>'Số tiền COD'
                                ])
                                @foreach($sorts as $value=>$label)
                                    <option value="{{ $value }}" {{ ($sortBy??'created_at')===$value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sort_order" class="form-control order-select">
                                <option value="desc" {{ ($sortOrder??'desc')==='desc' ? 'selected' : '' }}>↓</option>
                                <option value="asc" {{ ($sortOrder??'desc')==='asc' ? 'selected' : '' }}>↑</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="filter-modal-footer">
                    <button class="btn btn-primary btn-filter" type="submit">
                        <i class="fas fa-search"></i>
                        Áp dụng bộ lọc
                    </button>
                    <a href="{{ route('shipments.index') }}" class="btn btn-outline btn-reset">
                        <i class="fas fa-undo"></i>
                        Đặt lại
                    </a>
                    <button type="button" class="btn btn-outline btn-cancel" onclick="closeFilterModal()">
                        <i class="fas fa-times"></i>
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request('search') || request('status') || request('branch') || request('region') || request('sort_by'))
        <div class="active-filters">
            <div class="active-filters-header">
                <i class="fas fa-filter"></i>
                <span>Bộ lọc đang áp dụng:</span>
            </div>
            <div class="active-filters-tags">
                @if(request('search'))
                    <span class="filter-tag">
                        Tìm kiếm: "{{ request('search') }}"
                        <a href="{{ route('shipments.index', array_merge(request()->except('search'), ['page' => 1])) }}" class="remove-filter">×</a>
                    </span>
                @endif
                @if(request('status'))
                    @foreach(request('status') as $status)
                        <span class="filter-tag">
                            Trạng thái: {{ $statuses[$status] ?? $status }}
                            <a href="{{ route('shipments.index', array_merge(request()->except(['status', 'page']), ['status' => array_filter(request('status', []), fn($s) => $s !== $status)])) }}" class="remove-filter">×</a>
                        </span>
                    @endforeach
                @endif
                @if(request('branch'))
                    <span class="filter-tag">
                        Chi nhánh: {{ request('branch') }}
                        <a href="{{ route('shipments.index', request()->except(['branch', 'page'])) }}" class="remove-filter">×</a>
                    </span>
                @endif
                @if(request('region'))
                    <span class="filter-tag">
                        Khu vực: {{ request('region') === 'HCM' ? 'TP. Hồ Chí Minh' : 'Hà Nội' }}
                        <a href="{{ route('shipments.index', request()->except(['region', 'page'])) }}" class="remove-filter">×</a>
                    </span>
                @endif
                @if(request('sort_by'))
                    <span class="filter-tag">
                        Sắp xếp: {{ $sorts[request('sort_by')] ?? request('sort_by') }}
                        <a href="{{ route('shipments.index', request()->except(['sort_by', 'sort_order', 'page'])) }}" class="remove-filter">×</a>
                    </span>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Danh sách vận đơn
            </h3>
            <div class="card-stats">
                <span class="stat-item">
                    <i class="fas fa-truck"></i>
                    Tổng: {{ $shipments->total() }}
                </span>
            </div>
        </div>
        
        <div class="card-body">
            @if($shipments->count() === 0)
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="empty-state-title">Chưa có vận đơn nào</h3>
                    <p class="empty-state-text">Bắt đầu tạo vận đơn đầu tiên để quản lý việc vận chuyển</p>
                    <div class="empty-state-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tạo vận đơn mới
                        </a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table shipments-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mã đơn hàng</th>
                                <th>Mã vận đơn</th>
                                <th>Hãng vận chuyển</th>
                                <th>Trạng thái</th>
                                <th>Số tiền COD</th>
                                <th>Phí vận chuyển</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipments as $shipment)
                                <tr class="shipment-row">
                                    <td class="shipment-id">{{ $shipment->id }}</td>
                                    <td class="order-code">
                                        <span class="code-badge">{{ $shipment->order_code }}</span>
                                    </td>
                                    <td class="tracking-code">
                                        <span class="tracking-badge">{{ $shipment->tracking_code }}</span>
                                    </td>
                                    <td class="carrier">
                                        <span class="carrier-name">{{ $shipment->carrier }}</span>
                                    </td>
                                    <td class="status">
                                        @php($statusClass = [
                                            'pending_pickup' => 'status-pending',
                                            'picked_up' => 'status-picked',
                                            'in_transit' => 'status-transit',
                                            'retry' => 'status-retry',
                                            'returning' => 'status-returning',
                                            'returned' => 'status-returned',
                                            'delivered' => 'status-delivered',
                                            'failed' => 'status-failed'
                                        ])
                                        @php($statusIcons = [
                                            'pending_pickup' => 'fa-clock',
                                            'picked_up' => 'fa-box',
                                            'in_transit' => 'fa-truck',
                                            'retry' => 'fa-redo',
                                            'returning' => 'fa-undo',
                                            'returned' => 'fa-undo-alt',
                                            'delivered' => 'fa-check-circle',
                                            'failed' => 'fa-times-circle'
                                        ])
                                        <span class="status-badge {{ $statusClass[$shipment->status] ?? 'status-default' }}">
                                            <i class="fas {{ $statusIcons[$shipment->status] ?? 'fa-circle' }}"></i>
                                            {{ $statuses[$shipment->status] ?? $shipment->status }}
                                        </span>
                                    </td>
                                    <td class="cod-amount">
                                        <span class="amount {{ $shipment->cod_amount > 0 ? 'positive' : 'zero' }}">
                                            {{ number_format($shipment->cod_amount, 0) }}đ
                                        </span>
                                    </td>
                                    <td class="shipping-fee">
                                        <span class="fee">{{ number_format($shipment->shipping_fee, 0) }}đ</span>
                                    </td>
                                    <td class="created-date">
                                        <span class="date">{{ $shipment->created_at->format('d/m/Y') }}</span>
                                        <span class="time">{{ $shipment->created_at->format('H:i') }}</span>
                                    </td>
                                    <td class="actions">
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($shipments->hasPages())
                    <div class="pagination-wrapper">
                        {{ $shipments->links('vendor.pagination.perfume') }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <style>
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            gap: 24px;
        }

        .page-header-content {
            flex: 1;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-title i {
            color: #3b82f6;
            font-size: 28px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 16px;
            font-weight: 400;
            margin: 0;
        }

        .page-header-actions {
            display: flex;
            gap: 12px;
        }

        /* Card Styles */
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.06);
            transform: translateY(-1px);
        }

        .card-header {
            background: #f8fafc;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title i {
            color: #3b82f6;
        }

        .card-stats {
            display: flex;
            gap: 16px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-item i {
            color: #3b82f6;
        }

        .card-body {
            padding: 24px;
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-search {
            grid-column: span 2;
        }

        .filter-status {
            grid-column: span 1;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 500;
            color: #4b5563;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .search-input {
            padding: 10px 12px 10px 36px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            height: 40px;
        }

        .search-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .select-multiple {
            min-height: 40px;
            max-height: 120px;
            overflow-y: auto;
        }

        .sort-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .sort-select {
            flex: 1;
            height: 40px;
        }

        .order-select {
            width: 60px;
            height: 40px;
            text-align: center;
            font-weight: 600;
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            margin-top: 8px;
        }

        /* Compact Button Styles */
        .btn-filter {
            padding: 10px 20px;
            height: 40px;
        }

        .btn-reset {
            padding: 10px 16px;
            height: 40px;
        }

        /* Button Styles */
        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn i {
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-outline {
            background: #ffffff;
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }

        .btn-outline:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-create {
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .shipments-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            font-size: 14px;
        }

        .shipments-table th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .shipments-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .shipments-table tbody tr:hover {
            background: #f8fafc;
        }

        .shipment-row {
            transition: all 0.2s ease;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-picked {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-transit {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-retry {
            background: #fef3c7;
            color: #92400e;
        }

        .status-returning {
            background: #fef3c7;
            color: #92400e;
        }

        .status-returned {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-default {
            background: #f3f4f6;
            color: #374151;
        }

        /* Code Badges */
        .code-badge, .tracking-badge {
            background: #f1f5f9;
            color: #0f172a;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            font-weight: 500;
        }

        .carrier-name {
            font-weight: 500;
            color: #374151;
        }

        /* Amount Styles */
        .amount.positive {
            color: #059669;
            font-weight: 600;
        }

        .amount.zero {
            color: #6b7280;
        }

        .fee {
            color: #374151;
            font-weight: 500;
        }

        /* Date Styles */
        .created-date {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .date {
            font-weight: 500;
            color: #374151;
        }

        .time {
            font-size: 12px;
            color: #6b7280;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action:hover {
            transform: translateY(-1px);
        }

        .btn-view:hover {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-edit:hover {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-delete:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Pagination */
        .pagination-wrapper {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 64px;
            color: #3b82f6;
            margin-bottom: 24px;
            opacity: 0.7;
        }

        .empty-state-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .empty-state-text {
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.6;
            font-size: 16px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        /* Filter Modal Styles */
        .filter-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
            backdrop-filter: blur(4px);
        }

        .filter-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .filter-modal-content {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s ease-in-out;
        }

        .filter-modal.active .filter-modal-content {
            transform: scale(1) translateY(0);
        }

        .filter-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .filter-modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-modal-title i {
            color: #3b82f6;
        }

        .filter-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .filter-modal-close:hover {
            color: #3b82f6;
            background: #f1f5f9;
        }

        .filter-modal-body {
            padding: 24px;
            overflow-y: auto;
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .filter-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-filter {
            padding: 12px 24px;
            height: 44px;
            font-weight: 600;
        }

        .btn-reset {
            padding: 12px 20px;
            height: 44px;
        }

        .btn-cancel {
            padding: 12px 20px;
            height: 44px;
        }

        /* Active Filters Display */
        .active-filters {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }

        .active-filters-header {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .active-filters-header i {
            color: #3b82f6;
        }

        .active-filters-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-tag {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #c7d2fe;
            transition: all 0.2s ease;
        }

        .filter-tag:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.2);
        }

        .filter-tag .remove-filter {
            color: #1e40af;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            padding: 2px;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .filter-tag .remove-filter:hover {
            color: #2563eb;
            background: rgba(30, 64, 175, 0.1);
        }

        /* Filter Form in Modal */
        .filter-form {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0;
            text-transform: none;
            letter-spacing: 0;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .search-input {
            padding: 12px 16px 12px 44px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            height: 44px;
            width: 100%;
        }

        .search-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .select-multiple {
            min-height: 44px;
            max-height: 120px;
            overflow-y: auto;
            padding: 12px 16px;
        }

        .sort-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .sort-select {
            flex: 1;
            height: 44px;
            padding: 12px 16px;
        }

        .order-select {
            width: 80px;
            height: 44px;
            text-align: center;
            font-weight: 600;
            padding: 12px 16px;
        }

        /* Status Dropdown Styles */
        .status-dropdown {
            position: relative;
            width: 100%;
        }

        .status-dropdown-toggle {
            width: 100%;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            color: #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 40px;
        }

        .status-dropdown-toggle:hover {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .status-dropdown-text {
            font-weight: 500;
            color: #374151;
        }

        .status-dropdown-icon {
            color: #9ca3af;
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .status-dropdown.active .status-dropdown-icon {
            transform: rotate(180deg);
        }

        .status-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 100;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            flex-direction: column;
            transform-origin: top;
            transition: all 0.3s ease-in-out;
            margin-top: 4px;
        }

        .status-dropdown.active .status-dropdown-menu {
            display: flex !important;
        }

        .status-dropdown-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
            border-radius: 8px 8px 0 0;
        }

        .status-dropdown-header span {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        .clear-all-status {
            background: none;
            border: none;
            font-size: 13px;
            color: #3b82f6;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }

        .clear-all-status:hover {
            color: #2563eb;
        }

        .status-options {
            padding: 12px 16px;
        }

        .status-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 4px;
            padding: 8px 12px;
            margin: 2px 0;
        }

        .status-option:hover {
            background: #f3f4f6;
        }

        .status-option input[type="checkbox"] {
            display: none; /* Hide default checkbox */
        }

        .status-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .status-option input[type="checkbox"]:checked + .status-checkbox {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .status-option input[type="checkbox"]:checked + .status-checkbox::after {
            content: '\f00c'; /* Font Awesome checkmark */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            font-size: 12px;
        }

        .status-label {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }

        /* Custom scrollbar for dropdown */
        .status-dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .status-dropdown-menu::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .status-dropdown-menu::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .status-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .filter-modal-content {
                width: 95%;
                max-height: 95vh;
                margin: 20px;
            }

            .filter-modal-body {
                padding: 20px;
                gap: 16px;
            }

            .filter-modal-footer {
                padding: 16px 20px;
                flex-direction: column;
                gap: 8px;
            }

            .btn-filter,
            .btn-reset,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .filter-modal-content {
                width: 100%;
                height: 100%;
                margin: 0;
                border-radius: 0;
            }

            .filter-modal-header {
                padding: 16px 20px;
            }

            .filter-modal-body {
                padding: 16px 20px;
            }

            .filter-modal-footer {
                padding: 16px 20px;
            }
        }

        /* Responsive adjustments for main page */
        @media (max-width: 1024px) {
            .filter-search {
                grid-column: span 1;
            }
            
            .filter-row {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }

            .page-title {
                font-size: 24px;
            }

            .filter-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .filter-search {
                grid-column: span 1;
            }
            
            .filter-status {
                grid-column: span 1;
            }
            
            .sort-controls {
                flex-direction: column;
                gap: 8px;
            }
            
            .order-select {
                width: 100%;
            }
            
            .filter-actions {
                flex-direction: column;
                gap: 8px;
            }

            .card-header {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .shipments-table th,
            .shipments-table td {
                padding: 12px 16px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .btn-action {
                width: 28px;
                height: 28px;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 20px;
            }

            .card-body {
                padding: 16px;
            }

            .shipments-table th,
            .shipments-table td {
                padding: 8px 12px;
                font-size: 12px;
            }

            .status-badge {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>

    <script>
        // Filter Modal Functions
        function openFilterModal() {
            const modal = document.getElementById('filterModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeFilterModal() {
            const modal = document.getElementById('filterModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('filterModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeFilterModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFilterModal();
            }
        });

        // Auto-close modal after form submission
        document.querySelector('.filter-form').addEventListener('submit', function() {
            setTimeout(closeFilterModal, 100);
        });

        // Status Dropdown Functions
        function toggleStatusDropdown() {
            const dropdown = document.querySelector('.status-dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns first
            document.querySelectorAll('.status-dropdown').forEach(d => {
                d.classList.remove('active');
            });
            
            // Toggle current dropdown
            if (!isActive) {
                dropdown.classList.add('active');
            }
        }

        function updateStatusText() {
            const statusDropdown = document.querySelector('.status-dropdown');
            const selectedCount = statusDropdown.querySelectorAll('.status-option input:checked').length;
            const statusDropdownToggle = statusDropdown.querySelector('.status-dropdown-toggle');
            const statusDropdownText = statusDropdownToggle.querySelector('.status-dropdown-text');

            if (selectedCount === 0) {
                statusDropdownText.textContent = 'Chọn trạng thái';
            } else {
                statusDropdownText.textContent = `${selectedCount} trạng thái đã chọn`;
            }
        }

        function clearAllStatus() {
            const statusDropdown = document.querySelector('.status-dropdown');
            const statusOptions = statusDropdown.querySelectorAll('.status-option input[type="checkbox"]');
            statusOptions.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateStatusText();
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const statusDropdowns = document.querySelectorAll('.status-dropdown');
            statusDropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        });

        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.status-dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    </script>
@endsection


