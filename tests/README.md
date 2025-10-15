# Tests Directory

Thư mục này chứa các test cases cho dự án PerfumeShop.

## Cấu trúc:

- `Feature/` - Test các tính năng end-to-end
- `Unit/` - Test các unit riêng lẻ
- `TestCase.php` - Base test case class
- `CreatesApplication.php` - Trait để tạo application

## Chạy tests:

```bash
# Chạy tất cả tests
php artisan test

# Chạy tests theo loại
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Chạy test cụ thể
php artisan test --filter=ProductTest
```

## Quy tắc viết test:

1. Tên file test phải kết thúc bằng `Test.php`
2. Tên class phải kết thúc bằng `Test`
3. Tên method test phải bắt đầu bằng `test_` hoặc sử dụng annotation `@test`
4. Sử dụng descriptive names cho test methods
5. Mỗi test phải độc lập và có thể chạy riêng lẻ
