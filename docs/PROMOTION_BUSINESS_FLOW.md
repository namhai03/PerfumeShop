# Luồng Nghiệp Vụ Khuyến Mại - PerfumeShop

## 📋 Tổng Quan Hệ Thống Khuyến Mại

### 🎯 Các Loại Khuyến Mại Được Hỗ Trợ
1. **Giảm theo phần trăm** - Giảm X% giá trị đơn hàng
2. **Giảm số tiền cố định** - Giảm X VNĐ
3. **Miễn phí vận chuyển** - Miễn phí ship
4. **Mua X tặng Y** - Mua X sản phẩm tặng Y sản phẩm

### 🎯 Phạm Vi Áp Dụng
- **Toàn bộ đơn hàng** - Áp dụng cho toàn bộ giá trị đơn
- **Theo sản phẩm cụ thể** - Chỉ áp dụng cho SP được chọn
- **Theo danh mục** - Áp dụng cho tất cả SP trong danh mục

---

## 🔄 Luồng Nghiệp Vụ Chi Tiết

### 1. 📝 **Tạo Khuyến Mại** (`/promotions/create`)
```
Người dùng → Nhập thông tin → Validation → Lưu vào DB → Redirect
```

**Liên kết với:**
- **Danh sách khuyến mại** (`/promotions`) - Sau khi tạo thành công
- **Chi tiết khuyến mại** (`/promotions/{id}`) - Để xem và chỉnh sửa

### 2. 📊 **Quản Lý Khuyến Mại** (`/promotions`)
```
Danh sách CTKM → Xem chi tiết → Chỉnh sửa/Kích hoạt/Hủy → Cập nhật DB
```

**Liên kết với:**
- **Tạo mới** (`/promotions/create`) - Nút "Tạo khuyến mại"
- **Chi tiết** (`/promotions/{id}`) - Click vào từng CTKM
- **Báo cáo sử dụng** (`/promotions/{id}/usage`) - Thống kê lượt dùng

### 3. 🛒 **Áp Dụng Trong Đơn Hàng** (`/orders/create`)
```
Tạo đơn hàng → Nhập mã KM → Validate điều kiện → Tính toán giảm giá → Cập nhật tổng tiền
```

**Logic Validation:**
- ✅ Kiểm tra thời gian hiệu lực
- ✅ Kiểm tra điều kiện đơn hàng tối thiểu
- ✅ Kiểm tra số lượng sản phẩm
- ✅ Kiểm tra phạm vi áp dụng (SP/Danh mục)
- ✅ Kiểm tra giới hạn sử dụng
- ✅ Kiểm tra kênh bán hàng
- ✅ Kiểm tra nhóm khách hàng

**Liên kết với:**
- **Danh sách sản phẩm** (`/products`) - Để chọn SP áp dụng KM
- **Danh mục sản phẩm** (`/categories`) - Để chọn danh mục áp dụng KM
- **Nhóm khách hàng** (`/customer-groups`) - Để áp dụng cho nhóm KH cụ thể

### 4. 💰 **Tính Toán Giảm Giá** (Service Layer)
```
Input: Đơn hàng + Mã KM → Validate → Tính toán → Output: Số tiền giảm
```

**Công Thức Tính Toán:**
- **Phần trăm**: `min(discount_value% * order_total, max_discount_amount)`
- **Số tiền cố định**: `min(discount_value, max_discount_amount)`
- **Miễn phí ship**: `shipping_fee = 0`
- **Mua X tặng Y**: `Giảm giá sản phẩm Y`

### 5. 📈 **Báo Cáo và Thống Kê**
```
Dữ liệu sử dụng KM → Tổng hợp → Hiển thị biểu đồ → Xuất báo cáo
```

**Các Metric:**
- Tổng lượt sử dụng
- Tổng giá trị giảm giá
- Tỷ lệ chuyển đổi
- Top khuyến mại hiệu quả nhất
- Phân tích theo thời gian

**Liên kết với:**
- **Dashboard tổng quan** (`/dashboard`) - Hiển thị KPIs
- **Báo cáo doanh thu** (`/reports/revenue`) - Tác động đến doanh thu
- **Phân tích khách hàng** (`/customers/analytics`) - Hành vi sử dụng KM

---

## 🔗 Liên Kết Với Các Module Khác

### 🛍️ **Module Đơn Hàng**
- **Tạo đơn hàng**: Áp dụng mã khuyến mại
- **Thanh toán**: Tính toán giảm giá cuối cùng
- **Hủy đơn**: Hoàn lại lượt sử dụng KM
- **Hoàn trả**: Xử lý KM đã áp dụng

