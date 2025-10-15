## Luồng hệ thống chi tiết: Khuyến mại AI (AI Promotions)

### Mục tiêu
- Tự động gợi ý chiến dịch khuyến mại dựa trên dữ liệu sản phẩm, tồn kho, doanh thu và hành vi khách hàng.
- Hỗ trợ sinh nội dung chiến dịch (tên, mô tả, CTA) bằng AI.
- Cho phép khởi chạy chiến dịch trực tiếp, có kiểm soát rủi ro và ngân sách.

### Thành phần chính
- Controller: `App\Http\Controllers\PromotionAiController`
  - `index()`: Trang UI.
  - `suggest()`: Sinh đề xuất chiến dịch dựa vào dữ liệu.
  - `generateCopy()`: Sinh nội dung chiến dịch bằng `LLMService`.
  - `launch()`: Tạo bản ghi `Promotion` từ đề xuất đã chọn.
- Services sử dụng:
  - `DataService`: Gom dữ liệu thực tế (sản phẩm, đơn hàng, tồn kho, khách hàng).
  - `PromotionService`: Chuẩn hóa rule khuyến mại, tính toán.
  - `LLMService`: Sinh nội dung AI (copy name/description/CTA).

### Dữ liệu đầu vào (UI → API)
- Mục tiêu: `objective` ∈ {`push_stock`, `increase_aov`, `reactivation`, `seasonal`}
- Thời gian: `start_at`, `end_at`
- Ngân sách/giới hạn: `budget_cap`, `usage_limit`, `usage_limit_per_customer`
- Ràng buộc: `max_discount_percent`, `min_order_amount`, `sales_channel`, `customer_group_id`
- Phạm vi: theo `category_ids`, `product_ids` (tùy chọn)

### Logic gợi ý (heuristic an toàn)
1) Lấy top SKU tồn cao/slow-moving: `stock_days_of_cover` cao hoặc `sales_velocity` thấp.
2) Lấy top SKU doanh thu cao nhưng biên lợi nhuận tốt (nếu có margin): ưu đãi vừa phải để tăng sản lượng.
3) AOV uplift: đặt khuyến mại ngưỡng `min_order_amount` ≈ p75 AOV để kéo AOV lên.
4) Reactivation: khách rời rạc > N ngày → mã cá nhân hóa (nếu có kênh email/SMS sau này).
5) Ràng buộc trần ưu đãi: `discount_percent ≤ max_discount_percent` và không làm âm biên lợi nhuận ước tính.

Kết quả đề xuất gồm:
- `campaign_id` (tạm thời sinh UUID client-side hoặc server-side)
- `name_suggestion`, `objective`, `type` (percent/fixed_amount/free_shipping/buy_x_get_y)
- `discount_value`, `min_order_amount`, `scope` (order/product)
- `applicable_product_ids` / `applicable_category_ids`
- Ước tính: `predicted_uplift_revenue`, `predicted_usage`, `risk_score`
- Gợi ý copy (nếu người dùng yêu cầu sinh): `title`, `subtitle`, `cta`, `long_description`

### Sinh nội dung bằng AI
- Input: metadata đề xuất + vài số liệu thực tế từ `DataService`.
- Prompt: role system của `LLMService` + ràng buộc độ dài và giọng văn (ngắn gọn, rõ ràng, thân thiện, không hứa hẹn quá mức).
- Output: name/description/CTA cho các kênh (onsite, email/SMS). Lưu trong payload để người dùng chỉnh sửa trước khi khởi chạy.

### Khởi chạy chiến dịch
- Tạo `Promotion`:
  - `code`: mã tự sinh (ví dụ: `AI{Ymd}{rand}`)
  - `name`, `description`, `type`, `scope`, `discount_value`, `max_discount_amount` (nếu có)
  - `min_order_amount`, `min_items`
  - `applicable_product_ids`, `applicable_category_ids`, `applicable_sales_channels`
  - `priority`, `start_at`, `end_at`, `is_active`, `usage_limit`, `usage_limit_per_customer`
- Chính sách an toàn:
  - Giới hạn thời gian, giới hạn sử dụng.
  - Không stack trừ khi cho phép.
  - Có thể dừng sớm (manually) nếu KPI xấu.

### Báo cáo/KPI (mở rộng về sau)
- Theo dõi `PromotionUsage`: lượt dùng, tổng giảm, ROI ước tính.
- Dashboard chiến dịch: CR, AOV, uplift so với baseline.
- A/B Testing: tạo 2-3 biến thể copy/ưu đãi trong khung an toàn, tự động promote winner.

### Luồng UI
1) Màn hình `Promotions → AI` (`GET /promotions/ai`).
2) Người dùng nhập mục tiêu và ràng buộc → bấm “Gợi ý chiến dịch”.
3) Server trả danh sách đề xuất + ước tính KPI.
4) Người dùng chọn 1 đề xuất → bấm “Sinh nội dung” để AI tạo copy.
5) Người dùng chỉnh sửa copy, thời gian, giới hạn → bấm “Khởi chạy”.
6) Hệ thống tạo `Promotion` và chuyển hướng tới trang chi tiết `promotions.show`.

### API Contract (tối giản)
- `POST /api/promotions/ai/suggest`
  - Body: như phần Dữ liệu đầu vào.
  - Response: `{ success: true, suggestions: Suggestion[] }`
- `POST /api/promotions/ai/generate-copy`
  - Body: `{ suggestion: Suggestion, tone?: string }`
  - Response: `{ success: true, copy: { title, subtitle, cta, long_description } }`
- `POST /api/promotions/ai/launch`
  - Body: `{ suggestion: Suggestion, copy, settings }`
  - Response: `{ success: true, promotion_id, redirect_url }`

### Rủi ro và kiểm soát
- Trần ưu đãi theo biên lợi nhuận (nếu có), mặc định ≤ 25%.
- Giới hạn sử dụng theo chiến dịch/khách hàng.
- Không cho phép stack trừ khi explicit.
- Giới hạn thời gian và auto-stop khi cần (về sau).

### Triển khai
- Sprint 1: trang UI, gợi ý heuristic, sinh copy, tạo `Promotion`.
- Sprint 2: báo cáo chiến dịch, A/B test cơ bản.
- Sprint 3: tối ưu heuristic với dữ liệu học được.


