# BIỂU ĐỒ TUẦN TỰ - HỆ THỐNG QUẢN LÝ CỬA HÀNG NƯỚC HOA

## Tổng quan

Folder này chứa các biểu đồ tuần tự (Sequence Diagrams) mô tả chi tiết luồng xử lý của các chức năng chính trong hệ thống PerfumeShop. Các biểu đồ được tạo bằng PlantUML để dễ dàng hiểu và bảo trì.

## Danh sách biểu đồ

**Tổng cộng: 27 biểu đồ tuần tự** tương ứng với 27 bảng trong báo cáo đặc tả chức năng.

### 1. Đăng nhập hệ thống (`01_dang_nhap_he_thong.puml`)
- **Mô tả**: Luồng xử lý đăng nhập của quản trị viên
- **Các bước chính**: Xác thực thông tin, tạo session, chuyển hướng
- **Xử lý lỗi**: Thông tin không hợp lệ, tài khoản bị khóa

### 2. Thêm sản phẩm (`02_them_san_pham.puml`)
- **Mô tả**: Luồng tạo sản phẩm mới với đầy đủ thông tin
- **Các bước chính**: Validate input, upload hình ảnh, tạo sản phẩm, gán danh mục, tạo biến thể
- **Xử lý lỗi**: Validation thất bại, file hình ảnh không hợp lệ

### 3. Tạo đơn hàng (`03_tao_don_hang.puml`)
- **Mô tả**: Luồng tạo đơn hàng mới với quản lý tồn kho tự động
- **Các bước chính**: Validate, tính toán tổng tiền, tạo/tìm khách hàng, cập nhật tồn kho
- **Xử lý lỗi**: Sản phẩm không đủ tồn kho, mã khuyến mãi không hợp lệ

### 4. Nhập kho (`04_nhap_kho.puml`)
- **Mô tả**: Luồng nhập hàng mới vào kho và cập nhật tồn kho
- **Các bước chính**: Validate input, tính toán tồn kho mới, cập nhật database, ghi nhận lịch sử
- **Xử lý lỗi**: Validation thất bại

### 5. Cập nhật trạng thái đơn hàng (`05_cap_nhat_trang_thai_don_hang.puml`)
- **Mô tả**: Luồng cập nhật trạng thái đơn hàng và quản lý tồn kho tự động
- **Các bước chính**: Validate, map status to type, cập nhật tồn kho theo loại đơn hàng
- **Xử lý lỗi**: Validation thất bại

### 6. Tạo phiếu thu (`06_tao_phieu_thu.puml`)
- **Mô tả**: Luồng tạo phiếu thu và cập nhật số dư tài khoản
- **Các bước chính**: Validate input, upload chứng từ, tạo phiếu thu, cập nhật số dư
- **Xử lý lỗi**: Validation thất bại

### 7. Tạo chương trình khuyến mãi (`07_tao_chuong_trinh_khuyen_mai.puml`)
- **Mô tả**: Luồng tạo chương trình khuyến mãi với điều kiện áp dụng
- **Các bước chính**: Validate input, tạo promotion, gán sản phẩm, thiết lập điều kiện
- **Xử lý lỗi**: Validation thất bại

### 8. Tạo vận đơn (`08_tao_van_don.puml`)
- **Mô tả**: Luồng tạo vận đơn cho đơn hàng đã xác nhận
- **Các bước chính**: Validate input, tính phí vận chuyển, tạo vận đơn, cập nhật trạng thái đơn hàng
- **Xử lý lỗi**: Validation thất bại

### 9. Báo cáo doanh thu (`09_bao_cao_doanh_thu.puml`)
- **Mô tả**: Luồng tạo báo cáo doanh thu với nhiều chiều phân tích
- **Các bước chính**: Validate date range, truy vấn dữ liệu, xử lý và hiển thị báo cáo, xuất Excel
- **Xử lý lỗi**: Validation thất bại

### 10. AI gợi ý sản phẩm (`10_ai_goi_y_san_pham.puml`)
- **Mô tả**: Luồng phân tích dữ liệu bằng AI và đưa ra gợi ý sản phẩm
- **Các bước chính**: Thu thập dữ liệu bán hàng và tồn kho, phân tích bằng AI, hiển thị gợi ý
- **Xử lý lỗi**: AI analysis thất bại

### 11-27. Các biểu đồ bổ sung
Từ file `11_dang_xuat.puml` đến `27_tro_ly_tra_cuu_thong_tin.puml` bao gồm tất cả các chức năng còn lại được mô tả trong báo cáo đặc tả chức năng, đảm bảo mỗi bảng có một biểu đồ tương ứng.

## Cách sử dụng

### 1. Xem biểu đồ trực tiếp
- Mở file `.puml` bằng editor hỗ trợ PlantUML
- Hoặc sử dụng online editor: https://www.plantuml.com/plantuml/

### 2. Xuất hình ảnh
```bash
# Cài đặt PlantUML
npm install -g node-plantuml

# Xuất PNG
puml generate 01_dang_nhap_he_thong.puml --png

# Xuất SVG
puml generate 01_dang_nhap_he_thong.puml --svg
```

### 3. Tích hợp vào tài liệu
- Copy nội dung file `.puml` vào tài liệu Markdown
- Sử dụng với các công cụ hỗ trợ PlantUML như GitLab, GitHub

## Cấu trúc biểu đồ

Mỗi biểu đồ tuần tự bao gồm:

1. **Actor**: Người sử dụng (Quản trị viên)
2. **Participants**: Các thành phần hệ thống tham gia
   - Trình duyệt (Browser)
   - Controller (xử lý logic)
   - Model (truy cập dữ liệu)
   - Database (lưu trữ)
   - External Services (AI, File Storage)

3. **Messages**: Các tương tác giữa các thành phần
4. **Alt blocks**: Xử lý các trường hợp thay thế và lỗi
5. **Notes**: Ghi chú giải thích logic

## Lưu ý kỹ thuật

- Tất cả các biểu đồ đều sử dụng database transaction để đảm bảo tính toàn vẹn dữ liệu
- Có xử lý lỗi và rollback transaction khi cần thiết
- Tích hợp với các dịch vụ bên ngoài như AI
- Hỗ trợ upload file và quản lý storage

## Cập nhật biểu đồ

Khi có thay đổi trong code hoặc logic nghiệp vụ:

1. Cập nhật file `.puml` tương ứng
2. Kiểm tra lại luồng xử lý
3. Cập nhật tài liệu này nếu cần
4. Test biểu đồ bằng PlantUML renderer

## Liên kết với tài liệu khác

- **Báo cáo đặc tả chức năng**: `BAO_CAO_DAC_TA_CHUC_NANG.md`
- **Use Case Diagrams**: `../usecaseuml/`
- **API Documentation**: `../docs/`
