# 🚀 KẾ HOẠCH TÍCH HỢP EMBEDDING VÀ VECTOR STORE
## Nâng cấp Hệ thống AI Agents PerfumeShop

---

## 📋 TỔNG QUAN

### Mục tiêu
Tích hợp **Embedding** và **Vector Store** để nâng cao khả năng tìm kiếm semantic và gợi ý sản phẩm thông minh hơn.

### Lợi ích
- **Semantic Search**: Tìm kiếm sản phẩm dựa trên ý nghĩa, không chỉ từ khóa
- **Smart Recommendations**: Gợi ý sản phẩm chính xác hơn dựa trên similarity
- **Enhanced AI Context**: Cung cấp context phong phú hơn cho LLM
- **Scalable Search**: Tìm kiếm hiệu quả với hàng nghìn sản phẩm

---

## 🏗️ KIẾN TRÚC TỔNG THỂ

### 1. Vector Store Options

**Option A: SQLite với Vector Extension (Recommended)**
```sql
-- Sử dụng sqlite-vss extension
CREATE VIRTUAL TABLE product_embeddings USING vss0(
    embedding(1536),  -- OpenAI text-embedding-3-small
    product_id INTEGER
);
```

**Option B: PostgreSQL với pgvector**
```sql
-- Cần migrate từ SQLite sang PostgreSQL
CREATE EXTENSION vector;
CREATE TABLE product_embeddings (
    id SERIAL PRIMARY KEY,
    product_id INTEGER,
    embedding vector(1536),
    content_type VARCHAR(50),
    content_text TEXT
);
```

**Option C: External Vector DB (Chroma, Pinecone)**
- ChromaDB: Open source, dễ tích hợp
- Pinecone: Managed service, production-ready

### 2. Embedding Strategy

**Multi-field Embedding**:
- **Product Name**: "Chanel No.5 EDP"
- **Description**: Mô tả chi tiết sản phẩm
- **Fragrance Notes**: "Top: Aldehydes, Heart: Rose, Base: Sandalwood"
- **Combined**: Tất cả thông tin sản phẩm

---

## 🔧 IMPLEMENTATION PLAN

### Phase 1: Database Schema

**Migration cho ProductEmbedding**:
```php
// Migration: create_product_embeddings_table.php
Schema::create('product_embeddings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('content_type'); // 'name', 'description', 'notes', 'combined'
    $table->text('content_text');
    $table->json('embedding'); // Store as JSON array
    $table->string('model_name')->default('text-embedding-3-small');
    $table->timestamps();
    
    $table->index(['product_id', 'content_type']);
});
```

