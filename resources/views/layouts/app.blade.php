<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PerfumeShop - Quản lý cửa hàng nước hoa')</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            width: 280px;
            background: linear-gradient(180deg, #2d3748 0%, #4a5568 50%, #718096 100%);
            color: white;
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .logo {
            font-size: 24px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            letter-spacing: 0.5px;
            display: block;
            text-align: center;
            margin-bottom: 32px;
            padding: 0 24px;
        }

        .nav-item {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0 20px 20px 0;
            margin-right: 16px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.2px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(3px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .sub-nav {
            list-style: none;
            margin-left: 20px;
            margin-top: 8px;
        }

        .sub-nav .nav-link {
            padding: 10px 24px;
            font-size: 13px;
            margin-right: 16px;
        }

        .sub-nav .nav-link.active {
            background: rgba(255,255,255,0.12);
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 20px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #f7fafc;
            border-radius: 8px;
            padding: 10px 16px;
            width: 380px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .search-bar:focus-within {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
            background: white;
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            margin-left: 12px;
            font-size: 14px;
            color: #4a5568;
        }

        .search-bar input::placeholder {
            color: #718096;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #718096;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .header-icon:hover {
            background: #4299e1;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
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
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #4299e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
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

        .btn-primary {
            background: #4299e1;
            color: white;
        }

        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.2s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }

        .close {
            font-size: 24px;
            font-weight: 300;
            color: #718096;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #e53e3e;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .search-bar {
                width: 200px;
            }
            
            .header {
                padding: 16px 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .card {
                padding: 20px;
            }

            /* Responsive cho bảng */
            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px 12px;
            }

            .table td.product-name {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="{{ route('products.index') }}" class="logo">PerfumeShop</a>
        
        <nav>
            <ul>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Đơn hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Vận chuyển
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        Sản phẩm
                    </a>
                    <ul class="sub-nav">
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link active">
                                Danh sách sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                Danh mục sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                Bảng giá
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Quản lý kho
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Khách hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Khuyến mại
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Sổ quỹ
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Báo cáo
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Chat OmniAI
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
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
            <div class="search-bar">
                <i class="fas fa-search" style="color: #6c757d;"></i>
                <input type="text" placeholder="Tìm kiếm (Ctrl + K)" />
            </div>
            
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
</body>
</html>
