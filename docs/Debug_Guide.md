# 🔧 HƯỚNG DẪN DEBUG CHAT AI

## 🚨 **VẤN ĐỀ: KHÔNG GIAO TIẾP ĐƯỢC VỚI LLM**

Nếu bạn không thể giao tiếp với LLM trong OmniAI chat, hãy làm theo các bước sau:

## 🔍 **BƯỚC 1: KIỂM TRA CẤU HÌNH**

### 1.1 Kiểm tra file `.env`:
```env
# Thêm vào file .env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini
```

### 1.2 Kiểm tra API Key:
- Đảm bảo API key hợp lệ
- Kiểm tra quota còn lại
- Test API key tại: https://platform.openai.com/api-keys

## 🧪 **BƯỚC 2: SỬ DỤNG DEBUG PAGE**

Truy cập: `/debug/llm`

Trang này sẽ:
- ✅ Kiểm tra cấu hình LLM
- 🧪 Test LLM với câu hỏi đơn giản
- 📋 Hiển thị thông tin debug

## 🔍 **BƯỚC 3: KIỂM TRA LOGS**

Xem logs trong `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log
```

Tìm các log:
- `OmniAI: Using LLM for message`
- `OmniAI: LLM response generated`
- `OpenAI API Error`
- `LLM Service Error`

## 🛠️ **BƯỚC 4: TEST API TRỰC TIẾP**

### 4.1 Test bằng curl:
```bash
curl -X POST http://localhost:8000/api/ai/test \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"message": "Xin chào"}'
```

### 4.2 Test bằng Postman:
- URL: `POST /api/ai/test`
- Headers: `Content-Type: application/json`
- Body: `{"message": "Xin chào"}`

## 🚨 **CÁC LỖI THƯỜNG GẶP**

### 1. **"LLM chưa được cấu hình"**
**Nguyên nhân:** Chưa thiết lập OPENAI_API_KEY
**Giải pháp:** Thêm API key vào file .env

### 2. **"Không gọi được LLM: 401"**
**Nguyên nhân:** API key không hợp lệ
**Giải pháp:** Kiểm tra và cập nhật API key

### 3. **"Không gọi được LLM: 429"**
**Nguyên nhân:** Quota đã hết
**Giải pháp:** Nạp thêm credit vào OpenAI account

### 4. **"Lỗi kết nối"**
**Nguyên nhân:** Network hoặc server issue
**Giải pháp:** Kiểm tra internet và server

### 5. **"Có lỗi xảy ra khi xử lý câu hỏi"**
**Nguyên nhân:** Exception trong code
**Giải pháp:** Xem logs để debug

## 🔧 **DEBUG COMMANDS**

### Kiểm tra cấu hình:
```bash
php artisan tinker
>>> config('services.openai.api_key')
>>> config('services.openai.model')
```

### Test LLM service:
```bash
php artisan tinker
>>> $llm = app(\App\Services\LLMService::class);
>>> $llm->isConfigured()
>>> $llm->chat('Xin chào')
```

## 📊 **KIỂM TRA PERFORMANCE**

### 1. Response time:
- Bình thường: 2-5 giây
- Chậm: >10 giây
- Timeout: >30 giây

### 2. Token usage:
- Input: ~50-100 tokens
- Output: ~100-200 tokens
- Max: 500 tokens

## 🎯 **TEST CASES**

### Test câu hỏi đơn giản:
- "Xin chào"
- "Bạn có thể giúp gì?"
- "Tôi cần tư vấn"

### Test câu hỏi về sản phẩm:
- "Tìm nước hoa nam"
- "Gợi ý sản phẩm cho nữ"
- "So sánh Chanel và Dior"

### Test tra cứu:
- "Đơn số ABC123"
- "Sdt 0912345678"
- "Tồn thấp < 5"

## 🚀 **GIẢI PHÁP NHANH**

Nếu vẫn không hoạt động:

1. **Restart server:**
```bash
php artisan serve
```

2. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

3. **Check routes:**
```bash
php artisan route:list | grep ai
```

4. **Test endpoint:**
```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "test"}'
```

## 📞 **LIÊN HỆ HỖ TRỢ**

Nếu vẫn gặp vấn đề:
1. Gửi logs từ `storage/logs/laravel.log`
2. Gửi kết quả từ `/debug/llm`
3. Mô tả chi tiết lỗi gặp phải
