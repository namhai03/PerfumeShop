# Use Case Diagrams - Hệ thống quản lý cửa hàng nước hoa

## Tổng quan
Folder này chứa các biểu đồ Use Case được phân rã theo từng chức năng chính của hệ thống.

## Danh sách các file Use Case:

### 1. Xác thực và Bảo mật (`01_xac_thuc_bao_mat.puml`)
- Đăng nhập hệ thống
- Đăng xuất
- Đổi mật khẩu
- Quản lý phiên làm việc
- Khóa tài khoản
- Reset mật khẩu

### 2. Quản lý Sản phẩm (`02_quan_ly_san_pham.puml`)
- Thêm/Sửa/Xóa sản phẩm
- Quản lý biến thể sản phẩm
- Quản lý danh mục
- Upload hình ảnh
- Kích hoạt/Ẩn sản phẩm
- Nhập giá sản phẩm

### 3. Quản lý Khách hàng (`03_quan_ly_khach_hang.puml`)
- Thêm/Sửa/Xóa khách hàng
- Xem lịch sử mua hàng
- Phân nhóm khách hàng
- Thêm ghi chú khách hàng
- Cập nhật thông tin liên hệ
- Theo dõi giá trị khách hàng

### 4. Quản lý Đơn hàng (`04_quan_ly_don_hang.puml`)
- Tạo đơn hàng
- Thêm sản phẩm vào đơn
- Cập nhật trạng thái đơn
- In hóa đơn
- Áp dụng mã khuyến mãi
- Tính tổng tiền đơn hàng
- Ghi chú đơn hàng
- Hủy đơn hàng

### 5. Quản lý Tồn kho (`05_quan_ly_ton_kho.puml`)
- Xem tồn kho
- Nhập/Xuất kho
- Điều chỉnh tồn kho
- Kiểm kê tồn kho
- Xem lịch sử tồn kho
- Cảnh báo hết hàng
- Cập nhật giá nhập
- Theo dõi tồn kho theo SKU
- Báo cáo tồn kho

### 6. Sổ quỹ Thu Chi (`06_so_quy_thu_chi.puml`)
- Tạo phiếu thu/chi
- Đính kèm chứng từ
- Đối soát số dư
- Báo cáo thu chi
- Phân loại hạng mục
- Theo dõi dòng tiền
- Xuất báo cáo tài chính

### 7. Khuyến mãi (`07_khuyen_mai.puml`)
- Tạo chương trình khuyến mãi
- Quản lý mã giảm giá
- Theo dõi hiệu quả khuyến mãi
- Gợi ý khuyến mãi
- Áp dụng điều kiện khuyến mãi
- Quản lý lượt sử dụng
- Tạo mã khuyến mãi tự động

### 8. Vận chuyển (`08_van_chuyen.puml`)
- Tạo vận đơn
- Cập nhật trạng thái vận chuyển
- Theo dõi giao hàng
- Xử lý hoàn trả
- Xem lịch sử vận chuyển
- Quản lý địa chỉ giao hàng
- Tính phí vận chuyển
- Cập nhật thông tin người nhận
- Ghi chú vận chuyển
- Báo cáo vận chuyển

### 9. Báo cáo Thống kê (`09_bao_cao_thong_ke.puml`)
- Dashboard tổng quan
- Báo cáo doanh thu
- Báo cáo tồn kho
- Báo cáo khách hàng
- Báo cáo đơn hàng
- Xuất báo cáo Excel
- Tóm tắt kinh doanh
- Báo cáo theo thời gian
- So sánh kỳ trước
- Phân tích xu hướng
- Báo cáo hiệu quả khuyến mãi

### 10. AI Agents Hỗ trợ (`10_ai_agents_ho_tro.puml`)
- Gợi ý sản phẩm
- Phân tích xu hướng bán hàng
- Dự đoán nhu cầu tồn kho
- Trợ lý tra cứu thông tin
- Cảnh báo bất thường
- Gợi ý khuyến mãi thông minh
- Tóm tắt tình hình kinh doanh
- Phân tích khách hàng
- Dự báo doanh thu
- Tối ưu hóa giá bán

## Cách sử dụng:

1. **Mở từng file riêng lẻ** để xem chi tiết từng chức năng
2. **Sử dụng với PlantUML** hoặc các công cụ vẽ khác
3. **Tùy chỉnh** theo nhu cầu cụ thể của báo cáo
4. **Kết hợp** các file để tạo biểu đồ tổng quan

## Lưu ý:
- Tất cả các biểu đồ đều được thiết kế theo chiều dọc
- Sử dụng hidden connections để sắp xếp layout
- Có thể xuất ra PNG/SVG để đưa vào báo cáo
- Mỗi file tập trung vào một chức năng cụ thể, dễ quản lý và chỉnh sửa

