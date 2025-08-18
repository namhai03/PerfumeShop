# PerfumeShop - Hệ thống quản lý cửa hàng nước hoa

## Mô tả
PerfumeShop là một hệ thống quản lý cửa hàng nước hoa được xây dựng bằng Laravel, cung cấp các chức năng quản lý sản phẩm, kho hàng và bán hàng.

## Tính năng chính

### Trang Danh sách sản phẩm
- **Giao diện hiện đại**: Thiết kế theo phong cách admin dashboard với sidebar navigation
- **Tìm kiếm thông minh**: Tìm kiếm theo mã sản phẩm (SKU), tên sản phẩm, barcode
- **Bộ lọc đa dạng**: 
  - Kênh bán hàng (Shopee, Tiktok Shop, Offline)
  - Loại sản phẩm (Nước hoa nam, nữ, unisex)
  - Tag sản phẩm
  - Trạng thái có thể bán
- **Quản lý sản phẩm**:
  - Thêm sản phẩm mới
  - Import/Export Excel
  - Chọn nhiều sản phẩm (checkbox)
  - Phân trang linh hoạt (20, 50, 100 sản phẩm/trang)

### Cấu trúc dữ liệu sản phẩm
- Thông tin cơ bản: tên, mô tả, SKU, barcode
- Giá cả: giá nhập, giá bán
- Phân loại: danh mục, thương hiệu, kênh bán hàng
- Đặc tính: dung tích, nồng độ, xuất xứ
- Quản lý kho: số lượng tồn kho, ngày nhập hàng
- Trạng thái: hoạt động/không hoạt động

## Cài đặt và chạy

### Yêu cầu hệ thống
- PHP >= 8.1
- Composer
- SQLite (hoặc MySQL/PostgreSQL)

### Cài đặt
```bash
# Clone repository
git clone <repository-url>
cd PerfumeShop

# Cài đặt dependencies
composer install

# Tạo file .env
cp .env.example .env

# Tạo key ứng dụng
php artisan key:generate

# Chạy migrations
php artisan migrate

# Tạo dữ liệu mẫu
php artisan db:seed --class=ProductSeeder

# Chạy server
php artisan serve
```

### Truy cập
- URL: http://localhost:8000
- Tự động chuyển hướng đến trang Danh sách sản phẩm

## Cấu trúc project

```
PerfumeShop/
├── app/
│   ├── Http/Controllers/
│   │   └── ProductController.php      # Xử lý logic sản phẩm
│   └── Models/
│       └── Product.php                # Model sản phẩm
├── database/
│   ├── migrations/                    # Cấu trúc database
│   └── seeders/
│       └── ProductSeeder.php          # Dữ liệu mẫu
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php              # Layout chính
│   └── products/
│       └── index.blade.php            # Trang danh sách sản phẩm
└── routes/
    └── web.php                        # Định tuyến
```

## Chức năng nghiệp vụ

### Quản lý sản phẩm
- **CRUD operations**: Tạo, đọc, cập nhật, xóa sản phẩm
- **Import/Export**: Hỗ trợ file Excel (.xlsx, .xls) và CSV
- **Tìm kiếm và lọc**: Hỗ trợ tìm kiếm theo nhiều tiêu chí
- **Phân trang**: Hiển thị số lượng sản phẩm tùy chọn

### Quản lý kho
- **Theo dõi tồn kho**: Hiển thị số lượng hiện có
- **Lịch sử nhập hàng**: Ghi nhận ngày nhập và giá nhập
- **Trạng thái sản phẩm**: Hoạt động/không hoạt động

### Báo cáo và thống kê
- **Thống kê theo danh mục**: Phân loại sản phẩm
- **Thống kê theo kênh bán**: Theo dõi hiệu quả từng kênh
- **Xuất báo cáo**: Định dạng Excel/CSV

## Công nghệ sử dụng

- **Backend**: Laravel 10.x
- **Database**: SQLite (có thể thay đổi sang MySQL/PostgreSQL)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **UI Framework**: Custom CSS với Font Awesome icons
- **Excel Processing**: Maatwebsite Excel package

## Đóng góp

1. Fork project
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## License

Distributed under the MIT License. See `LICENSE` for more information.

## Liên hệ

- Email: [your-email@example.com]
- Project Link: [https://github.com/username/PerfumeShop](https://github.com/username/PerfumeShop)