**Model ProductEmbedding**:
```php
class ProductEmbedding extends Model
{
    protected $fillable = [
        'product_id', 'content_type', 'content_text', 
        'embedding', 'model_name'
    ];
    
    protected $casts = [
        'embedding' => 'array',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

### Phase 2: Embedding Service

**EmbeddingService**:
```php
class EmbeddingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    
    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = config('services.openai.base_url');
        $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
    }
    
    public function generateEmbedding(string $text): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/embeddings', [
                'model' => $this->model,
                'input' => $text,
                'encoding_format' => 'float'
            ]);
            
        return $response->json()['data'][0]['embedding'];
    }
    
    public function generateProductEmbeddings(Product $product): void
    {
        $embeddings = [
            'name' => $this->generateEmbedding($product->name),
            'description' => $this->generateEmbedding($product->description ?? ''),
            'notes' => $this->generateEmbedding($this->formatFragranceNotes($product)),
            'combined' => $this->generateEmbedding($this->formatCombinedContent($product))
        ];
        
        foreach ($embeddings as $type => $embedding) {
            ProductEmbedding::updateOrCreate(
                ['product_id' => $product->id, 'content_type' => $type],
                [
                    'content_text' => $this->getContentByType($product, $type),
                    'embedding' => $embedding,
                    'model_name' => $this->model
                ]
            );
        }
    }
}
```

### Phase 3: Vector Search Service

**VectorSearchService**:
```php
class VectorSearchService
{
    public function searchSimilarProducts(string $query, int $limit = 5): Collection
    {
        $embeddingService = app(EmbeddingService::class);
        $queryEmbedding = $embeddingService->generateEmbedding($query);
        
        // SQLite với vector similarity
        $results = DB::select("
            SELECT 
                pe.product_id,
                pe.content_type,
                pe.content_text,
                p.name,
                p.brand,
                p.selling_price,
                -- Cosine similarity calculation
                (
                    SELECT SUM(a.value * b.value) / 
                    (SQRT(SUM(a.value * a.value)) * SQRT(SUM(b.value * b.value)))
                    FROM json_each(pe.embedding) a
                    CROSS JOIN json_each(?) b
                    WHERE a.key = b.key
                ) as similarity
            FROM product_embeddings pe
            JOIN products p ON pe.product_id = p.id
            WHERE pe.content_type = 'combined'
            ORDER BY similarity DESC
            LIMIT ?
        ", [json_encode($queryEmbedding), $limit]);
        
        return collect($results);
    }
    
    public function findSimilarProducts(Product $product, int $limit = 5): Collection
    {
        $productEmbedding = ProductEmbedding::where('product_id', $product->id)
            ->where('content_type', 'combined')
            ->first();
            
        if (!$productEmbedding) {
            return collect();
        }
        
        return $this->searchByEmbedding($productEmbedding->embedding, $limit, $product->id);
    }
}
```

### Phase 4: Enhanced AI Integration

**Cập nhật OmniAIChatController**:
```php
// Trong OmniAIChatController
private function searchProductsWithEmbedding(string $query): array
{
    $vectorSearch = app(VectorSearchService::class);
    $similarProducts = $vectorSearch->searchSimilarProducts($query, 5);
    
    return $similarProducts->map(function ($product) {
        return [
            'id' => $product->product_id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => $product->selling_price,
            'similarity' => round($product->similarity, 3),
            'match_type' => 'semantic'
        ];
    })->toArray();
}

// Cập nhật searchProducts method
private function searchProducts(string $query): array
{
    // Thử semantic search trước
    $semanticResults = $this->searchProductsWithEmbedding($query);
    if (!empty($semanticResults)) {
        return $semanticResults;
    }
    
    // Fallback to keyword search
    return $this->searchProductsByKeywords($query);
}
```

### Phase 5: Batch Processing

**Command để generate embeddings**:
```php
// Artisan Command: GenerateProductEmbeddings
class GenerateProductEmbeddings extends Command
{
    protected $signature = 'ai:generate-embeddings {--product-id=} {--batch-size=10}';
    
