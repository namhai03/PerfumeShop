# Mô tả Use Case - Xác thực và Bảo mật

## Tổng quan
Module Xác thực và Bảo mật đảm bảo tính an toàn và kiểm soát truy cập vào hệ thống quản lý cửa hàng nước hoa.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống, có quyền truy cập đầy đủ

## Use Cases

### UC1: Đăng nhập hệ thống
**Mô tả**: Quản trị viên đăng nhập vào hệ thống bằng tên đăng nhập và mật khẩu
**Tiền điều kiện**: Hệ thống đang hoạt động
**Luồng chính**:
1. Quản trị viên truy cập trang đăng nhập
2. Nhập tên đăng nhập và mật khẩu
3. Hệ thống xác thực thông tin
4. Nếu hợp lệ, chuyển đến trang chủ
5. Nếu không hợp lệ, hiển thị thông báo lỗi

**Luồng phụ**:
- 3a. Thông tin đăng nhập không chính xác
  - 3a.1. Hiển thị thông báo lỗi
  - 3a.2. Cho phép nhập lại
- 3b. Tài khoản bị khóa
  - 3b.1. Hiển thị thông báo tài khoản bị khóa
  - 3b.2. Yêu cầu liên hệ quản trị

**Hậu điều kiện**: Quản trị viên đã đăng nhập thành công vào hệ thống

### UC1_1: Đăng xuất
**Mô tả**: Quản trị viên đăng xuất khỏi hệ thống
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên nhấn nút "Đăng xuất"
2. Hệ thống xác nhận hành động
3. Hủy phiên làm việc hiện tại
4. Chuyển về trang đăng nhập

**Hậu điều kiện**: Phiên làm việc đã được kết thúc

### UC1_2: Đổi mật khẩu
**Mô tả**: Quản trị viên thay đổi mật khẩu hiện tại
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên truy cập trang đổi mật khẩu
2. Nhập mật khẩu hiện tại
3. Nhập mật khẩu mới
4. Xác nhận mật khẩu mới
5. Hệ thống kiểm tra tính hợp lệ
6. Cập nhật mật khẩu mới

**Luồng phụ**:
- 2a. Mật khẩu hiện tại không đúng
  - 2a.1. Hiển thị thông báo lỗi
  - 2a.2. Yêu cầu nhập lại
- 5a. Mật khẩu mới không đủ mạnh
  - 5a.1. Hiển thị yêu cầu độ mạnh mật khẩu
  - 5a.2. Yêu cầu nhập lại

**Hậu điều kiện**: Mật khẩu đã được cập nhật thành công

### UC1_3: Quản lý phiên làm việc
**Mô tả**: Hệ thống quản lý phiên làm việc của quản trị viên
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Hệ thống tạo phiên làm việc khi đăng nhập
2. Theo dõi hoạt động của phiên
3. Tự động đăng xuất khi hết thời gian
4. Ghi nhận lịch sử phiên làm việc

**Luồng phụ**:
- 3a. Phiên làm việc bị gián đoạn
  - 3a.1. Lưu trạng thái hiện tại
  - 3a.2. Thông báo phiên hết hạn

**Hậu điều kiện**: Phiên làm việc được quản lý an toàn

### UC1_4: Khóa tài khoản
**Mô tả**: Hệ thống khóa tài khoản khi phát hiện hoạt động bất thường
**Tiền điều kiện**: Phát hiện hoạt động đáng ngờ
**Luồng chính**:
1. Hệ thống phát hiện đăng nhập bất thường
2. Tự động khóa tài khoản tạm thời
3. Gửi thông báo cảnh báo
4. Yêu cầu xác thực bổ sung

**Luồng phụ**:
- 1a. Nhiều lần đăng nhập sai
  - 1a.1. Tăng số lần thử
  - 1a.2. Khóa tài khoản sau 5 lần thử

**Hậu điều kiện**: Tài khoản được bảo vệ khỏi truy cập trái phép

### UC1_5: Reset mật khẩu
**Mô tả**: Khôi phục mật khẩu khi quản trị viên quên
**Tiền điều kiện**: Quản trị viên không thể đăng nhập
**Luồng chính**:
1. Quản trị viên chọn "Quên mật khẩu"
2. Nhập thông tin xác thực
3. Hệ thống gửi mã reset qua email
4. Nhập mã reset và mật khẩu mới
5. Xác nhận và cập nhật mật khẩu

**Luồng phụ**:
- 2a. Thông tin xác thực không đúng
  - 2a.1. Hiển thị thông báo lỗi
  - 2a.2. Yêu cầu nhập lại

**Hậu điều kiện**: Mật khẩu đã được khôi phục thành công

## Quan hệ giữa các Use Case
- **Include**: UC1 bao gồm UC1_1, UC1_2, UC1_3
- **Extend**: UC1_4 và UC1_5 mở rộng từ UC1 khi có tình huống đặc biệt

## Yêu cầu phi chức năng
- Thời gian phản hồi đăng nhập < 3 giây
- Mật khẩu phải có độ dài tối thiểu 8 ký tự
- Phiên làm việc tự động hết hạn sau 30 phút không hoạt động
- Hệ thống ghi log tất cả hoạt động đăng nhập

