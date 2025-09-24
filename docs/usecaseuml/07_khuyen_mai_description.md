# Mô tả Use Case - Khuyến mãi

## Tổng quan
Module Khuyến mãi cho phép quản trị viên tạo và quản lý các chương trình khuyến mãi, mã giảm giá để thu hút khách hàng và tăng doanh số bán hàng.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC7: Khuyến mãi
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý khuyến mãi tổng thể bao gồm tạo, sửa và theo dõi hiệu quả chương trình.

### UC7_1: Tạo chương trình khuyến mãi
**Mô tả**: Quản trị viên tạo chương trình khuyến mãi mới với các điều kiện và mức giảm giá cụ thể.

### UC7_2: Sửa khuyến mãi
**Mô tả**: Quản trị viên chỉnh sửa thông tin chương trình khuyến mãi đã tạo khi cần thay đổi điều kiện hoặc mức giảm giá.

### UC7_3: Xóa khuyến mãi
**Mô tả**: Quản trị viên xóa chương trình khuyến mãi khi không còn hiệu lực hoặc không cần thiết.

### UC7_4: Xem danh sách khuyến mãi
**Mô tả**: Quản trị viên xem danh sách tất cả chương trình khuyến mãi với trạng thái hoạt động và hiệu lực.

### UC7_5: Quản lý mã giảm giá
**Mô tả**: Quản trị viên tạo và quản lý các mã giảm giá để khách hàng có thể sử dụng khi mua hàng.

### UC7_6: Theo dõi hiệu quả khuyến mãi
**Mô tả**: Quản trị viên theo dõi và đánh giá hiệu quả của các chương trình khuyến mãi thông qua số lượng sử dụng và doanh thu.

### UC7_7: Gợi ý khuyến mãi
**Mô tả**: Hệ thống AI gợi ý các chương trình khuyến mãi phù hợp dựa trên tình hình kinh doanh và xu hướng bán hàng.

### UC7_8: Áp dụng điều kiện khuyến mãi
**Mô tả**: Quản trị viên thiết lập các điều kiện áp dụng khuyến mãi như giá trị đơn hàng tối thiểu, sản phẩm áp dụng.

### UC7_9: Quản lý lượt sử dụng
**Mô tả**: Quản trị viên theo dõi và giới hạn số lượt sử dụng của từng mã khuyến mãi để kiểm soát chi phí.

### UC7_10: Tạo mã khuyến mãi tự động
**Mô tả**: Hệ thống tự động tạo mã khuyến mãi theo quy tắc đã thiết lập để tiết kiệm thời gian quản lý.

## Quan hệ giữa các Use Case
- **Include**: UC7 bao gồm tất cả các UC7_1 đến UC7_10

## Yêu cầu phi chức năng
- Thời gian tạo chương trình khuyến mãi < 3 giây
- Hỗ trợ tối đa 100 chương trình khuyến mãi đồng thời
- Tự động kiểm tra điều kiện áp dụng khuyến mãi
- Tự động backup dữ liệu khuyến mãi hàng ngày





