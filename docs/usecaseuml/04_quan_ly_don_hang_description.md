# Mô tả Use Case - Quản lý Đơn hàng

## Tổng quan
Module Quản lý Đơn hàng cho phép quản trị viên tạo, theo dõi và xử lý đơn hàng của khách hàng từ khi tạo đến khi hoàn thành.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC4: Quản lý đơn hàng
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý đơn hàng tổng thể bao gồm tạo, sửa, xóa và theo dõi trạng thái đơn hàng.

### UC4_1: Tạo đơn hàng
**Mô tả**: Quản trị viên tạo đơn hàng mới cho khách hàng với thông tin sản phẩm, số lượng và giá trị đơn hàng.

### UC4_2: Thêm sản phẩm vào đơn
**Mô tả**: Quản trị viên thêm các sản phẩm và số lượng tương ứng vào đơn hàng hiện có.

### UC4_3: Sửa đơn hàng
**Mô tả**: Quản trị viên chỉnh sửa thông tin đơn hàng như sản phẩm, số lượng hoặc thông tin khách hàng.

### UC4_4: Xóa đơn hàng
**Mô tả**: Quản trị viên xóa đơn hàng khỏi hệ thống khi đơn hàng bị hủy hoặc không hợp lệ.

### UC4_5: Xem danh sách đơn hàng
**Mô tả**: Quản trị viên xem danh sách tất cả đơn hàng với khả năng lọc theo trạng thái và thời gian.

### UC4_6: Tìm kiếm đơn hàng
**Mô tả**: Quản trị viên tìm kiếm đơn hàng theo mã đơn hàng, tên khách hàng hoặc các tiêu chí khác.

### UC4_7: Cập nhật trạng thái đơn
**Mô tả**: Quản trị viên cập nhật trạng thái đơn hàng như đang xử lý, đã giao, hoàn thành hoặc hủy.

### UC4_8: In hóa đơn
**Mô tả**: Quản trị viên in hóa đơn cho đơn hàng với đầy đủ thông tin sản phẩm, giá cả và thông tin khách hàng.

### UC4_9: Áp dụng mã khuyến mãi
**Mô tả**: Quản trị viên áp dụng mã giảm giá hoặc chương trình khuyến mãi cho đơn hàng để giảm giá trị thanh toán.

### UC4_10: Tính tổng tiền đơn hàng
**Mô tả**: Hệ thống tự động tính tổng tiền đơn hàng bao gồm giá sản phẩm, thuế và các khoản phí khác.

### UC4_11: Ghi chú đơn hàng
**Mô tả**: Quản trị viên thêm ghi chú đặc biệt cho đơn hàng như yêu cầu giao hàng hoặc lưu ý quan trọng.

### UC4_12: Hủy đơn hàng
**Mô tả**: Quản trị viên hủy đơn hàng khi khách hàng yêu cầu hoặc có lỗi trong quá trình xử lý.

## Quan hệ giữa các Use Case
- **Include**: UC4 bao gồm tất cả các UC4_1 đến UC4_12

## Yêu cầu phi chức năng
- Thời gian tạo đơn hàng < 3 giây
- Tìm kiếm đơn hàng phải có kết quả trong < 1 giây
- Hỗ trợ tối đa 50 sản phẩm mỗi đơn hàng
- Tự động backup dữ liệu đơn hàng hàng ngày

