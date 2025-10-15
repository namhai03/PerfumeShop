## Quy trình nghiệp vụ vận hành cửa hàng PerfumeShop

Tài liệu này mô tả quy trình vận hành chi tiết của cửa hàng, bám sát mô hình dữ liệu, controllers và views trong hệ thống PerfumeShop (Laravel). Mục tiêu là giúp nhân sự vận hành hiểu rõ từng bước nghiệp vụ từ nhập đơn, áp khuyến mại, xuất kho, tạo vận đơn, thu chi quỹ đến báo cáo.

---

### 1) Quản lý sản phẩm và tồn kho

- **Danh mục sản phẩm**: Tạo/sửa/xóa sản phẩm và biến thể (nếu có), cập nhật tên, SKU, barcode, giá bán, trạng thái có thể bán, kênh bán.
- **Xem tồn kho**: Trang tồn kho hiển thị số lượng tồn, bộ lọc theo danh mục/kênh bán/trạng thái có thể bán, tìm kiếm theo SKU/tên/barcode.
- **Điều chỉnh tồn kho (manual adjust)**:
  - Trường hợp sử dụng: kiểm kê, nhập hàng, xuất mẫu, hủy hỏng, chuyển kho nội bộ, điều chỉnh do sai lệch.
  - Thao tác: mở trang chi tiết sản phẩm trong kho → chọn "Cập nhật tồn kho" → nhập loại giao dịch (`import`, `export`, `adjust_increase`, `adjust_decrease`, ...), số lượng, ghi chú.
  - Hệ thống ghi nhận một `InventoryMovement` gồm: loại giao dịch, số lượng thay đổi, tồn trước/sau, thời điểm, mã tham chiếu/ghi chú.
- **Lịch sử kho**: Có thể lọc theo khoảng thời gian, loại giao dịch tăng/giảm, tìm theo ghi chú/mã tham chiếu, xuất Excel lịch sử.

Lưu ý: Khi phát sinh đơn bán/đơn hoàn (phần 2), hệ thống tự động ghi `InventoryMovement` tương ứng và cập nhật `stock` của sản phẩm/biến thể liên quan.

---

### 2) Quy trình bán hàng (Đơn hàng)

- **Khởi tạo đơn**:
  - Vào trang tạo đơn, nhập thông tin khách hàng (tên, SĐT), địa chỉ giao hàng (nếu có), kênh bán (`sales_channel`), phương thức thanh toán, ngày đặt/giao dự kiến, ghi chú.
  - Thêm các dòng hàng: chọn sản phẩm/biến thể, số lượng, đơn giá; hệ thống tính `total_amount` và có thể nhập giảm giá/khuyến mại theo (3).
  - `status` khi khởi tạo thường là `draft` hoặc `confirmed` tùy quy định nội bộ.
  - `type` đơn được suy luận:
    - `sale`: đơn đi (confirmed → processing → shipping → delivered).
    - `draft`: đơn nháp (không ảnh hưởng tồn kho).
    - `return`: đơn hoàn (nhập trả về kho khi xác nhận).
- **Vòng đời đơn hàng (7 trạng thái)** trên `App\Models\Order`:
  - `draft` → `confirmed` → `processing` → `shipping` → `delivered`.
  - Nhánh lỗi/ngoại lệ: `failed` (giao thất bại), `returned` (hoàn hàng thành công).
- **Tự động cập nhật kho khi lưu đơn**:
  - Với `type = sale`: trừ tồn kho, ghi `InventoryMovement` loại `export`.
  - Với `type = return`: cộng tồn kho, ghi `InventoryMovement` loại `return`.
  - Với `type = draft`: không thay đổi tồn kho.
- **Cập nhật KPI khách hàng**: Tổng đơn và tổng chi tiêu của khách hàng được tăng theo `final_amount` khi đơn được tạo.

Thực hành tốt: Chốt đơn ở `confirmed` chỉ khi thông tin khách + mặt hàng + giá đã đúng; tạo vận đơn ở (4) sau khi đơn chuyển `processing`.

---

### 3) Áp dụng khuyến mại

- **Nguồn khuyến mại**: Module `Promotion` định nghĩa chương trình với các thuộc tính: loại (`percent`, `fixed_amount`, `free_shipping`), phạm vi (`order`/`product`), điều kiện (`min_order_amount`, `min_items`), phạm vi áp dụng (sản phẩm, danh mục, nhóm khách, kênh bán), hiệu lực thời gian, ưu tiên (`priority`), cho phép cộng dồn (`is_stackable`).
- **Tính toán áp dụng**: `PromotionService::calculate(cart)` sẽ:
  - Lọc các `Promotion` đang hiệu lực, phù hợp điều kiện giỏ hàng.
  - Nếu có nhiều CTKM không cộng dồn: chọn cái đem lại mức giảm tốt nhất; nếu cho phép cộng dồn: áp theo `priority` từ cao đến thấp.
  - Kết quả gồm tổng giảm giá, giảm phí ship (nếu có), danh sách CTKM áp dụng và ghi chú cách tính.
