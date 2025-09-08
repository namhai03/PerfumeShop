# Hướng dẫn tích hợp N8N với PerfumeShop

## Tổng quan
Hướng dẫn này sẽ giúp bạn tích hợp n8n platform với ứng dụng Laravel PerfumeShop để tự động hóa các quy trình nghiệp vụ.

## Bước 1: Thiết lập n8n Platform

### 1.1 Đăng ký tài khoản
- Truy cập https://cloud.n8n.io/
- Đăng ký tài khoản miễn phí
- Tạo workspace mới cho dự án PerfumeShop

### 1.2 Tạo Workflow đầu tiên: "Cảnh báo tồn kho thấp"

#### Node 1: Webhook Trigger
- **Loại**: Webhook
- **Method**: GET
- **Path**: `/low-stock-alert`
- **Authentication**: None

#### Node 2: HTTP Request (Gọi API Laravel)
- **Loại**: HTTP Request
- **Method**: GET
- **URL**: `{{ $json.webhookUrl }}/api/n8n/inventory/low-stock`
- **Headers**: 
  ```
  Content-Type: application/json
  ```

#### Node 3: Filter
- **Loại**: IF
- **Condition**: `{{ $json.data.length > 0 }}`

#### Node 4: Email Notification
- **Loại**: Email
- **Subject**: "🚨 Cảnh báo: Sản phẩm tồn kho thấp - PerfumeShop"
- **Body**: 
  ```
  Có {{ $json.count }} sản phẩm có tồn kho thấp:
  
  {{ $json.data.map(item => `- ${item.name} (SKU: ${item.sku}): ${item.stock}/${item.low_stock_threshold}`).join('\n') }}
  
  Thời gian: {{ $json.timestamp }}
  ```

## Bước 2: Cấu hình Laravel

### 2.1 Thêm biến môi trường
Thêm vào file `.env`:
```env
N8N_WEBHOOK_URL=https://your-n8n-instance.com/webhook
N8N_API_KEY=your-n8n-api-key
N8N_BASE_URL=https://your-n8n-instance.com
```

### 2.2 Test API endpoints
Truy cập: `http://localhost:8000/n8n/test`

### 2.3 Chạy command kiểm tra
```bash
# Kiểm tra tồn kho thấp
php artisan inventory:check-low-stock

# Kiểm tra và gửi thông báo
php artisan inventory:check-low-stock --send-alert
```

## Bước 3: Các API Endpoints có sẵn

### Inventory APIs
- `GET /api/n8n/inventory/low-stock` - Lấy sản phẩm tồn kho thấp
- `GET /api/n8n/inventory/overview` - Tổng quan kho hàng

### Order APIs
- `GET /api/n8n/orders/pending` - Đơn hàng chờ xử lý
- `POST /api/n8n/orders/{order}/update-status` - Cập nhật trạng thái đơn hàng

### Product APIs
- `GET /api/n8n/products/expiring-soon` - Sản phẩm sắp hết hạn
- `POST /api/n8n/products/{product}/update-stock` - Cập nhật tồn kho

## Bước 4: Workflow đề xuất tiếp theo

### 4.1 Workflow "Thông báo đơn hàng mới"
- Trigger: Webhook khi có đơn hàng mới
- Action: Gửi email/SMS thông báo cho khách hàng

### 4.2 Workflow "Báo cáo hàng ngày"
- Trigger: Cron job hàng ngày
- Action: Tạo và gửi báo cáo doanh thu

### 4.3 Workflow "Cảnh báo sản phẩm hết hạn"
- Trigger: Cron job hàng tuần
- Action: Gửi thông báo sản phẩm sắp hết hạn

## Bước 5: Troubleshooting

### 5.1 Kiểm tra log
```bash
tail -f storage/logs/laravel.log
```

### 5.2 Test API endpoints
```bash
curl http://localhost:8000/api/n8n/inventory/low-stock
```

### 5.3 Kiểm tra cấu hình
```bash
php artisan config:cache
php artisan route:clear
```

## Lưu ý quan trọng

1. **Bảo mật**: Luôn sử dụng HTTPS cho production
2. **Rate limiting**: Cấu hình rate limiting cho API endpoints
3. **Error handling**: Xử lý lỗi gracefully để không ảnh hưởng đến ứng dụng chính
4. **Monitoring**: Theo dõi performance và uptime của n8n workflows
5. **Backup**: Backup workflow configurations thường xuyên

## Hỗ trợ

Nếu gặp vấn đề, hãy kiểm tra:
1. Log files trong `storage/logs/`
2. Network connectivity giữa Laravel và n8n
3. API key và webhook URL configuration
4. n8n workflow execution logs