    public function handle()
    {
        $embeddingService = app(EmbeddingService::class);
        
        $query = Product::query();
        if ($this->option('product-id')) {
            $query->where('id', $this->option('product-id'));
        }
        
        $products = $query->get();
        $batchSize = $this->option('batch-size');
        
        $this->info("Generating embeddings for {$products->count()} products...");
        
        $products->chunk($batchSize)->each(function ($chunk) use ($embeddingService) {
            foreach ($chunk as $product) {
                $this->info("Processing: {$product->name}");
                $embeddingService->generateProductEmbeddings($product);
                
                // Rate limiting
                usleep(100000); // 100ms delay
            }
        });
        
        $this->info('Embedding generation completed!');
    }
}
```

---

## 🎯 USE CASES CỤ THỂ

### 1. Semantic Product Search

**Trước (Keyword-based)**:
```
Query: "nước hoa nam mùi gỗ"
Results: Chỉ tìm sản phẩm có từ "gỗ" trong tên/mô tả
```

**Sau (Semantic)**:
```
Query: "nước hoa nam mùi gỗ"
Results: 
- Sandalwood, Cedar, Oak products
- Woody fragrance family
- Masculine scents with woody notes
- Similar aromatic profiles
```

### 2. Smart Recommendations

**Similar Product Suggestions**:
```php
// Trong ProductController
public function getSimilarProducts(Product $product)
{
    $vectorSearch = app(VectorSearchService::class);
    $similar = $vectorSearch->findSimilarProducts($product, 4);
    
    return response()->json([
        'similar_products' => $similar->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->name,
                'brand' => $item->brand,
                'similarity_score' => $item->similarity,
                'reason' => 'Similar fragrance profile'
            ];
        })
    ]);
}
```

### 3. Enhanced AI Context

**Cải thiện LLM responses**:
```php
// Trong LLMService
public function chatWithSemanticContext(string $message, array $context = []): string
{
    // Tìm sản phẩm liên quan bằng semantic search
    $vectorSearch = app(VectorSearchService::class);
    $relevantProducts = $vectorSearch->searchSimilarProducts($message, 3);
    
    if ($relevantProducts->isNotEmpty()) {
        $context['semantic_products'] = $this->formatSemanticProducts($relevantProducts);
    }
    
    return $this->chat($message, $context);
}
```

---

## 📊 PERFORMANCE & OPTIMIZATION

### 1. Caching Strategy

```php
// Cache embeddings để tránh regenerate
Cache::remember("product_embedding_{$productId}_{$type}", 3600, function() use ($product, $type) {
    return ProductEmbedding::where('product_id', $product->id)
        ->where('content_type', $type)
        ->first();
});
```

### 2. Batch Processing

```php
// Process multiple products cùng lúc
public function generateBatchEmbeddings(array $productIds): void
{
    $products = Product::whereIn('id', $productIds)->get();
    
    foreach ($products as $product) {
        $this->generateProductEmbeddings($product);
        usleep(50000); // 50ms delay để tránh rate limit
    }
}
```

### 3. Indexing

```sql
-- Tạo indexes cho performance
CREATE INDEX idx_product_embeddings_product_type ON product_embeddings(product_id, content_type);
CREATE INDEX idx_product_embeddings_model ON product_embeddings(model_name);
```

---

## 🚀 DEPLOYMENT ROADMAP

### Week 1: Foundation
- [ ] Tạo migration cho product_embeddings table
- [ ] Implement EmbeddingService
- [ ] Test với một vài sản phẩm

### Week 2: Core Features
- [ ] Implement VectorSearchService
- [ ] Tạo command generate embeddings
- [ ] Integrate với OmniAI chat

### Week 3: Enhancement
- [ ] Add semantic search to product search
- [ ] Implement similar products feature
- [ ] Performance optimization

### Week 4: Production
- [ ] Batch generate embeddings cho tất cả products
- [ ] Monitoring và error handling
- [ ] Documentation và testing

---

## 💰 COST ESTIMATION

### OpenAI Embedding Costs
- **text-embedding-3-small**: $0.00002 per 1K tokens
- **Average product**: ~200 tokens
- **1000 products**: ~$4
- **Monthly queries**: ~$10-20

### Storage Costs
- **SQLite**: Minimal (embedded)
- **PostgreSQL**: ~$5-10/month
- **External Vector DB**: ~$20-50/month

---

## 🔍 MONITORING & ANALYTICS

### Metrics to Track
- **Search Accuracy**: Click-through rates
- **Response Time**: Embedding generation + search
- **Cost Tracking**: OpenAI API usage
- **User Satisfaction**: Search result relevance

### Logging
```php
Log::info('Semantic Search', [
    'query' => $query,
    'results_count' => $results->count(),
    'avg_similarity' => $results->avg('similarity'),
    'response_time' => $responseTime
]);
```

---

## ✅ KẾT LUẬN

Việc tích hợp Embedding và Vector Store sẽ:

1. **Nâng cao trải nghiệm tìm kiếm** với semantic understanding
2. **Cải thiện gợi ý sản phẩm** dựa trên similarity
3. **Tăng độ chính xác** của AI responses
4. **Chuẩn bị cho tương lai** với advanced AI features

**Recommendation**: Bắt đầu với **SQLite + sqlite-vss** cho development, sau đó migrate sang **PostgreSQL + pgvector** cho production.

