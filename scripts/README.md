# Scripts Directory

Thư mục này chứa các script tiện ích cho dự án PerfumeShop.

## Các script có sẵn:

- `create_sample_orders.php` - Tạo dữ liệu đơn hàng mẫu
- `test_inventory_filters.php` - Test các bộ lọc kho hàng
- `test_inventory_history.php` - Test lịch sử kho hàng
- `test_timezone.php` - Test xử lý múi giờ
- `generate_vector_embeddings.php` - Tạo vector embeddings cho AI

## Cách sử dụng:

```bash
# Chạy script từ thư mục gốc
php scripts/script_name.php

# Hoặc từ thư mục scripts
cd scripts
php script_name.php
```

## Lưu ý:

- Đảm bảo đã cài đặt đầy đủ dependencies trước khi chạy
- Một số script có thể cần quyền truy cập database
- Kiểm tra log để theo dõi quá trình thực thi