### 👥 **Module Khách Hàng**
- **Nhóm khách hàng**: Áp dụng KM cho nhóm cụ thể
- **Lịch sử mua hàng**: Theo dõi việc sử dụng KM
- **Điểm thưởng**: Tích hợp với hệ thống loyalty

### 📦 **Module Sản Phẩm**
- **Danh mục**: Áp dụng KM theo danh mục
- **Sản phẩm cụ thể**: Chọn SP áp dụng KM
- **Giá sản phẩm**: Tính toán giá sau KM

### 🚚 **Module Vận Chuyển**
- **Miễn phí ship**: Tích hợp với tính phí vận chuyển
- **Điều kiện ship**: KM có thể thay đổi điều kiện ship

### 💳 **Module Thanh Toán**
- **COD**: Áp dụng KM cho đơn COD
- **Online**: Áp dụng KM cho đơn online
- **Tích hợp**: Với các cổng thanh toán

---

## ⚙️ **Cấu Hình và Thiết Lập**

### 🔧 **Cài Đặt Hệ Thống**
- **Độ ưu tiên**: Thứ tự áp dụng KM khi có nhiều KM
- **Stackable**: Cho phép kết hợp nhiều KM
- **Auto-generate code**: Tự động tạo mã KM
- **Validation rules**: Quy tắc validation tùy chỉnh

### 📊 **Monitoring và Alert**
- **Sắp hết hạn**: Cảnh báo KM sắp hết hạn
- **Hết lượt sử dụng**: Thông báo khi KM hết lượt
- **Performance**: Theo dõi hiệu quả KM
- **Abuse detection**: Phát hiện lạm dụng KM

---

## 🎯 **Best Practices**

### ✅ **Thiết Kế Khuyến Mại**
1. **Mục tiêu rõ ràng**: Tăng doanh thu, thu hút KH mới, thanh lý tồn kho
2. **Điều kiện hợp lý**: Không quá khó, không quá dễ
3. **Thời gian phù hợp**: Tránh xung đột với các KM khác
4. **Budget control**: Kiểm soát ngân sách KM

### ✅ **Quản Lý Rủi Ro**
1. **Giới hạn sử dụng**: Tránh lạm dụng
2. **Validation chặt chẽ**: Kiểm tra điều kiện kỹ lưỡng
3. **Backup plan**: Có kế hoạch dự phòng
4. **Monitoring**: Theo dõi real-time

### ✅ **Tối Ưu Hóa**
1. **A/B Testing**: Test hiệu quả các loại KM
2. **Personalization**: Cá nhân hóa KM theo KH
3. **Cross-selling**: KM kết hợp với upsell
4. **Retention**: KM giữ chân KH cũ

---

## 📱 **API Endpoints**

### 🔌 **Promotion API**
```
GET    /api/promotions              # Danh sách KM
POST   /api/promotions              # Tạo KM mới
GET    /api/promotions/{id}         # Chi tiết KM
PUT    /api/promotions/{id}         # Cập nhật KM
DELETE /api/promotions/{id}         # Xóa KM
POST   /api/promotions/validate     # Validate mã KM
GET    /api/promotions/{id}/usage   # Thống kê sử dụng
```

### 🔌 **Order Integration API**
```
POST   /api/orders/apply-promotion  # Áp dụng KM vào đơn hàng
POST   /api/orders/remove-promotion # Bỏ KM khỏi đơn hàng
GET    /api/orders/promotion-history # Lịch sử KM trong đơn hàng
```

---

## 🚀 **Roadmap Phát Triển**

### 📅 **Phase 1** (Hiện tại)
- ✅ Tạo và quản lý KM cơ bản
- ✅ Áp dụng KM trong đơn hàng
- ✅ Validation điều kiện cơ bản

### 📅 **Phase 2** (Tương lai gần)
- 🔄 KM theo thời gian thực
- 🔄 KM cá nhân hóa
- 🔄 Tích hợp với email marketing
- 🔄 KM theo địa lý

### 📅 **Phase 3** (Dài hạn)
- 🔄 AI-powered promotion optimization
- 🔄 Dynamic pricing với KM
- 🔄 Cross-platform promotion sync
- 🔄 Advanced analytics và ML

---

*Tài liệu này được cập nhật thường xuyên để phản ánh các thay đổi trong hệ thống.*