- **Ghi nhận sử dụng**: Khi chốt đơn, có thể lưu `PromotionUsage` để theo dõi lượt dùng theo đơn/khách, phục vụ báo cáo ROI (mở rộng sau).
- **AI Promotions (tùy chọn)**: Dùng giao diện `Promotions → AI` để sinh đề xuất chiến dịch theo mục tiêu (đẩy tồn, tăng AOV, tái hoạt động, mùa vụ), sinh nội dung marketing, và khởi chạy chiến dịch mới. Áp dụng chiến dịch vào đơn tuân thủ các ràng buộc an toàn (trần giảm giá, thời gian, giới hạn lượt dùng).

Khuyến nghị: Thiết lập mức `max_discount_percent` phù hợp biên lợi nhuận; không bật cộng dồn nếu chưa đánh giá tác động.

---

### 4) Vận chuyển và đồng bộ trạng thái đơn

- **Tạo vận đơn**:
  - Khi đơn ở `processing`, tạo `Shipment` gắn với `order_code` (mã đơn nội bộ).
  - Hệ thống tạo `ShipmentEvent` đầu tiên ghi nhận thời điểm tạo.
- **Vòng đời vận đơn**:
  - `pending_pickup` → `picked_up` → `in_transit` → ( `delivered` | `returning` → `returned` | `failed` → `returned` ) hoặc có thể `cancelled` ở giai đoạn đầu.
  - Mọi lần đổi trạng thái đều ghi `ShipmentEvent` kèm `event_at` và `note`.
- **Ràng buộc chuyển trạng thái**: Không cho chuyển tiếp nếu đã ở trạng thái kết thúc (`delivered`, `returned`, `cancelled`); `failed` chỉ cho chuyển sang `returned`.
- **Đồng bộ đơn hàng**: Khi vận đơn đổi trạng thái, `Order.status` được map tương ứng:
  - `pending_pickup` → `processing`.
  - `picked_up`, `in_transit`, `retry`, `returning` → `shipping`.
  - `delivered` → `delivered`.
  - `failed` → `failed`.
  - `returned` → `returned`.

Lưu ý: Chốt doanh thu ở trạng thái `delivered`. Trường hợp `failed`/`returned` cần quy trình tài chính phù hợp (hoàn tiền/thu thêm phí nếu có).

---

### 5) Thu/chi và quản lý quỹ tiền mặt (Cashbook)

- **Tài khoản quỹ (`CashAccount`)**: Khai báo các tài khoản tiền mặt/ngân hàng, số dư đầu kỳ, trạng thái hoạt động.
- **Phiếu thu/chi/chuyển quỹ (`CashVoucher`)**:
  - Loại chứng từ: `receipt` (phiếu thu), `payment` (phiếu chi), `transfer` (chuyển quỹ nội bộ).
  - Trường thông tin: số chứng từ, số tiền, mô tả, lý do, đối tượng chi/thu (khách, NCC, NV…), tài khoản nguồn/đích, ngày giao dịch, tham chiếu, ghi chú.
  - Trạng thái duyệt: `pending` → `approved` / `cancelled`. Khi duyệt, ghi `approved_by`, `approved_at`.
- **Quy trình nghiệp vụ**:
  1. Lập phiếu (trạng thái `pending`).
  2. Kiểm tra đối chiếu chứng từ đính kèm (hóa đơn, biên nhận, ảnh). 
  3. Trưởng bộ phận duyệt → trạng thái `approved`.
  4. Cập nhật số dư theo quy chế (nếu có tích hợp tự động). Trường hợp `transfer` cập nhật 2 tài khoản nguồn/đích.
  5. Có thể hủy (`cancelled`) nếu phát hiện sai sót trước khi duyệt.
- **Tra cứu & báo cáo**: Lọc theo loại, trạng thái, ngày; tìm theo mã chứng từ/mô tả/đối tượng; xuất danh sách.

Khuyến nghị kiểm soát: Phân quyền người lập/duyệt; bắt buộc đính kèm chứng từ ảnh/pdf với khoản chi lớn.

---

### 6) Báo cáo và phân tích

