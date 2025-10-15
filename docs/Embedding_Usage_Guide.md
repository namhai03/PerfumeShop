# 🚀 HƯỚNG DẪN SỬ DỤNG EMBEDDING VÀ VECTOR STORE
## PerfumeShop AI Enhancement

---

## 📋 TỔNG QUAN

Hệ thống Embedding và Vector Store đã được tích hợp vào PerfumeShop để nâng cao khả năng tìm kiếm semantic và gợi ý sản phẩm thông minh.

### Tính năng chính:
- **Semantic Search**: Tìm kiếm sản phẩm dựa trên ý nghĩa
- **Similar Products**: Gợi ý sản phẩm tương tự
- **Enhanced AI Context**: Cung cấp context phong phú cho LLM
- **Hybrid Search**: Kết hợp semantic và keyword search

---

## 🔧 THIẾT LẬP

### 1. Cấu hình Environment

Thêm vào file `.env`:
```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4o-mini
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
```

### 2. Chạy Migration

```bash
php artisan migrate
```

### 3. Generate Embeddings

```bash
# Generate embeddings cho tất cả sản phẩm
php artisan ai:generate-embeddings

# Generate cho sản phẩm cụ thể
php artisan ai:generate-embeddings --product-id=1

# Regenerate embeddings (force)
php artisan ai:generate-embeddings --force

# Batch size nhỏ hơn để tránh rate limit
php artisan ai:generate-embeddings --batch-size=3
```

---

## 🎯 SỬ DỤNG API

### 1. Semantic Search

**Endpoint**: `POST /api/semantic/search`

```bash
curl -X POST http://localhost:8000/api/semantic/search \
  -H "Content-Type: application/json" \
  -d '{
    "query": "nước hoa nam mùi gỗ",
    "limit": 5,
    "content_type": "combined",
    "min_similarity": 0.3
  }'
```

**Response**:
```json
{
  "success": true,
  "query": "nước hoa nam mùi gỗ",
  "results": [
    {
      "product_id": 1,
      "name": "Chanel Bleu de Chanel",
      "brand": "Chanel",
      "selling_price": "2500000",
      "similarity": 0.8542,
      "match_type": "semantic"
    }
  ],
  "total_found": 5,
  "avg_similarity": 0.7234,
  "search_type": "semantic"
}
```

### 2. Find Similar Products

**Endpoint**: `GET /api/semantic/products/{productId}/similar`

```bash
curl -X GET http://localhost:8000/api/semantic/products/1/similar?limit=4
```

### 3. Hybrid Search

**Endpoint**: `POST /api/semantic/hybrid-search`

```bash
curl -X POST http://localhost:8000/api/semantic/hybrid-search \
  -H "Content-Type: application/json" \
  -d '{
    "query": "nước hoa nữ ngọt ngào",
    "limit": 5
  }'
```

### 4. Search Suggestions

**Endpoint**: `GET /api/semantic/suggestions`

```bash
curl -X GET "http://localhost:8000/api/semantic/suggestions?query=nước hoa&limit=5"
```

### 5. Coverage Statistics

**Endpoint**: `GET /api/semantic/coverage-stats`

```bash
curl -X GET http://localhost:8000/api/semantic/coverage-stats
```

---

## 🔍 TÍCH HỢP VỚI OMNIAI

### Cải thiện Chat Experience

OmniAI đã được nâng cấp để sử dụng semantic search:

**Trước**:
```
User: "tìm nước hoa nam mùi gỗ"
AI: Chỉ tìm sản phẩm có từ "gỗ" trong tên/mô tả
```

**Sau**:
```
User: "tìm nước hoa nam mùi gỗ"
AI: Tìm sản phẩm có woody notes, sandalwood, cedar, oak...
```

### API Chat với Semantic Context

```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "gợi ý nước hoa nam phù hợp cho mùa hè",
    "context": {}
  }'
```

---

## 📊 MONITORING VÀ ANALYTICS

### 1. Performance Analysis

**Endpoint**: `POST /api/semantic/analyze-performance`

```bash
curl -X POST http://localhost:8000/api/semantic/analyze-performance \
  -H "Content-Type: application/json" \
  -d '{
    "query": "nước hoa nữ sang trọng"
  }'
```

**Response**:
```json
{
  "success": true,
  "analysis": {
    "query": "nước hoa nữ sang trọng",
    "response_time_ms": 245.67,
    "semantic_results_count": 8,
    "keyword_results_count": 5,
    "avg_semantic_similarity": 0.7234,
    "has_embeddings": true
  }
}
```

### 2. Coverage Statistics

```json
{
  "success": true,
  "stats": {
    "total_active_products": 150,
    "products_with_embeddings": 120,
    "coverage_percentage": 80.0,
    "embedding_types": {
      "name": 120,
      "description": 115,
      "notes": 98,
      "combined": 120
    },
    "total_embeddings": 453
  }
}
```

---

## 🛠️ QUẢN LÝ EMBEDDINGS

### 1. Generate Embeddings cho Sản phẩm Mới

```php
// Trong ProductController hoặc khi tạo sản phẩm mới
use App\Services\EmbeddingService;

public function store(Request $request)
{
    $product = Product::create($request->validated());
    
    // Generate embeddings
    $embeddingService = app(EmbeddingService::class);
    $embeddingService->generateProductEmbeddings($product);
    
    return response()->json(['success' => true, 'product' => $product]);
}
```

