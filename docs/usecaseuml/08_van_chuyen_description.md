# Mô tả Use Case - Vận chuyển

## Tổng quan
Module Vận chuyển cho phép quản trị viên quản lý quá trình giao hàng từ khi tạo vận đơn đến khi khách hàng nhận được hàng.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC8: Vận chuyển
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý vận chuyển tổng thể bao gồm tạo vận đơn và theo dõi giao hàng.

### UC8_1: Tạo vận đơn
**Mô tả**: Quản trị viên tạo vận đơn mới cho đơn hàng với thông tin địa chỉ giao hàng và phương thức vận chuyển.

### UC8_2: Cập nhật trạng thái vận chuyển
**Mô tả**: Quản trị viên cập nhật trạng thái vận chuyển như đang chuẩn bị, đang giao, đã giao hoặc gặp sự cố.

### UC8_3: Theo dõi giao hàng
**Mô tả**: Quản trị viên theo dõi tiến trình giao hàng và cập nhật thông tin vị trí hàng hóa cho khách hàng.

### UC8_4: Xử lý hoàn trả
**Mô tả**: Quản trị viên xử lý các trường hợp hoàn trả hàng hóa khi khách hàng không hài lòng hoặc hàng bị lỗi.

### UC8_5: Xem lịch sử vận chuyển
**Mô tả**: Quản trị viên xem lịch sử các đơn hàng đã vận chuyển để phân tích hiệu quả và thời gian giao hàng.

### UC8_6: Quản lý địa chỉ giao hàng
**Mô tả**: Quản trị viên quản lý và cập nhật thông tin địa chỉ giao hàng của khách hàng để đảm bảo giao hàng chính xác.

### UC8_7: Tính phí vận chuyển
**Mô tả**: Hệ thống tự động tính phí vận chuyển dựa trên khoảng cách, trọng lượng và phương thức vận chuyển.

### UC8_8: Cập nhật thông tin người nhận
**Mô tả**: Quản trị viên cập nhật thông tin người nhận hàng khi khách hàng thay đổi hoặc có yêu cầu đặc biệt.

### UC8_9: Ghi chú vận chuyển
**Mô tả**: Quản trị viên thêm ghi chú đặc biệt cho quá trình vận chuyển như yêu cầu giao hàng hoặc lưu ý quan trọng.

### UC8_10: Báo cáo vận chuyển
**Mô tả**: Quản trị viên tạo báo cáo vận chuyển để phân tích hiệu quả giao hàng và chi phí vận chuyển.

## Quan hệ giữa các Use Case
- **Include**: UC8 bao gồm tất cả các UC8_1 đến UC8_10

## Yêu cầu phi chức năng
- Thời gian tạo vận đơn < 2 giây
- Tự động tính phí vận chuyển theo khoảng cách
- Hỗ trợ theo dõi vị trí hàng hóa theo thời gian thực
- Tự động backup dữ liệu vận chuyển hàng ngày





