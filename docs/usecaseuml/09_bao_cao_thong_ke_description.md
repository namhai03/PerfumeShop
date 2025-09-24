# Mô tả Use Case - Báo cáo Thống kê

## Tổng quan
Module Báo cáo Thống kê cung cấp các báo cáo chi tiết về tình hình kinh doanh giúp quản trị viên đưa ra quyết định chính xác.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC9: Báo cáo thống kê
**Mô tả**: Quản trị viên thực hiện các thao tác tạo và xem báo cáo thống kê tổng thể về tình hình kinh doanh của cửa hàng.

### UC9_1: Dashboard tổng quan
**Mô tả**: Quản trị viên xem dashboard tổng quan hiển thị các chỉ số kinh doanh quan trọng như doanh thu, đơn hàng, khách hàng.

### UC9_2: Báo cáo doanh thu
**Mô tả**: Quản trị viên tạo báo cáo doanh thu theo ngày, tuần, tháng để theo dõi xu hướng tăng trưởng kinh doanh.

### UC9_3: Báo cáo tồn kho
**Mô tả**: Quản trị viên tạo báo cáo tồn kho để phân tích tình hình hàng hóa và lập kế hoạch nhập hàng phù hợp.

### UC9_4: Báo cáo khách hàng
**Mô tả**: Quản trị viên tạo báo cáo khách hàng để phân tích hành vi mua sắm và giá trị khách hàng.

### UC9_5: Báo cáo đơn hàng
**Mô tả**: Quản trị viên tạo báo cáo đơn hàng để phân tích xu hướng bán hàng và hiệu quả xử lý đơn hàng.

### UC9_6: Xuất báo cáo Excel
**Mô tả**: Quản trị viên xuất các báo cáo ra file Excel để lưu trữ, chia sẻ hoặc phân tích thêm bằng công cụ khác.

### UC9_7: Tóm tắt kinh doanh
**Mô tả**: Hệ thống AI tạo tóm tắt tình hình kinh doanh với các insights và đề xuất cải thiện hiệu quả.

### UC9_8: Báo cáo theo thời gian
**Mô tả**: Quản trị viên tạo báo cáo theo các khoảng thời gian khác nhau để so sánh và phân tích xu hướng.

### UC9_9: So sánh kỳ trước
**Mô tả**: Quản trị viên so sánh kết quả kinh doanh hiện tại với kỳ trước để đánh giá sự tăng trưởng hoặc suy giảm.

### UC9_10: Phân tích xu hướng
**Mô tả**: Quản trị viên phân tích xu hướng kinh doanh dựa trên dữ liệu lịch sử để dự đoán và lập kế hoạch.

### UC9_11: Báo cáo hiệu quả khuyến mãi
**Mô tả**: Quản trị viên tạo báo cáo đánh giá hiệu quả của các chương trình khuyến mãi để tối ưu hóa chiến lược.

## Quan hệ giữa các Use Case
- **Include**: UC9 bao gồm tất cả các UC9_1 đến UC9_11

## Yêu cầu phi chức năng
- Thời gian tạo báo cáo < 5 giây
- Hỗ trợ xuất báo cáo ra nhiều định dạng (PDF, Excel, CSV)
- Tự động cập nhật dashboard theo thời gian thực
- Tự động backup dữ liệu báo cáo hàng ngày





