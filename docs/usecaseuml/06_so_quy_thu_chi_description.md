# Mô tả Use Case - Sổ quỹ Thu Chi

## Tổng quan
Module Sổ quỹ Thu Chi cho phép quản trị viên quản lý tài chính cửa hàng thông qua việc ghi nhận các khoản thu chi và theo dõi dòng tiền.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC6: Sổ quỹ thu chi
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý tài chính tổng thể bao gồm thu, chi và theo dõi dòng tiền.

### UC6_1: Tạo phiếu thu
**Mô tả**: Quản trị viên tạo phiếu thu để ghi nhận các khoản tiền nhận được từ bán hàng hoặc nguồn khác.

### UC6_2: Tạo phiếu chi
**Mô tả**: Quản trị viên tạo phiếu chi để ghi nhận các khoản tiền chi ra cho mua hàng, chi phí vận hành.

### UC6_3: Sửa phiếu thu chi
**Mô tả**: Quản trị viên chỉnh sửa thông tin phiếu thu chi đã tạo khi phát hiện sai sót hoặc cần cập nhật.

### UC6_4: Xóa phiếu thu chi
**Mô tả**: Quản trị viên xóa phiếu thu chi khi phiếu được tạo nhầm hoặc không hợp lệ.

### UC6_5: Xem danh sách phiếu
**Mô tả**: Quản trị viên xem danh sách tất cả phiếu thu chi với khả năng lọc theo thời gian và loại phiếu.

### UC6_6: Đính kèm chứng từ
**Mô tả**: Quản trị viên đính kèm hóa đơn, chứng từ liên quan đến phiếu thu chi để lưu trữ và kiểm tra.

### UC6_7: Đối soát số dư
**Mô tả**: Quản trị viên đối soát số dư quỹ thực tế với số dư trong hệ thống để đảm bảo tính chính xác.

### UC6_8: Báo cáo thu chi
**Mô tả**: Quản trị viên tạo báo cáo thu chi theo ngày, tuần, tháng để phân tích tình hình tài chính.

### UC6_9: Phân loại hạng mục
**Mô tả**: Quản trị viên phân loại các khoản thu chi theo hạng mục như bán hàng, mua hàng, chi phí vận hành.

### UC6_10: Theo dõi dòng tiền
**Mô tả**: Quản trị viên theo dõi dòng tiền vào ra để đánh giá tình hình tài chính và khả năng thanh toán.

### UC6_11: Xuất báo cáo tài chính
**Mô tả**: Quản trị viên xuất báo cáo tài chính chi tiết để trình báo hoặc lưu trữ hồ sơ kế toán.

## Quan hệ giữa các Use Case
- **Include**: UC6 bao gồm tất cả các UC6_1 đến UC6_11

## Yêu cầu phi chức năng
- Thời gian tạo phiếu thu chi < 2 giây
- Hỗ trợ đính kèm file chứng từ tối đa 10MB
- Tự động tính toán số dư quỹ theo thời gian thực
- Tự động backup dữ liệu tài chính hàng ngày