### 2. Update Embeddings khi Sản phẩm Thay đổi

```php
public function update(Request $request, Product $product)
{
    $product->update($request->validated());
    
    // Regenerate embeddings
    $embeddingService = app(EmbeddingService::class);
    $embeddingService->generateProductEmbeddings($product);
    
    return response()->json(['success' => true, 'product' => $product]);
}
```

### 3. Batch Processing

```php
// Process multiple products
$embeddingService = app(EmbeddingService::class);
$productIds = [1, 2, 3, 4, 5];
$embeddingService->generateBatchEmbeddings($productIds, 3);
```

---

## 🚨 TROUBLESHOOTING

### 1. Lỗi thường gặp

**"Embedding service not configured"**
- Kiểm tra OPENAI_API_KEY trong .env
- Đảm bảo API key hợp lệ và có credit

**"Semantic search failed"**
- Kiểm tra embeddings đã được generate chưa
- Kiểm tra database connection
- Xem logs trong storage/logs/laravel.log

**"No embeddings found"**
- Chạy command generate embeddings
- Kiểm tra product có active không

### 2. Debug Commands

```bash
# Kiểm tra embedding stats
php artisan ai:generate-embeddings --product-id=1

# Test API
curl -X GET http://localhost:8000/api/semantic/coverage-stats

# Check logs
tail -f storage/logs/laravel.log
```

### 3. Performance Issues

**Slow Search**:
- Kiểm tra database indexes
- Giảm limit trong search
- Cache embeddings nếu cần

**Rate Limiting**:
- Giảm batch size
- Thêm delay giữa các requests
- Sử dụng queue cho batch processing

---

## 📈 BEST PRACTICES

### 1. Content Strategy

**Tối ưu nội dung cho embedding**:
- Mô tả sản phẩm chi tiết và đầy đủ
- Sử dụng từ khóa fragrance phong phú
- Bao gồm cả tiếng Việt và tiếng Anh

**Ví dụ tốt**:
```
"Chanel No.5 EDP - Nước hoa nữ kinh điển với hương hoa hồng và ylang-ylang, 
phù hợp cho phụ nữ sang trọng, sử dụng quanh năm"
```

### 2. Search Optimization

**Query Optimization**:
- Sử dụng từ khóa cụ thể
- Kết hợp semantic và keyword search
- Filter theo similarity threshold

**Response Optimization**:
- Cache kết quả search phổ biến
- Pagination cho large results
- Include relevance scores

### 3. Monitoring

**Metrics to Track**:
- Search response time
- Embedding coverage percentage
- User click-through rates
- API usage costs

**Logging**:
```php
Log::info('Semantic Search', [
    'query' => $query,
    'results_count' => $results->count(),
    'avg_similarity' => $results->avg('similarity'),
    'response_time' => $responseTime
]);
```

---

## 🔮 ROADMAP

### Phase 1: Foundation ✅
- [x] Database schema
- [x] Embedding service
- [x] Vector search service
- [x] API endpoints

### Phase 2: Integration ✅
- [x] OmniAI integration
- [x] Semantic search in chat
- [x] Command line tools

### Phase 3: Enhancement (Future)
- [ ] Real-time embedding updates
- [ ] Advanced similarity algorithms
- [ ] Multi-language support
- [ ] A/B testing framework

### Phase 4: Production (Future)
- [ ] Performance optimization
- [ ] Caching strategies
- [ ] Monitoring dashboard
- [ ] Cost optimization

---

## 💡 TIPS VÀ TRICKS

### 1. Tối ưu Embedding Quality

```php
// Format content tốt cho embedding
private function formatCombinedContent(Product $product): string
{
    $content = [];
    $content[] = $product->name;
    $content[] = "Brand: " . $product->brand;
    $content[] = "Gender: " . $product->gender;
    $content[] = "Style: " . $product->style;
    $content[] = "Season: " . $product->season;
    
    // Thêm fragrance notes
    if ($product->top_notes) {
        $content[] = "Top notes: " . $product->top_notes;
    }
    
    return implode('. ', $content);
}
```

### 2. Smart Fallback Strategy

```php
// Luôn có fallback cho semantic search
public function searchProductsWithSemantic(string $query): array
{
    try {
        $semanticResults = $this->vectorSearchService->searchSimilarProducts($query, 5);
        if ($semanticResults->isNotEmpty()) {
            return $semanticResults->toArray();
        }
    } catch (\Exception $e) {
        Log::warning('Semantic search failed, using keyword search');
    }
    
    return $this->searchProductsByKeywords($query);
}
```

### 3. Cost Management

```php
// Batch processing để tối ưu cost
public function generateBatchEmbeddings(array $productIds, int $batchSize = 5): void
{
    $products = Product::whereIn('id', $productIds)->get();
    
    $products->chunk($batchSize)->each(function ($chunk) {
        foreach ($chunk as $product) {
            $this->generateProductEmbeddings($product);
            usleep(100000); // 100ms delay
        }
    });
}
```

---

**Tài liệu được tạo bởi**: AI Assistant  
**Ngày cập nhật**: $(date)  
**Phiên bản**: 1.0  
**Dự án**: PerfumeShop - Enhanced AI System





