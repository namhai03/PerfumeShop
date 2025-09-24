# 🤖 HƯỚNG DẪN THIẾT LẬP LLM CHO OMNIAI

## 📋 TỔNG QUAN

OmniAI chat đã được tích hợp với OpenAI LLM để có thể:
- **Chat thông minh** về sản phẩm nước hoa
- **Gợi ý sản phẩm** dựa trên nhu cầu
- **Trả lời câu hỏi** về thông tin cửa hàng
- **Hỗ trợ tra cứu** đơn hàng, khách hàng, tồn kho

## 🔧 THIẾT LẬP

### 1. Cấu hình OpenAI API Key

Thêm vào file `.env`:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini
```

### 2. Kiểm tra cấu hình

Truy cập trang OmniAI: `/omni-ai`

Nếu thấy thông báo "LLM chưa được cấu hình", kiểm tra lại API key.

## 🎯 CÁCH SỬ DỤNG

### 1. Chat với OmniAI

Truy cập `/omni-ai` và thử các câu hỏi:

**Tra cứu thông tin:**
- "đơn số ABC123"
- "sdt 0912345678"
- "tồn thấp < 5"
- "ctkm đang chạy"

**Câu hỏi về sản phẩm:**
- "tìm nước hoa nam"
- "gợi ý sản phẩm cho nữ"
- "nước hoa nào phù hợp cho mùa hè"
- "so sánh Chanel và Dior"

**Thông tin kinh doanh:**
- "kpi hôm nay"
- "báo cáo doanh thu 30d"
- "phân tích khách hàng"

### 2. API Endpoint

```bash
POST /api/ai/chat
{
    "message": "tìm nước hoa nam phù hợp cho mùa hè",
    "context": {}
}
```

## 🔍 TÍNH NĂNG

### ✅ Intent Recognition
- **Order Lookup**: Tra cứu đơn hàng theo mã
- **Customer Lookup**: Tra cứu khách hàng theo SĐT
- **Low Stock**: Kiểm tra sản phẩm tồn thấp
- **Promotions**: Xem CTKM đang chạy
- **KPI Reports**: Báo cáo kinh doanh
- **Product Search**: Tìm kiếm sản phẩm

### ✅ LLM Chat
- Trả lời câu hỏi thông minh
- Gợi ý sản phẩm phù hợp
- So sánh sản phẩm
- Tư vấn mùi hương

### ✅ Product Intelligence
- Tìm kiếm sản phẩm theo từ khóa
- Phân tích đặc điểm sản phẩm
- Gợi ý dựa trên nhu cầu

## 🚨 TROUBLESHOOTING

### Lỗi thường gặp:

1. **"LLM chưa được cấu hình"**
   - Kiểm tra OPENAI_API_KEY trong .env
   - Đảm bảo API key hợp lệ và có credit

2. **"Không gọi được LLM"**
   - Kiểm tra kết nối internet
   - Kiểm tra quota OpenAI API
   - Kiểm tra logs trong `storage/logs/laravel.log`

3. **"Có lỗi xảy ra khi xử lý câu hỏi"**
   - Kiểm tra timeout (30 giây)
   - Kiểm tra max_tokens (500)
   - Kiểm tra model có sẵn không

### Debug:

```bash
# Kiểm tra logs
tail -f storage/logs/laravel.log

# Test API key
curl -H "Authorization: Bearer YOUR_API_KEY" \
     https://api.openai.com/v1/models
```

## 📊 PERFORMANCE

- **Response Time**: ~2-5 giây
- **Max Tokens**: 500 (có thể điều chỉnh)
- **Timeout**: 30 giây
- **Temperature**: 0.3 (cân bằng sáng tạo và chính xác)

## 🔮 CUSTOMIZATION

### Thay đổi System Prompt

Chỉnh sửa trong `app/Services/LLMService.php`:

```php
private function getDefaultSystemPrompt(): string
{
    return "Bạn là OmniAI - trợ lý AI thông minh cho cửa hàng nước hoa PerfumeShop.
    
    // Thêm custom instructions ở đây
    ";
}
```

### Thêm từ khóa sản phẩm

Chỉnh sửa trong `app/Http/Controllers/Api/OmniAIChatController.php`:

```php
private function extractProductKeywords(string $query): array
{
    // Thêm từ khóa mới ở đây
    $brandKeywords = ['chanel', 'dior', 'gucci', 'your_brand'];
}
```

## 💡 BEST PRACTICES

1. **API Key Security**: Không commit API key vào git
2. **Rate Limiting**: Tránh spam requests
3. **Error Handling**: Luôn có fallback response
4. **Logging**: Monitor API usage và errors
5. **Testing**: Test với các loại câu hỏi khác nhau

## 📞 SUPPORT

Nếu gặp vấn đề:
1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Test API key với OpenAI
3. Kiểm tra network connectivity
4. Liên hệ team development
