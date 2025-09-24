# BÁO CÁO ĐẶC TẢ CHỨC NĂNG - HỆ THỐNG QUẢN LÝ CỬA HÀNG NƯỚC HOA

## TỔNG QUAN DỰ ÁN

**Tên dự án:** PerfumeShop - Hệ thống quản lý cửa hàng nước hoa  
**Công nghệ:** Laravel 10.x, PHP 8.1+, SQLite/MySQL, PlantUML  
**Mục đích:** Quản lý toàn diện hoạt động kinh doanh cửa hàng nước hoa  
**Phạm vi:** Hệ thống quản lý cửa hàng nước hoa với đầy đủ các module từ quản lý sản phẩm, khách hàng, đơn hàng, tồn kho, tài chính đến báo cáo và AI hỗ trợ  
**Đối tượng sử dụng:** Quản trị viên cửa hàng nước hoa  
**Thời gian phát triển:** Đồ án tốt nghiệp  
**Kiến trúc:** MVC (Model-View-Controller) với Laravel Framework

---

## MỤC LỤC

1. [Tổng quan dự án](#tổng-quan-dự-án)
2. [Xác thực và bảo mật](#1-xác-thực-và-bảo-mật)
3. [Quản lý sản phẩm](#2-quản-lý-sản-phẩm)
4. [Quản lý khách hàng](#3-quản-lý-khách-hàng)
5. [Quản lý đơn hàng](#4-quản-lý-đơn-hàng)
6. [Quản lý tồn kho](#5-quản-lý-tồn-kho)
7. [Sổ quỹ thu chi](#6-sổ-quỹ-thu-chi)
8. [Khuyến mãi](#7-khuyến-mãi)
9. [Vận chuyển](#8-vận-chuyển)
10. [Báo cáo thống kê](#9-báo-cáo-thống-kê)
11. [AI Agents hỗ trợ](#10-ai-agents-hỗ-trợ)
12. [Danh sách các biểu đồ tuần tự](#danh-sách-các-biểu-đồ-tuần-tự)
13. [Cấu trúc hệ thống](#cấu-trúc-hệ-thống)
14. [Yêu cầu phi chức năng](#yêu-cầu-phi-chức-năng)
15. [Quy trình phát triển](#quy-trình-phát-triển)
16. [Tính năng nổi bật](#tính-năng-nổi-bật)
17. [Kết luận](#kết-luận)

---

## 1. XÁC THỰC VÀ BẢO MẬT

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Đăng nhập hệ thống |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên đăng nhập vào hệ thống bằng tên đăng nhập và mật khẩu để truy cập các chức năng quản lý |
| **Sự kiện kích hoạt** | Quản trị viên truy cập trang web và cần xác thực |
| **Điều kiện trước** | Hệ thống đang hoạt động, có tài khoản hợp lệ |
| **Điều kiện sau** | Quản trị viên đã đăng nhập thành công và có thể truy cập hệ thống |
| **Luồng sự kiện chính** | 1. Truy cập trang đăng nhập<br>2. Nhập tên đăng nhập và mật khẩu<br>3. Hệ thống xác thực thông tin<br>4. Nếu hợp lệ, chuyển đến trang chủ<br>5. Nếu không hợp lệ, hiển thị thông báo lỗi |
| **Luồng sự kiện thay thế** | 3a. Thông tin đăng nhập không chính xác → Hiển thị thông báo lỗi, cho phép nhập lại<br>3b. Tài khoản bị khóa → Hiển thị thông báo tài khoản bị khóa, yêu cầu liên hệ quản trị |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Đăng xuất |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên đăng xuất khỏi hệ thống để kết thúc phiên làm việc |
| **Sự kiện kích hoạt** | Quản trị viên nhấn nút "Đăng xuất" |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Phiên làm việc đã được kết thúc |
| **Luồng sự kiện chính** | 1. Nhấn nút "Đăng xuất"<br>2. Hệ thống xác nhận hành động<br>3. Hủy phiên làm việc hiện tại<br>4. Chuyển về trang đăng nhập |
| **Luồng sự kiện thay thế** | Không có |

---

## 2. QUẢN LÝ SẢN PHẨM

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Thêm sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên thêm sản phẩm nước hoa mới vào hệ thống với đầy đủ thông tin |
| **Sự kiện kích hoạt** | Quản trị viên chọn "Thêm sản phẩm" |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Sản phẩm mới đã được thêm vào hệ thống |
| **Luồng sự kiện chính** | 1. Chọn "Thêm sản phẩm"<br>2. Nhập thông tin sản phẩm (tên, mô tả, thương hiệu, danh mục, giá bán)<br>3. Upload hình ảnh sản phẩm<br>4. Chọn trạng thái (kích hoạt/ẩn)<br>5. Lưu thông tin sản phẩm |
| **Luồng sự kiện thay thế** | 2a. Thông tin bắt buộc chưa đầy đủ → Hiển thị thông báo lỗi, yêu cầu nhập đầy đủ<br>3a. File hình ảnh không hợp lệ → Hiển thị thông báo lỗi, yêu cầu chọn file khác |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Sửa sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên chỉnh sửa thông tin sản phẩm hiện có trong hệ thống |
| **Sự kiện kích hoạt** | Quản trị viên chọn sản phẩm cần sửa |
| **Điều kiện trước** | Sản phẩm đã tồn tại trong hệ thống |
| **Điều kiện sau** | Thông tin sản phẩm đã được cập nhật |
| **Luồng sự kiện chính** | 1. Tìm kiếm sản phẩm cần sửa<br>2. Chọn "Sửa sản phẩm"<br>3. Chỉnh sửa thông tin cần thiết<br>4. Cập nhật hình ảnh (nếu cần)<br>5. Lưu thay đổi |
| **Luồng sự kiện thay thế** | 1a. Không tìm thấy sản phẩm → Hiển thị thông báo không tìm thấy, yêu cầu tìm kiếm lại |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Xóa sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xóa sản phẩm khỏi hệ thống khi không còn cần thiết |
| **Sự kiện kích hoạt** | Quản trị viên chọn sản phẩm cần xóa |
| **Điều kiện trước** | Sản phẩm không có đơn hàng liên quan |
| **Điều kiện sau** | Sản phẩm đã được xóa khỏi hệ thống |
| **Luồng sự kiện chính** | 1. Tìm kiếm sản phẩm cần xóa<br>2. Chọn "Xóa sản phẩm"<br>3. Hệ thống kiểm tra ràng buộc<br>4. Xác nhận xóa<br>5. Thực hiện xóa sản phẩm |
| **Luồng sự kiện thay thế** | 3a. Sản phẩm có đơn hàng liên quan → Hiển thị cảnh báo, không cho phép xóa, đề xuất ẩn sản phẩm |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Tìm kiếm sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tìm kiếm sản phẩm theo nhiều tiêu chí khác nhau |
| **Sự kiện kích hoạt** | Quản trị viên nhập từ khóa tìm kiếm |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Kết quả tìm kiếm được hiển thị |
| **Luồng sự kiện chính** | 1. Nhập từ khóa tìm kiếm<br>2. Chọn tiêu chí tìm kiếm (tên, thương hiệu, danh mục, SKU, barcode)<br>3. Hệ thống thực hiện tìm kiếm<br>4. Hiển thị kết quả tìm kiếm |
| **Luồng sự kiện thay thế** | 4a. Không tìm thấy kết quả → Hiển thị thông báo không tìm thấy, đề xuất từ khóa tương tự |

---

## 3. QUẢN LÝ KHÁCH HÀNG

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Thêm khách hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên thêm thông tin khách hàng mới vào hệ thống |
| **Sự kiện kích hoạt** | Quản trị viên chọn "Thêm khách hàng" |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Khách hàng mới đã được thêm vào hệ thống |
| **Luồng sự kiện chính** | 1. Chọn "Thêm khách hàng"<br>2. Nhập thông tin khách hàng (tên, số điện thoại, email, địa chỉ)<br>3. Chọn nhóm khách hàng (nếu có)<br>4. Thêm ghi chú (nếu cần)<br>5. Lưu thông tin khách hàng |
| **Luồng sự kiện thay thế** | 2a. Thông tin bắt buộc chưa đầy đủ → Hiển thị thông báo lỗi, yêu cầu nhập đầy đủ |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Xem lịch sử mua hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xem chi tiết lịch sử mua hàng của khách hàng |
| **Sự kiện kích hoạt** | Quản trị viên chọn khách hàng và xem lịch sử |
| **Điều kiện trước** | Khách hàng đã tồn tại trong hệ thống |
| **Điều kiện sau** | Lịch sử mua hàng được hiển thị |
| **Luồng sự kiện chính** | 1. Tìm kiếm khách hàng<br>2. Chọn "Xem lịch sử mua hàng"<br>3. Hệ thống hiển thị danh sách đơn hàng<br>4. Xem chi tiết từng đơn hàng |
| **Luồng sự kiện thay thế** | Không có |

---

## 4. QUẢN LÝ ĐƠN HÀNG

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Tạo đơn hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo đơn hàng mới cho khách hàng với thông tin sản phẩm và giá trị |
| **Sự kiện kích hoạt** | Quản trị viên chọn "Tạo đơn hàng" |
| **Điều kiện trước** | Quản trị viên đã đăng nhập, có sản phẩm trong hệ thống |
| **Điều kiện sau** | Đơn hàng mới đã được tạo thành công |
| **Luồng sự kiện chính** | 1. Chọn "Tạo đơn hàng"<br>2. Nhập thông tin khách hàng<br>3. Thêm sản phẩm vào đơn hàng<br>4. Nhập số lượng và giá<br>5. Áp dụng mã khuyến mãi (nếu có)<br>6. Tính tổng tiền đơn hàng<br>7. Lưu đơn hàng |
| **Luồng sự kiện thay thế** | 3a. Sản phẩm không đủ tồn kho → Hiển thị cảnh báo, yêu cầu điều chỉnh số lượng<br>5a. Mã khuyến mãi không hợp lệ → Hiển thị thông báo lỗi, không áp dụng mã |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Cập nhật trạng thái đơn hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên cập nhật trạng thái đơn hàng theo quy trình xử lý |
| **Sự kiện kích hoạt** | Đơn hàng cần thay đổi trạng thái |
| **Điều kiện trước** | Đơn hàng đã tồn tại trong hệ thống |
| **Điều kiện sau** | Trạng thái đơn hàng đã được cập nhật |
| **Luồng sự kiện chính** | 1. Chọn đơn hàng cần cập nhật<br>2. Chọn trạng thái mới (Đã xác nhận, Đang xử lý, Đang giao, Đã giao, Thất bại, Trả hàng)<br>3. Xác nhận thay đổi<br>4. Hệ thống cập nhật trạng thái và tồn kho |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Hủy đơn hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên hủy đơn hàng khi khách hàng yêu cầu hoặc có lỗi |
| **Sự kiện kích hoạt** | Quản trị viên chọn hủy đơn hàng |
| **Điều kiện trước** | Đơn hàng chưa được giao |
| **Điều kiện sau** | Đơn hàng đã được hủy và tồn kho được hoàn trả |
| **Luồng sự kiện chính** | 1. Chọn đơn hàng cần hủy<br>2. Chọn "Hủy đơn hàng"<br>3. Nhập lý do hủy<br>4. Xác nhận hủy<br>5. Hệ thống hoàn trả tồn kho<br>6. Cập nhật trạng thái đơn hàng |
| **Luồng sự kiện thay thế** | 5a. Đơn hàng đã được giao → Không cho phép hủy, đề xuất tạo đơn trả hàng |

---

## 5. QUẢN LÝ TỒN KHO

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Xem tồn kho |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xem danh sách tồn kho hiện tại của tất cả sản phẩm |
| **Sự kiện kích hoạt** | Quản trị viên truy cập trang quản lý tồn kho |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Danh sách tồn kho được hiển thị |
| **Luồng sự kiện chính** | 1. Truy cập trang quản lý tồn kho<br>2. Hệ thống hiển thị danh sách sản phẩm với số lượng tồn<br>3. Lọc theo danh mục, thương hiệu<br>4. Sắp xếp theo tiêu chí |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Nhập kho |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên nhập hàng mới vào kho và cập nhật tồn kho |
| **Sự kiện kích hoạt** | Có hàng mới nhập về |
| **Điều kiện trước** | Sản phẩm đã tồn tại trong hệ thống |
| **Điều kiện sau** | Tồn kho đã được cập nhật |
| **Luồng sự kiện chính** | 1. Chọn sản phẩm cần nhập<br>2. Nhập số lượng nhập<br>3. Nhập giá nhập<br>4. Nhập nhà cung cấp<br>5. Thêm ghi chú<br>6. Xác nhận nhập kho<br>7. Hệ thống cập nhật tồn kho |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Điều chỉnh tồn kho |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên điều chỉnh tồn kho khi có sai sót hoặc kiểm kê |
| **Sự kiện kích hoạt** | Phát hiện sai sót trong tồn kho |
| **Điều kiện trước** | Sản phẩm đã tồn tại trong hệ thống |
| **Điều kiện sau** | Tồn kho đã được điều chỉnh |
| **Luồng sự kiện chính** | 1. Chọn sản phẩm cần điều chỉnh<br>2. Nhập số lượng thực tế<br>3. Nhập lý do điều chỉnh<br>4. Xác nhận điều chỉnh<br>5. Hệ thống cập nhật tồn kho và ghi nhận lịch sử |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Xem lịch sử tồn kho |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xem lịch sử thay đổi tồn kho của sản phẩm |
| **Sự kiện kích hoạt** | Quản trị viên chọn xem lịch sử tồn kho |
| **Điều kiện trước** | Sản phẩm đã tồn tại trong hệ thống |
| **Điều kiện sau** | Lịch sử tồn kho được hiển thị |
| **Luồng sự kiện chính** | 1. Chọn sản phẩm<br>2. Chọn "Xem lịch sử tồn kho"<br>3. Hệ thống hiển thị danh sách các giao dịch<br>4. Lọc theo thời gian, loại giao dịch<br>5. Xem chi tiết từng giao dịch |
| **Luồng sự kiện thay thế** | Không có |

---

## 6. SỔ QUỸ THU CHI

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Tạo phiếu thu chi |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo phiếu thu hoặc phiếu chi để ghi nhận các khoản tiền vào/ra |
| **Sự kiện kích hoạt** | Có khoản tiền thu vào hoặc chi ra |
| **Điều kiện trước** | Quản trị viên đã đăng nhập, có tài khoản quỹ |
| **Điều kiện sau** | Phiếu thu/chi đã được tạo và số dư tài khoản được cập nhật |
| **Luồng sự kiện chính** | 1. Truy cập trang sổ quỹ<br>2. Chọn "Tạo phiếu thu" hoặc "Tạo phiếu chi"<br>3. Nhập thông tin phiếu (loại, tài khoản, số tiền, ngày, lý do)<br>4. Đính kèm chứng từ (nếu có)<br>5. Lưu phiếu<br>6. Hệ thống cập nhật số dư tài khoản |
| **Luồng sự kiện thay thế** | 3a. Thông tin không hợp lệ → Hiển thị thông báo lỗi, yêu cầu nhập lại<br>5a. Số dư tài khoản không đủ (phiếu chi) → Hiển thị cảnh báo, cho phép tiếp tục hoặc hủy |

---

## 7. KHUYẾN MÃI

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Tạo chương trình khuyến mãi |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo chương trình khuyến mãi mới với các điều kiện và mức giảm giá |
| **Sự kiện kích hoạt** | Cần tạo chương trình khuyến mãi |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Chương trình khuyến mãi đã được tạo |
| **Luồng sự kiện chính** | 1. Chọn "Tạo chương trình khuyến mãi"<br>2. Nhập thông tin chương trình (tên, mô tả)<br>3. Thiết lập điều kiện áp dụng<br>4. Thiết lập mức giảm giá<br>5. Thiết lập thời gian hiệu lực<br>6. Lưu chương trình |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Quản lý mã giảm giá |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo và quản lý các mã giảm giá cho khách hàng |
| **Sự kiện kích hoạt** | Cần tạo mã giảm giá |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Mã giảm giá đã được tạo |
| **Luồng sự kiện chính** | 1. Chọn "Quản lý mã giảm giá"<br>2. Tạo mã giảm giá mới<br>3. Thiết lập điều kiện sử dụng<br>4. Thiết lập số lượt sử dụng<br>5. Lưu mã giảm giá |
| **Luồng sự kiện thay thế** | Không có |

---

## 8. VẬN CHUYỂN

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Tạo vận đơn |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo vận đơn mới cho đơn hàng |
| **Sự kiện kích hoạt** | Đơn hàng cần được vận chuyển |
| **Điều kiện trước** | Đơn hàng đã được xác nhận |
| **Điều kiện sau** | Vận đơn đã được tạo |
| **Luồng sự kiện chính** | 1. Chọn đơn hàng cần vận chuyển<br>2. Chọn "Tạo vận đơn"<br>3. Nhập thông tin vận chuyển<br>4. Chọn phương thức vận chuyển<br>5. Tính phí vận chuyển<br>6. Lưu vận đơn |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Cập nhật trạng thái vận chuyển |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên cập nhật trạng thái vận chuyển theo tiến trình giao hàng |
| **Sự kiện kích hoạt** | Có thay đổi trong quá trình vận chuyển |
| **Điều kiện trước** | Vận đơn đã được tạo |
| **Điều kiện sau** | Trạng thái vận chuyển đã được cập nhật |
| **Luồng sự kiện chính** | 1. Chọn vận đơn<br>2. Chọn trạng thái mới (Đang chuẩn bị, Đang giao, Đã giao, Gặp sự cố)<br>3. Cập nhật thông tin vị trí<br>4. Thêm ghi chú<br>5. Lưu thay đổi |
| **Luồng sự kiện thay thế** | Không có |

---

## 9. BÁO CÁO THỐNG KÊ

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Dashboard tổng quan |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xem dashboard tổng quan với các chỉ số kinh doanh quan trọng |
| **Sự kiện kích hoạt** | Quản trị viên truy cập trang chủ |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Dashboard được hiển thị |
| **Luồng sự kiện chính** | 1. Truy cập trang chủ<br>2. Hệ thống tính toán các chỉ số<br>3. Hiển thị dashboard với biểu đồ<br>4. Cập nhật theo thời gian thực |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Báo cáo doanh thu |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên tạo báo cáo doanh thu theo thời gian |
| **Sự kiện kích hoạt** | Cần xem báo cáo doanh thu |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Báo cáo doanh thu được hiển thị |
| **Luồng sự kiện chính** | 1. Chọn "Báo cáo doanh thu"<br>2. Chọn khoảng thời gian<br>3. Chọn loại báo cáo<br>4. Hệ thống tạo báo cáo<br>5. Hiển thị kết quả |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Xuất báo cáo Excel |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | Quản trị viên xuất các báo cáo ra file Excel để lưu trữ và phân tích |
| **Sự kiện kích hoạt** | Cần xuất báo cáo |
| **Điều kiện trước** | Có báo cáo cần xuất |
| **Điều kiện sau** | File Excel đã được tạo |
| **Luồng sự kiện chính** | 1. Chọn báo cáo cần xuất<br>2. Chọn "Xuất Excel"<br>3. Hệ thống tạo file Excel<br>4. Tải file về máy |
| **Luồng sự kiện thay thế** | Không có |

---

## 10. AI AGENTS HỖ TRỢ

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Gợi ý sản phẩm |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | AI phân tích dữ liệu và đưa ra gợi ý sản phẩm nên nhập thêm hoặc giảm giá |
| **Sự kiện kích hoạt** | Quản trị viên yêu cầu gợi ý |
| **Điều kiện trước** | Có dữ liệu bán hàng |
| **Điều kiện sau** | Gợi ý được hiển thị |
| **Luồng sự kiện chính** | 1. Chọn "Gợi ý sản phẩm"<br>2. AI phân tích dữ liệu bán hàng<br>3. Đưa ra gợi ý sản phẩm<br>4. Hiển thị kết quả với lý do |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Phân tích xu hướng bán hàng |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | AI phân tích xu hướng bán hàng để dự đoán sản phẩm nào sẽ bán chạy |
| **Sự kiện kích hoạt** | Quản trị viên yêu cầu phân tích xu hướng |
| **Điều kiện trước** | Có dữ liệu lịch sử bán hàng |
| **Điều kiện sau** | Báo cáo xu hướng được hiển thị |
| **Luồng sự kiện chính** | 1. Chọn "Phân tích xu hướng"<br>2. AI phân tích dữ liệu lịch sử<br>3. Xác định xu hướng<br>4. Đưa ra dự đoán<br>5. Hiển thị kết quả |
| **Luồng sự kiện thay thế** | Không có |

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên usecase** | Trợ lý tra cứu thông tin |
| **Tác nhân** | Quản trị viên |
| **Mô tả** | AI trợ lý giúp quản trị viên tra cứu nhanh thông tin bằng ngôn ngữ tự nhiên |
| **Sự kiện kích hoạt** | Quản trị viên đặt câu hỏi |
| **Điều kiện trước** | Quản trị viên đã đăng nhập |
| **Điều kiện sau** | Thông tin được trả về |
| **Luồng sự kiện chính** | 1. Nhập câu hỏi bằng ngôn ngữ tự nhiên<br>2. AI phân tích câu hỏi<br>3. Tìm kiếm thông tin liên quan<br>4. Trả về kết quả |
| **Luồng sự kiện thay thế** | 2a. Không hiểu câu hỏi → Yêu cầu làm rõ |

---

## DANH SÁCH CÁC BIỂU ĐỒ TUẦN TỰ

### Thống kê tổng quan
- **Tổng số use case:** 26 (gộp phiếu thu chi thành 1)
- **Tổng số biểu đồ tuần tự:** 26
- **Tỷ lệ coverage:** 100% (mỗi use case có 1 biểu đồ tương ứng)
- **Công nghệ biểu đồ:** PlantUML
- **Định dạng file:** .puml

Hệ thống có **26 biểu đồ tuần tự** tương ứng với **26 use case** được mô tả trong báo cáo:

### 1. Xác thực & Bảo mật (2 biểu đồ)
- `01_dang_nhap_he_thong.puml` - Đăng nhập hệ thống
- `11_dang_xuat.puml` - Đăng xuất

### 2. Quản lý Sản phẩm (4 biểu đồ)
- `02_them_san_pham.puml` - Thêm sản phẩm
- `12_sua_san_pham.puml` - Sửa sản phẩm
- `13_xoa_san_pham.puml` - Xóa sản phẩm
- `14_tim_kiem_san_pham.puml` - Tìm kiếm sản phẩm

### 3. Quản lý Khách hàng (2 biểu đồ)
- `15_them_khach_hang.puml` - Thêm khách hàng
- `16_xem_lich_su_mua_hang.puml` - Xem lịch sử mua hàng

### 4. Quản lý Đơn hàng (3 biểu đồ)
- `03_tao_don_hang.puml` - Tạo đơn hàng
- `05_cap_nhat_trang_thai_don_hang.puml` - Cập nhật trạng thái đơn hàng
- `17_huy_don_hang.puml` - Hủy đơn hàng

### 5. Quản lý Tồn kho (4 biểu đồ)
- `18_xem_ton_kho.puml` - Xem tồn kho
- `04_nhap_kho.puml` - Nhập kho
- `19_dieu_chinh_ton_kho.puml` - Điều chỉnh tồn kho
- `20_xem_lich_su_ton_kho.puml` - Xem lịch sử tồn kho

### 6. Sổ quỹ Thu Chi (1 biểu đồ)
- `06_07_tao_phieu_thu_chi.puml` - Tạo phiếu thu chi

### 7. Khuyến mãi (2 biểu đồ)
- `07_tao_chuong_trinh_khuyen_mai.puml` - Tạo chương trình khuyến mãi
- `22_quan_ly_ma_giam_gia.puml` - Quản lý mã giảm giá

### 8. Vận chuyển (2 biểu đồ)
- `08_tao_van_don.puml` - Tạo vận đơn
- `23_cap_nhat_trang_thai_van_chuyen.puml` - Cập nhật trạng thái vận chuyển

### 9. Báo cáo Thống kê (3 biểu đồ)
- `24_dashboard_tong_quan.puml` - Dashboard tổng quan
- `09_bao_cao_doanh_thu.puml` - Báo cáo doanh thu
- `25_xuat_bao_cao_excel.puml` - Xuất báo cáo Excel

### 10. AI Agents Hỗ trợ (3 biểu đồ)
- `10_ai_goi_y_san_pham.puml` - AI gợi ý sản phẩm
- `26_phan_tich_xu_huong_ban_hang.puml` - Phân tích xu hướng bán hàng
- `27_tro_ly_tra_cuu_thong_tin.puml` - Trợ lý tra cứu thông tin

---

## CẤU TRÚC HỆ THỐNG

### Kiến trúc tổng thể
- **Frontend:** Blade Templates với CSS/JavaScript
- **Backend:** Laravel 10.x Framework
- **Database:** SQLite (development) / MySQL (production)
- **AI Integration:** LLM Service cho phân tích dữ liệu
- **External Services:** N8N cho tự động hóa workflow

### Các Module chính
1. **Authentication Module:** Xác thực và phân quyền
2. **Product Management:** Quản lý sản phẩm và danh mục
3. **Customer Management:** Quản lý khách hàng và nhóm khách hàng
4. **Order Management:** Quản lý đơn hàng và trạng thái
5. **Inventory Management:** Quản lý tồn kho và lịch sử
6. **Financial Management:** Quản lý thu chi và sổ quỹ
7. **Promotion Management:** Quản lý khuyến mãi và mã giảm giá
8. **Shipping Management:** Quản lý vận chuyển và vận đơn
9. **Reporting Module:** Báo cáo và thống kê
10. **AI Assistant:** Trợ lý AI và phân tích dữ liệu

### Database Schema
- **Users:** Thông tin người dùng hệ thống
- **Products:** Thông tin sản phẩm và biến thể
- **Categories:** Danh mục sản phẩm
- **Customers:** Thông tin khách hàng
- **Customer Groups:** Nhóm khách hàng
- **Orders:** Đơn hàng và chi tiết đơn hàng
- **Inventory Movements:** Lịch sử thay đổi tồn kho
- **Cash Vouchers:** Phiếu thu chi
- **Promotions:** Chương trình khuyến mãi
- **Shipments:** Vận đơn và trạng thái vận chuyển

---

## YÊU CẦU PHI CHỨC NĂNG

### 1. HIỆU SUẤT (Performance)

Hệ thống PerfumeShop được thiết kế để đảm bảo hiệu suất cao trong mọi tình huống sử dụng. Về mặt thời gian phản hồi, hệ thống cam kết đáp ứng các chỉ số sau: đăng nhập hệ thống trong vòng 3 giây, tải danh sách sản phẩm trong 2 giây, tìm kiếm sản phẩm trong 1 giây, tạo đơn hàng trong 3 giây, tạo báo cáo trong 5 giây, xuất file Excel trong 10 giây, tải dashboard trong 2 giây, và cập nhật tồn kho trong 1 giây.

Về khả năng xử lý đồng thời, hệ thống được thiết kế để hỗ trợ tối thiểu 50 người dùng đồng thời, xử lý 100 giao dịch mỗi phút, thực hiện 200 truy vấn tìm kiếm mỗi phút, và hỗ trợ upload đồng thời 10 file mỗi phút. Để đạt được hiệu suất này, hệ thống áp dụng các kỹ thuật tối ưu hóa bao gồm sử dụng Redis cache cho dữ liệu thường xuyên truy cập, tối ưu hóa database với index và pagination, sử dụng CDN cho hình ảnh và tài nguyên tĩnh, cũng như nén dữ liệu truyền tải bằng Gzip.

### 2. BẢO MẬT (Security)

Bảo mật là một trong những yếu tố quan trọng nhất của hệ thống PerfumeShop. Hệ thống được thiết kế với nhiều lớp bảo mật để đảm bảo tính toàn vẹn và bảo mật của dữ liệu. Về xác thực và phân quyền, hệ thống yêu cầu mật khẩu có độ dài tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt. Phiên làm việc sẽ tự động hết hạn sau 30 phút không hoạt động để ngăn chặn truy cập trái phép. Để bảo vệ khỏi các cuộc tấn công brute force, hệ thống giới hạn 5 lần thử đăng nhập sai và khóa tài khoản trong 15 phút. Hệ thống sử dụng Role-based Access Control (RBAC) để quản lý phân quyền và JWT token cho các API calls.

Về bảo vệ dữ liệu, hệ thống áp dụng mã hóa AES-256 cho tất cả dữ liệu nhạy cảm và bắt buộc sử dụng HTTPS cho mọi giao tiếp. Để ngăn chặn các lỗ hổng bảo mật phổ biến, hệ thống sử dụng prepared statements để chống SQL injection, validate và escape tất cả input để chống XSS, và sử dụng CSRF tokens để chống CSRF attacks. Hệ thống cũng duy trì một audit trail đầy đủ, ghi log tất cả hoạt động quan trọng bao gồm đăng nhập thành công/thất bại, mọi thay đổi dữ liệu quan trọng, và các lỗi hệ thống chi tiết. Tất cả logs được lưu trữ tối thiểu 1 năm để phục vụ mục đích kiểm toán và điều tra.

### 3. KHẢ NĂNG MỞ RỘNG (Scalability)

Hệ thống PerfumeShop được thiết kế với khả năng mở rộng cao để đáp ứng nhu cầu phát triển của doanh nghiệp. Về khả năng lưu trữ dữ liệu, hệ thống có thể hỗ trợ tối đa 50,000 khách hàng, 10,000 sản phẩm, 100,000 đơn hàng mỗi năm, theo dõi 1,000,000 giao dịch tồn kho, và lưu trữ dữ liệu báo cáo trong 5 năm. Về tính năng, hệ thống cho phép tối đa 100 sản phẩm trong mỗi đơn hàng, 20 biến thể cho mỗi sản phẩm, upload file hình ảnh tối đa 10MB và file Excel tối đa 50MB, hỗ trợ 5 cấp danh mục sản phẩm, và quản lý tối đa 1,000 chương trình khuyến mãi đồng thời.

Về mặt hạ tầng, hệ thống được thiết kế để hỗ trợ horizontal scaling, cho phép thêm server khi cần thiết. Database được thiết kế để hỗ trợ read replicas và sharding để phân tán tải. Hệ thống cũng hỗ trợ cloud storage như AWS S3 và Google Cloud để lưu trữ file, và tích hợp load balancer để đảm bảo high availability và phân tán tải hiệu quả.

### 4. ĐỘ TIN CẬY (Reliability)

Độ tin cậy là yếu tố quan trọng đảm bảo hệ thống hoạt động ổn định và liên tục. Hệ thống PerfumeShop được thiết kế với chiến lược backup và recovery toàn diện, bao gồm backup tự động hàng ngày vào 3:00 AM, backup đa lớp với cả local và cloud storage, khả năng khôi phục dữ liệu trong vòng 4 giờ, và mất dữ liệu tối đa 1 giờ. Hệ thống cũng thực hiện test restore hàng tháng để đảm bảo tính khả dụng của backup.

Về tính sẵn sàng, hệ thống cam kết đảm bảo 99.5% uptime, tương đương tối đa 3.6 giờ downtime mỗi tháng. Mọi hoạt động maintenance sẽ được thông báo trước 24 giờ để người dùng có thể chuẩn bị. Hệ thống được thiết kế với khả năng failover tự động, chuyển sang server backup khi có lỗi, và được giám sát 24/7 với hệ thống alerting để phát hiện và xử lý sự cố kịp thời.

Để đảm bảo tính toàn vẹn dữ liệu, hệ thống sử dụng database transactions cho tất cả các thao tác quan trọng, validate dữ liệu ở cả frontend và backend, áp dụng database constraints để đảm bảo tính toàn vẹn ở mức database, và hỗ trợ rollback khi có lỗi trong quá trình xử lý.

### 5. KHẢ NĂNG SỬ DỤNG (Usability)

Khả năng sử dụng là yếu tố quan trọng đảm bảo người dùng có thể tương tác hiệu quả với hệ thống. Hệ thống PerfumeShop được thiết kế với giao diện responsive, hỗ trợ đầy đủ trên desktop, tablet và mobile, đảm bảo trải nghiệm nhất quán trên mọi thiết bị. Hệ thống tương thích với các trình duyệt phổ biến bao gồm Chrome, Firefox, Safari và Edge ở phiên bản mới nhất. Để đảm bảo tính tiếp cận, hệ thống tuân thủ chuẩn WCAG 2.1 Level AA và hỗ trợ đa ngôn ngữ với tiếng Việt và tiếng Anh.

Về trải nghiệm người dùng, hệ thống được thiết kế để người dùng mới có thể sử dụng các chức năng cơ bản trong vòng 30 phút. Hệ thống tích hợp đầy đủ hệ thống trợ giúp và hướng dẫn chi tiết cho từng chức năng. Thông báo lỗi được thiết kế rõ ràng và cung cấp hướng dẫn cụ thể để khắc phục. Hệ thống cũng hỗ trợ keyboard shortcuts cho các thao tác thường dùng để tăng hiệu quả làm việc.

### 6. KHẢ NĂNG BẢO TRÌ (Maintainability)

Khả năng bảo trì là yếu tố quan trọng đảm bảo hệ thống có thể được phát triển và cập nhật một cách hiệu quả trong tương lai. Về chất lượng code, hệ thống tuân thủ nghiêm ngặt các chuẩn coding bao gồm PSR-12 cho PHP và ESLint cho JavaScript. Tất cả code được comment đầy đủ và chi tiết để đảm bảo tính dễ hiểu và bảo trì. Hệ thống đạt unit test coverage tối thiểu 80% và sử dụng Git với branching strategy để quản lý phiên bản hiệu quả.

Về monitoring và debugging, hệ thống tích hợp các công cụ monitoring như New Relic và DataDog để theo dõi hiệu suất ứng dụng, sử dụng Sentry để track và phân tích lỗi, monitor response time và memory usage để phát hiện sớm các vấn đề hiệu suất. Hệ thống cũng sử dụng centralized logging với ELK stack để phân tích logs một cách hiệu quả và hỗ trợ debugging.

### 7. TÍNH TƯƠNG THÍCH (Compatibility)

Tính tương thích đảm bảo hệ thống có thể hoạt động hiệu quả trong môi trường thực tế và tích hợp với các hệ thống khác. Về hệ điều hành, hệ thống được thiết kế để chạy trên Linux server bao gồm Ubuntu 20.04+ và CentOS 8+, hỗ trợ phát triển trên Windows 10+, macOS 10.15+ và Linux. Hệ thống yêu cầu PHP 8.1+ với các extensions cần thiết và hỗ trợ nhiều loại database bao gồm MySQL 8.0+, PostgreSQL 13+ và SQLite 3.35+.

Về khả năng tích hợp, hệ thống được thiết kế để tích hợp với các dịch vụ thanh toán phổ biến tại Việt Nam như VNPay, MoMo và ZaloPay. Hệ thống cũng tích hợp với các dịch vụ vận chuyển như Giao Hàng Nhanh và Viettel Post để tự động hóa quy trình giao hàng. Về thông báo, hệ thống hỗ trợ gửi email qua SMTP, SendGrid và Mailgun, cũng như tích hợp với các nhà cung cấp SMS Việt Nam để gửi thông báo SMS.

### 8. YÊU CẦU PHÁP LÝ (Compliance)

Tuân thủ các quy định pháp lý là yếu tố quan trọng đảm bảo hệ thống có thể hoạt động hợp pháp và đáng tin cậy. Về bảo vệ dữ liệu cá nhân, hệ thống tuân thủ các quy định GDPR về bảo vệ dữ liệu cá nhân, bao gồm chính sách lưu trữ và xóa dữ liệu rõ ràng, thu thập consent từ người dùng một cách minh bạch, và hỗ trợ quyền "right to be forgotten" để xóa dữ liệu cá nhân khi được yêu cầu.

Về báo cáo và kiểm toán, hệ thống được thiết kế để tạo ra các báo cáo tài chính theo chuẩn Việt Nam, hỗ trợ xuất dữ liệu cho kế toán thuế một cách dễ dàng và chính xác. Hệ thống duy trì audit trail đầy đủ cho tất cả các giao dịch và thay đổi dữ liệu quan trọng, đảm bảo tính minh bạch và khả năng kiểm toán toàn diện.

---

## QUY TRÌNH PHÁT TRIỂN

### Phương pháp luận
- **Phương pháp:** Agile Development với Scrum
- **Công cụ quản lý:** Git version control
- **Testing:** Unit testing với PHPUnit
- **Documentation:** PlantUML cho biểu đồ, Markdown cho tài liệu

### Chu trình phát triển
1. **Planning:** Phân tích yêu cầu và thiết kế hệ thống
2. **Design:** Tạo use case diagrams và sequence diagrams
3. **Development:** Implement các chức năng theo từng module
4. **Testing:** Kiểm thử từng chức năng và tích hợp
5. **Deployment:** Triển khai hệ thống
6. **Maintenance:** Bảo trì và cập nhật

### Công nghệ sử dụng
- **Backend:** Laravel 10.x, PHP 8.1+
- **Frontend:** Blade Templates, CSS3, JavaScript ES6+
- **Database:** SQLite (dev), MySQL (prod)
- **AI/ML:** LLM Service integration
- **External APIs:** N8N workflow automation
- **Documentation:** PlantUML, Markdown

---

## TÍNH NĂNG NỔI BẬT

### 1. Quản lý sản phẩm thông minh
- Hỗ trợ nhiều biến thể sản phẩm (size, màu sắc, hương vị)
- Upload và quản lý hình ảnh sản phẩm
- Import/Export dữ liệu sản phẩm từ Excel/CSV
- Tìm kiếm nâng cao với nhiều bộ lọc

### 2. Quản lý khách hàng toàn diện
- Phân nhóm khách hàng với mức giá ưu đãi
- Theo dõi lịch sử mua hàng chi tiết
- Import/Export danh sách khách hàng
- Thống kê hành vi mua hàng

### 3. Quản lý đơn hàng linh hoạt
- Hỗ trợ nhiều loại đơn hàng (bán hàng, trả hàng, nháp)
- Tự động tính toán giá và áp dụng khuyến mãi
- Theo dõi trạng thái đơn hàng chi tiết
- Tích hợp với hệ thống vận chuyển

### 4. Quản lý tồn kho chính xác
- Theo dõi tồn kho real-time
- Lịch sử thay đổi tồn kho chi tiết
- Cảnh báo tồn kho thấp
- Điều chỉnh tồn kho với nhiều lý do khác nhau

### 5. Tài chính minh bạch
- Sổ quỹ thu chi chi tiết
- Phân loại các khoản thu chi
- Báo cáo tài chính đa dạng
- Xuất báo cáo Excel

### 6. AI hỗ trợ kinh doanh
- Gợi ý sản phẩm dựa trên xu hướng bán hàng
- Phân tích xu hướng thị trường
- Trợ lý tra cứu thông tin bằng ngôn ngữ tự nhiên
- Dự đoán nhu cầu sản phẩm

---

## KẾT LUẬN

Hệ thống PerfumeShop là một giải pháp quản lý cửa hàng nước hoa toàn diện và hiện đại, tích hợp đầy đủ các chức năng từ quản lý sản phẩm, khách hàng, đơn hàng đến tồn kho, tài chính và báo cáo. 

### Điểm mạnh của hệ thống:
- **Tính toàn diện:** Bao phủm tất cả các khía cạnh quản lý cửa hàng
- **Tính hiện đại:** Sử dụng công nghệ Laravel 10.x và AI
- **Tính linh hoạt:** Dễ dàng mở rộng và tùy chỉnh
- **Tính thân thiện:** Giao diện trực quan, dễ sử dụng
- **Tính tích hợp:** Kết nối với các dịch vụ bên ngoài

### Giá trị mang lại:
- **Tối ưu hóa vận hành:** Tự động hóa các quy trình quản lý
- **Nâng cao hiệu quả:** Giảm thời gian xử lý và tăng độ chính xác
- **Hỗ trợ quyết định:** AI cung cấp insights kinh doanh
- **Minh bạch tài chính:** Theo dõi và báo cáo chi tiết
- **Khả năng mở rộng:** Sẵn sàng cho tương lai

Hệ thống được thiết kế theo chuẩn nghiệp vụ, đảm bảo tính nhất quán và dễ sử dụng. Với 26 use case được mô tả chi tiết và 26 biểu đồ tuần tự tương ứng, hệ thống cung cấp tài liệu kỹ thuật đầy đủ cho việc phát triển và bảo trì.
