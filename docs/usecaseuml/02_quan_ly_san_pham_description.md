# Mô tả Use Case - Quản lý Sản phẩm

## Tổng quan
Module Quản lý Sản phẩm cho phép quản trị viên quản lý toàn bộ thông tin sản phẩm nước hoa trong hệ thống.

## Actor
- **Quản trị viên**: Người sử dụng duy nhất của hệ thống

## Use Cases

### UC2: Quản lý sản phẩm
**Mô tả**: Quản trị viên thực hiện các thao tác quản lý sản phẩm tổng thể
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên truy cập module quản lý sản phẩm
2. Chọn thao tác cần thực hiện
3. Thực hiện thao tác tương ứng
4. Xác nhận kết quả

**Hậu điều kiện**: Thao tác quản lý sản phẩm đã được thực hiện

### UC2_1: Thêm sản phẩm
**Mô tả**: Quản trị viên thêm sản phẩm nước hoa mới vào hệ thống
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên chọn "Thêm sản phẩm"
2. Nhập thông tin sản phẩm:
   - Tên sản phẩm
   - Mô tả
   - Thương hiệu
   - Danh mục
   - Giá bán
3. Upload hình ảnh sản phẩm
4. Chọn trạng thái (kích hoạt/ẩn)
5. Lưu thông tin sản phẩm

**Luồng phụ**:
- 2a. Thông tin bắt buộc chưa đầy đủ
  - 2a.1. Hiển thị thông báo lỗi
  - 2a.2. Yêu cầu nhập đầy đủ thông tin
- 3a. File hình ảnh không hợp lệ
  - 3a.1. Hiển thị thông báo lỗi
  - 3a.2. Yêu cầu chọn file khác

**Hậu điều kiện**: Sản phẩm mới đã được thêm vào hệ thống

### UC2_2: Sửa sản phẩm
**Mô tả**: Quản trị viên chỉnh sửa thông tin sản phẩm hiện có
**Tiền điều kiện**: Sản phẩm đã tồn tại trong hệ thống
**Luồng chính**:
1. Quản trị viên tìm kiếm sản phẩm cần sửa
2. Chọn "Sửa sản phẩm"
3. Chỉnh sửa thông tin cần thiết
4. Cập nhật hình ảnh (nếu cần)
5. Lưu thay đổi

**Luồng phụ**:
- 1a. Không tìm thấy sản phẩm
  - 1a.1. Hiển thị thông báo không tìm thấy
  - 1a.2. Yêu cầu tìm kiếm lại

**Hậu điều kiện**: Thông tin sản phẩm đã được cập nhật

### UC2_3: Xóa sản phẩm
**Mô tả**: Quản trị viên xóa sản phẩm khỏi hệ thống
**Tiền điều kiện**: Sản phẩm không có đơn hàng liên quan
**Luồng chính**:
1. Quản trị viên tìm kiếm sản phẩm cần xóa
2. Chọn "Xóa sản phẩm"
3. Hệ thống kiểm tra ràng buộc
4. Xác nhận xóa
5. Thực hiện xóa sản phẩm

**Luồng phụ**:
- 3a. Sản phẩm có đơn hàng liên quan
  - 3a.1. Hiển thị cảnh báo
  - 3a.2. Không cho phép xóa
  - 3a.3. Đề xuất ẩn sản phẩm thay vì xóa

**Hậu điều kiện**: Sản phẩm đã được xóa khỏi hệ thống

### UC2_4: Xem danh sách sản phẩm
**Mô tả**: Quản trị viên xem danh sách tất cả sản phẩm
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên truy cập danh sách sản phẩm
2. Hệ thống hiển thị danh sách với phân trang
3. Quản trị viên có thể lọc theo danh mục
4. Quản trị viên có thể sắp xếp theo tiêu chí

**Hậu điều kiện**: Danh sách sản phẩm được hiển thị

### UC2_5: Tìm kiếm sản phẩm
**Mô tả**: Quản trị viên tìm kiếm sản phẩm theo tiêu chí
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên nhập từ khóa tìm kiếm
2. Chọn tiêu chí tìm kiếm (tên, thương hiệu, danh mục)
3. Hệ thống thực hiện tìm kiếm
4. Hiển thị kết quả tìm kiếm

