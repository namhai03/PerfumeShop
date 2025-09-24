# Mô tả Use Case - Quản lý Khách hàng

## Tổng quan
Module Quản lý Khách hàng cho phép quản trị viên quản lý thông tin khách hàng, theo dõi lịch sử mua hàng và phân nhóm khách hàng để phục vụ tốt hơn.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC3: Quản lý khách hàng
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý khách hàng tổng thể bao gồm thêm, sửa, xóa và theo dõi thông tin khách hàng.

### UC3_1: Thêm khách hàng
**Mô tả**: Quản trị viên thêm thông tin khách hàng mới vào hệ thống bao gồm tên, địa chỉ, số điện thoại và email.

### UC3_2: Sửa thông tin khách hàng
**Mô tả**: Quản trị viên chỉnh sửa và cập nhật thông tin cá nhân của khách hàng hiện có trong hệ thống.

### UC3_3: Xóa khách hàng
**Mô tả**: Quản trị viên xóa thông tin khách hàng khỏi hệ thống khi không còn cần thiết hoặc khách hàng yêu cầu.

### UC3_4: Xem danh sách khách hàng
**Mô tả**: Quản trị viên xem danh sách tất cả khách hàng với khả năng lọc và sắp xếp theo tiêu chí khác nhau.

### UC3_5: Tìm kiếm khách hàng
**Mô tả**: Quản trị viên tìm kiếm khách hàng theo tên, số điện thoại, email hoặc các tiêu chí khác.

### UC3_6: Xem lịch sử mua hàng
**Mô tả**: Quản trị viên xem chi tiết lịch sử mua hàng của từng khách hàng để hiểu sở thích và hành vi mua sắm.

### UC3_7: Phân nhóm khách hàng
**Mô tả**: Quản trị viên phân loại khách hàng theo các tiêu chí như giá trị đơn hàng, tần suất mua để phục vụ tốt hơn.

### UC3_8: Thêm ghi chú khách hàng
**Mô tả**: Quản trị viên thêm ghi chú cá nhân về khách hàng để lưu trữ thông tin quan trọng hoặc đặc biệt.

### UC3_9: Cập nhật thông tin liên hệ
**Mô tả**: Quản trị viên cập nhật thông tin liên hệ của khách hàng như địa chỉ, số điện thoại mới.

### UC3_10: Theo dõi giá trị khách hàng
**Mô tả**: Quản trị viên theo dõi và đánh giá giá trị khách hàng dựa trên tổng giá trị đơn hàng và tần suất mua.

## Quan hệ giữa các Use Case
- **Include**: UC3 bao gồm tất cả các UC3_1 đến UC3_10

## Yêu cầu phi chức năng
- Thời gian tải danh sách khách hàng < 2 giây
- Tìm kiếm khách hàng phải có kết quả trong < 1 giây
- Hỗ trợ lưu trữ tối đa 10,000 khách hàng
- Tự động backup dữ liệu khách hàng hàng ngày