- **Báo cáo bán hàng**: Doanh thu theo thời gian, kênh bán, danh mục, sản phẩm; tỷ lệ giao thành công.
- **Báo cáo kho**: Lịch sử xuất/nhập/điều chỉnh, tồn cuối kỳ, sản phẩm bán chạy/hàng chậm xoay; hỗ trợ lọc và xuất Excel.
- **Báo cáo khuyến mại**: Lượt sử dụng, tổng giảm trừ, ước tính uplift (khi có `PromotionUsage` đầy đủ).
- **Báo cáo quỹ**: Tổng thu/chi theo loại chứng từ, đối tượng, tài khoản; số dư theo thời gian.
- **AI Reports (nếu bật)**: Agents cung cấp insights nhanh theo truy vấn tự nhiên (KPI ngày/tuần/tháng, cảnh báo tồn thấp, chương trình đang chạy).

---

### 7) Quy trình chuẩn theo ca vận hành

1. **Đầu ca**:
   - Kiểm tra tồn kho các mặt hàng chủ lực; xác nhận số dư quỹ đầu ca.
   - Xem khuyến mại đang hiệu lực; điều chỉnh giá/ưu đãi nếu cần.
2. **Trong ca**:
   - Tiếp nhận đơn từ các kênh; xác minh thông tin khách, địa chỉ, phí ship.
   - Áp khuyến mại theo `PromotionService`; xác nhận số tiền phải thu; chốt đơn `confirmed`/`processing`.
   - Xuất kho khi đóng gói; tạo `Shipment` và giao cho đơn vị vận chuyển.
   - Lập phiếu thu/chi phát sinh (COD, hoàn ứng, chuyển quỹ) ở trạng thái `pending` chờ duyệt.
3. **Cuối ca**:
   - Cập nhật trạng thái vận đơn; đối soát số đơn `delivered`/`failed`/`returned`.
   - Duyệt chứng từ `CashVoucher` hợp lệ; đối chiếu số dư quỹ.
   - Xuất báo cáo ca: doanh thu, đơn hàng, tồn kho thay đổi, thu/chi.

---

### 8) Kiểm soát & rủi ro

- **Giá và chiết khấu**: Áp trần giảm giá theo biên lợi nhuận; không cho phép cộng dồn nếu chưa được duyệt.
- **Kho**: Mọi thay đổi tồn kho phải có `InventoryMovement`; kiểm kê định kỳ đối chiếu số liệu.
- **Vận chuyển**: Tuân thủ ràng buộc chuyển trạng thái vận đơn; chỉ ghi nhận doanh thu ở `delivered`.
- **Quỹ**: Phân quyền lập/duyệt; số tiền lớn bắt buộc 2 lớp duyệt; lưu chứng từ bắt buộc.
- **Dữ liệu**: Ghi log thao tác quan trọng; backup CSDL; phân quyền truy cập báo cáo.

---

### 9) Tích hợp AI trong vận hành (tùy chọn)

- **OmniAI**: Hỏi nhanh về đơn hàng (#mã), khách hàng (SĐT), tồn thấp, chương trình khuyến mại, KPI ngày/tuần/tháng.
- **PromotionAI**: Gợi ý chiến dịch đẩy hàng chậm xoay/tăng AOV/tái hoạt động; sinh nội dung; khởi chạy trong khung an toàn.
- **Embedding Search**: Tìm kiếm thông minh theo ngữ nghĩa trên sản phẩm, đơn hàng, khách hàng, vận đơn, khuyến mại.

---

### 10) Phụ lục: Mapping thực thể và điểm chạm hệ thống

- **Đơn hàng**: `App\Models\Order` (trạng thái, quan hệ `items`, `shipments`).
- **Dòng hàng**: `App\Models\OrderItem` (sản phẩm/biến thể, số lượng, giá, ghi chú).
- **Kho**: `App\Models\InventoryMovement` (ghi nhận mọi thay đổi tồn), `InventoryController` (history/adjust/export).
- **Vận đơn**: `App\Models\Shipment`, `App\Models\ShipmentEvent`, `ShipmentController@updateStatus` (ràng buộc & đồng bộ `Order.status`).
- **Khuyến mại**: `App\Models\Promotion`, `App\Models\PromotionUsage`, `App\Services\PromotionService` (tính toán áp dụng), `PromotionAiController` (AI campaign).
- **Quỹ**: `App\Models\CashAccount`, `App\Models\CashVoucher`, `CashVoucherController` (lập/duyệt/tra cứu), routes `cashbook/*`.
- **Báo cáo**: Views trong `resources/views/*` và các dịch vụ dữ liệu (`DataService`) phục vụ AI và UI báo cáo.

---

Tài liệu này là chuẩn vận hành đề xuất. Khi có thay đổi nghiệp vụ hoặc cập nhật hệ thống, cần cập nhật lại quy trình tương ứng để bảo đảm nhất quán dữ liệu và tuân thủ kiểm soát nội bộ.