**Luồng phụ**:
- 4a. Không tìm thấy kết quả
  - 4a.1. Hiển thị thông báo không tìm thấy
  - 4a.2. Đề xuất từ khóa tương tự

**Hậu điều kiện**: Kết quả tìm kiếm được hiển thị

### UC2_6: Quản lý biến thể sản phẩm
**Mô tả**: Quản trị viên quản lý các biến thể của sản phẩm (dung tích, SKU)
**Tiền điều kiện**: Sản phẩm đã tồn tại
**Luồng chính**:
1. Quản trị viên chọn sản phẩm
2. Truy cập phần quản lý biến thể
3. Thêm/sửa/xóa biến thể
4. Thiết lập giá riêng cho từng biến thể
5. Lưu thông tin biến thể

**Hậu điều kiện**: Biến thể sản phẩm đã được quản lý

### UC2_7: Quản lý danh mục
**Mô tả**: Quản trị viên quản lý các danh mục sản phẩm
**Tiền điều kiện**: Quản trị viên đã đăng nhập
**Luồng chính**:
1. Quản trị viên truy cập quản lý danh mục
2. Thêm/sửa/xóa danh mục
3. Thiết lập danh mục cha/con
4. Gán sản phẩm vào danh mục
5. Lưu cấu trúc danh mục

**Hậu điều kiện**: Danh mục sản phẩm đã được quản lý

### UC2_8: Upload hình ảnh
**Mô tả**: Quản trị viên upload hình ảnh cho sản phẩm
**Tiền điều kiện**: Sản phẩm đã tồn tại
**Luồng chính**:
1. Quản trị viên chọn sản phẩm
2. Chọn "Upload hình ảnh"
3. Chọn file hình ảnh từ máy tính
4. Hệ thống kiểm tra định dạng và kích thước
5. Upload và lưu trữ hình ảnh
6. Cập nhật đường dẫn hình ảnh cho sản phẩm

**Luồng phụ**:
- 4a. File không đúng định dạng
  - 4a.1. Hiển thị thông báo lỗi
  - 4a.2. Yêu cầu chọn file khác
- 4b. File quá lớn
  - 4b.1. Hiển thị thông báo lỗi
  - 4b.2. Yêu cầu nén file

**Hậu điều kiện**: Hình ảnh sản phẩm đã được upload

### UC2_9: Kích hoạt/Ẩn sản phẩm
**Mô tả**: Quản trị viên thay đổi trạng thái hiển thị của sản phẩm
**Tiền điều kiện**: Sản phẩm đã tồn tại
**Luồng chính**:
1. Quản trị viên chọn sản phẩm
2. Chọn "Kích hoạt" hoặc "Ẩn"
3. Xác nhận thay đổi trạng thái
4. Hệ thống cập nhật trạng thái

**Hậu điều kiện**: Trạng thái sản phẩm đã được cập nhật

### UC2_10: Nhập giá sản phẩm
**Mô tả**: Quản trị viên nhập và cập nhật giá bán của sản phẩm
**Tiền điều kiện**: Sản phẩm đã tồn tại
**Luồng chính**:
1. Quản trị viên chọn sản phẩm
2. Truy cập phần quản lý giá
3. Nhập giá bán mới
4. Thiết lập giá cho từng biến thể
5. Lưu thông tin giá

**Luồng phụ**:
- 3a. Giá không hợp lệ (âm hoặc quá cao)
  - 3a.1. Hiển thị cảnh báo
  - 3a.2. Yêu cầu nhập lại

**Hậu điều kiện**: Giá sản phẩm đã được cập nhật

## Quan hệ giữa các Use Case
- **Include**: UC2 bao gồm tất cả các UC2_1 đến UC2_10

## Yêu cầu phi chức năng
- Thời gian tải danh sách sản phẩm < 2 giây
- Hỗ trợ upload hình ảnh tối đa 5MB
- Tìm kiếm sản phẩm phải có kết quả trong < 1 giây
- Hỗ trợ tối đa 10 biến thể cho mỗi sản phẩm
- Tự động backup dữ liệu sản phẩm hàng ngày

