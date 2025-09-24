<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PerfumeShop - Hệ thống quản lý cửa hàng nước hoa: sản phẩm, đơn hàng, tồn kho, khuyến mại, báo cáo.">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#111827">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PerfumeShop - Quản lý cửa hàng nước hoa')</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" crossorigin="anonymous"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #fafbfc;
            color: #2d3748;
            line-height: 1.6;
            font-size: 14px;
            font-weight: 400;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #0f172a 0%, #111827 50%, #1f2937 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            letter-spacing: 0.2px;
            display: block;
            text-align: left;
            margin-bottom: 16px;
            padding: 0 20px;
        }

        .nav { list-style: none; }
        .nav-item { list-style: none; }

        .nav-section-title {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 8px 20px;
            margin-top: 8px;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.08);
            margin: 8px 20px;
            border-radius: 1px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .nav-link .icon { width: 18px; text-align: center; opacity: 0.9; }

        .nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: white;
        }

        .nav-link.active {
            background: rgba(59,130,246,0.18);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(59,130,246,0.35);
        }

        .sub-nav {
            list-style: none;
            margin: 6px 0 6px 0;
            padding-left: 16px;
            border-left: 1px dashed rgba(255,255,255,0.12);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .sub-nav.expanded {
            max-height: 500px;
        }

        .sub-nav .nav-link {
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 6px;
        }

        .sub-nav .nav-link.active {
            background: rgba(59,130,246,0.22);
            box-shadow: inset 0 0 0 1px rgba(59,130,246,0.35);
        }

        /* Nav link với sub-nav */
        .nav-item.has-subnav > .nav-link {
            position: relative;
            cursor: pointer;
        }

        .nav-item.has-subnav > .nav-link::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            transition: transform 0.3s ease;
            font-size: 12px;
            opacity: 0.7;
        }

        .nav-item.has-subnav.expanded > .nav-link::after {
            transform: rotate(180deg);
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 12px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #718096;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            font-size: 12px;
        }

        .header-icon i { font-size: 12px; }
        .table i, .table .fa, .table .fas, .table .far { font-size: 14px; }

        .header-icon:hover {
            background: #4299e1;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 6px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .user-profile:hover {
            background: #4299e1;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        }

        .user-avatar {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            background: #4299e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 10px;
        }

        /* Content */
        .content {
            padding: 28px 32px;
            background: #fafbfc;
            min-height: calc(100vh - 100px);
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 28px;
            letter-spacing: -0.3px;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 50px;
            height: 3px;
            background: #4299e1;
            border-radius: 2px;
        }

        /* Cards */
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .table th,
        .table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 13px;
            text-transform: none;
            letter-spacing: 0.2px;
        }

        .table tr:hover {
            background-color: #f7fafc;
            transition: background-color 0.15s ease;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        /* Xử lý nội dung dài trong bảng */
        .table td {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table td.product-name {
            white-space: normal;
            max-width: 250px;
        }

        .table td.actions {
            white-space: nowrap;
            max-width: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn i { font-size: 14px; }

        .btn-primary {
            background: #4299e1;
            color: white;
        }

        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        }

        /* Disabled state for buttons */
        .btn:disabled,
        .btn[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
            box-shadow: none;
        }

        .btn-outline {
            background-color: white;
            color: #4299e1;
            border: 1px solid #4299e1;
        }

        .btn-outline:hover {
            background: #4299e1;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a5568;
            font-size: 14px;
            letter-spacing: 0.1px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }

        .form-control:focus {
            outline: none;
            border-color: #4299e1;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #ffffff;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }

        .empty-state-icon {
            font-size: 56px;
            color: #4299e1;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .empty-state-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
            letter-spacing: -0.2px;
        }

        .empty-state-text {
            color: #718096;
            margin-bottom: 28px;
            line-height: 1.6;
            font-size: 15px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-danger {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }

        /* Pagination - professional look, hide oversized chevrons */
        .pagination-controls nav, .pagination { display:flex; gap:8px; align-items:center; justify-content:flex-end; }
        .pagination li { list-style:none; }
        .pagination a, .pagination span { 
            padding: 6px 10px; border:1px solid #e5e7eb; border-radius:6px; text-decoration:none; color:#374151; background:#fff; 
        }
        .pagination a:hover { background:#f3f4f6; }
        .pagination .active span, .pagination a[aria-current="page"] { background:#4299e1; color:#fff; border-color:#4299e1; }
        .pagination svg { width:14px; height:14px; display:none; } /* ẩn icon mũi tên lớn nếu có */
        .pagination a[rel="prev"], .pagination a[rel="next"] { color:#4299e1; }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: white;
            margin: 8% auto;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 480px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.2s ease;
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .modal-header h3 { font-size: 18px; font-weight: 600; color: #2d3748; }
        .close { font-size: 24px; font-weight: 300; color: #718096; cursor: pointer; transition: color 0.2s ease; }
        .close:hover { color: #e53e3e; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
            .search-bar { width: 200px; }
            .header { padding: 16px 20px; }
            .content { padding: 20px; }
            .page-title { font-size: 24px; }
            .card { padding: 20px; }
            .table { font-size: 12px; }
            .table th, .table td { padding: 8px 12px; }
            .table td.product-name { max-width: 150px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="{{ route('dashboard.index') }}" class="logo">PerfumeShop</a>
        <nav>
            <ul class="nav">
                <li class="nav-item">
                    <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                        <i class="icon fas fa-gauge-high"></i>
                        Tổng quan
                    </a>
                </li>
                <li class="nav-item has-subnav {{ request()->routeIs('orders.*') ? 'expanded' : '' }}">
                    <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                        <i class="icon fas fa-receipt"></i>
                        Đơn hàng
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('orders.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') ? 'active' : '' }}">Tất cả đơn hàng</a></li>
                        <li class="nav-item"><a href="{{ route('orders.sales') }}" class="nav-link {{ request()->routeIs('orders.sales') ? 'active' : '' }}">Đơn bán</a></li>
                        <li class="nav-item"><a href="{{ route('orders.returns') }}" class="nav-link {{ request()->routeIs('orders.returns') ? 'active' : '' }}">Đơn trả</a></li>
                        <li class="nav-item"><a href="{{ route('orders.drafts') }}" class="nav-link {{ request()->routeIs('orders.drafts') ? 'active' : '' }}">Đơn nháp</a></li>
                    </ul>
                </li>
                <li class="nav-item has-subnav {{ request()->routeIs('shipping.*') || request()->routeIs('shipments.*') ? 'expanded' : '' }}">
                    <a href="{{ route('shipping.overview') }}" class="nav-link {{ request()->routeIs('shipping.*') || request()->routeIs('shipments.*') ? 'active' : '' }}">
                        <i class="icon fas fa-truck"></i>
                        Vận chuyển
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('shipping.*') || request()->routeIs('shipments.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('shipping.overview') }}" class="nav-link {{ request()->routeIs('shipping.overview') ? 'active' : '' }}">Tổng quan</a></li>
                        <li class="nav-item"><a href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}">Vận đơn</a></li>
                    </ul>
                </li>

                <li class="nav-item has-subnav {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'expanded' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="icon fas fa-box"></i>
                        Sản phẩm
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'expanded' : '' }}">
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                Danh sách sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">Danh mục sản phẩm</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">Bảng giá</a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-subnav {{ request()->routeIs('inventory.*') ? 'expanded' : '' }}">
                    <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <i class="icon fas fa-warehouse"></i>
                        Quản lý kho
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('inventory.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">Tồn kho</a></li>
                        <li class="nav-item"><a href="{{ route('inventory.history') }}" class="nav-link {{ request()->routeIs('inventory.history') ? 'active' : '' }}">Lịch sử</a></li>
                    </ul>
                </li>
                <li class="nav-item has-subnav {{ request()->routeIs('customers.*') || request()->routeIs('customer-groups.*') ? 'expanded' : '' }}">
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') || request()->routeIs('customer-groups.*') ? 'active' : '' }}">
                        <i class="icon fas fa-users"></i>
                        Khách hàng
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('customers.*') || request()->routeIs('customer-groups.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">Danh sách khách hàng</a></li>
                        <li class="nav-item"><a href="{{ route('customer-groups.index') }}" class="nav-link {{ request()->routeIs('customer-groups.*') ? 'active' : '' }}">Nhóm khách hàng</a></li>
                    </ul>
                </li>
                <li class="nav-item has-subnav {{ request()->routeIs('cashbook.*') ? 'expanded' : '' }}">
                    <a href="{{ route('cashbook.index') }}" class="nav-link {{ request()->routeIs('cashbook.*') ? 'active' : '' }}">
                        <i class="icon fas fa-wallet"></i>
                        Sổ quỹ
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('cashbook.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('cashbook.index') }}" class="nav-link {{ request()->routeIs('cashbook.index') ? 'active' : '' }}">Tất cả phiếu</a></li>
                        <li class="nav-item"><a href="{{ route('cashbook.accounts.index') }}" class="nav-link {{ request()->routeIs('cashbook.accounts.*') ? 'active' : '' }}">Tài khoản</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('promotions.index') }}" class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}">
                        <i class="icon fas fa-gift"></i>
                        Khuyến mại
                    </a>
                </li>
                <li class="nav-item has-subnav {{ request()->routeIs('reports.*') ? 'expanded' : '' }}">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="icon fas fa-chart-line"></i>
                        Báo cáo
                    </a>
                    <ul class="sub-nav {{ request()->routeIs('reports.*') ? 'expanded' : '' }}">
                        <li class="nav-item"><a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">Danh sách báo cáo</a></li>
                        <li class="nav-item"><a href="{{ route('reports.overview') }}" class="nav-link {{ request()->routeIs('reports.overview') ? 'active' : '' }}">Tổng quan báo cáo</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('omni-ai.index') }}" class="nav-link {{ request()->routeIs('omni-ai.*') ? 'active' : '' }}">
                        <i class="icon fas fa-comments"></i>
                        Chat OmniAI
                    </a>
                </li>


                <div class="nav-divider"></div>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="icon fas fa-gear"></i>
                        Cấu hình
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-actions">
                <div class="header-icon">
                    <i class="fas fa-question"></i>
                </div>
                <div class="header-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="user-profile">
                    <div class="user-avatar">Ch</div>
                    <span>Admin</span>
                    <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @stack('styles')
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            // Tối ưu ảnh: bật lazy-load và decoding async cho tất cả ảnh, trừ ảnh đầu trang nếu cần LCP
            try {
                var imgs = document.querySelectorAll('img');
                imgs.forEach(function(img, idx){
                    if (!img.hasAttribute('loading')) {
                        img.setAttribute('loading', idx === 0 ? 'eager' : 'lazy');
                    }
                    if (!img.hasAttribute('decoding')) {
                        img.setAttribute('decoding', 'async');
                    }
                });
            } catch (e) {}

            // Xử lý date inputs
            var dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(function(input){
                input.addEventListener('focus', function(){
                    if (!this.value) {
                        this.dataset.tempToday = '1';
                        try { this.valueAsDate = new Date(); } catch (e) {}
                        if (this.showPicker) { try { this.showPicker(); } catch (e) {} }
                    }
                });
                input.addEventListener('change', function(){
                    if (this.dataset.tempToday === '1') { this.dataset.userChanged = '1'; }
                });
                input.addEventListener('blur', function(){
                    if (this.dataset.tempToday === '1' && !this.dataset.userChanged) {
                        this.value = '';
                    }
                    delete this.dataset.tempToday;
                    delete this.dataset.userChanged;
                });
            });

            // Xử lý sidebar collapse/expand
            var navItemsWithSubnav = document.querySelectorAll('.nav-item.has-subnav');
            navItemsWithSubnav.forEach(function(navItem) {
                var navLink = navItem.querySelector('.nav-link');
                var subNav = navItem.querySelector('.sub-nav');
                
                navLink.addEventListener('click', function(e) {
                    // Chỉ toggle nếu không phải là link thực sự (href="#")
                    if (this.getAttribute('href') === '#') {
                        e.preventDefault();
                        
                        // Toggle expanded class
                        navItem.classList.toggle('expanded');
                        subNav.classList.toggle('expanded');
                    }
                });
            });
        });
    </script>
    <style>
        .qty-pos{ color:#16a34a; }
        .qty-neg{ color:#dc2626; }
        
        /* Order Status and Type Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-new {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-processing {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .badge-completed {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .badge-sale {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        
        .badge-return {
            background-color: #fce7f3;
            color: #be185d;
        }
        
        .badge-draft {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        
        .badge-default {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        
        /* Order Actions */
        .order-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        
        .order-actions .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }
        
        /* Order Table Styles */
        .order-number {
            font-weight: 600;
            color: #2d3748;
        }
        
        .customer-name {
            color: #4a5568;
            font-weight: 500;
        }
        
        .order-amount {
            font-weight: 600;
            color: #2d3748;
        }
        
        .order-date {
            color: #718096;
            font-size: 13px;
        }
    </style>
</body>
</html>
