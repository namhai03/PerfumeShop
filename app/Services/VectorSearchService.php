<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductEmbedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VectorSearchService
{
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Search for similar products using semantic similarity.
     */
    public function searchSimilarProducts(string $query, int $limit = 5, string $contentType = 'combined'): Collection
    {
        try {
            $queryEmbedding = $this->embeddingService->generateEmbedding($query);
            
            if (empty($queryEmbedding)) {
                return collect();
            }

            // Use SQLite with JSON functions for similarity calculation
            $results = DB::select("
                SELECT 
                    pe.product_id,
                    pe.content_type,
                    pe.content_text,
                    p.name,
                    p.brand,
                    p.selling_price,
                    p.image,
                    p.fragrance_family,
                    p.gender,
                    -- Calculate cosine similarity
                    (
                        SELECT SUM(a.value * b.value) / 
                        (SQRT(SUM(a.value * a.value)) * SQRT(SUM(b.value * b.value)))
                        FROM json_each(pe.embedding) a
                        CROSS JOIN json_each(?) b
                        WHERE CAST(a.key AS INTEGER) = CAST(b.key AS INTEGER)
                    ) as similarity
                FROM product_embeddings pe
                JOIN products p ON pe.product_id = p.id
                WHERE pe.content_type = ?
                AND p.is_active = 1
                ORDER BY similarity DESC
                LIMIT ?
            ", [json_encode($queryEmbedding), $contentType, $limit]);

            return collect($results)->map(function ($item) {
                return (object) [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'brand' => $item->brand,
                    'selling_price' => $item->selling_price,
                    'image' => $item->image,
                    'fragrance_family' => $item->fragrance_family,
                    'gender' => $item->gender,
                    'similarity' => round($item->similarity, 4),
                    'match_type' => 'semantic',
                    'content_type' => $item->content_type
                ];
            });

        } catch (\Exception $e) {
            Log::error('Vector search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Find products similar to a given product.
     */
    public function findSimilarProducts(Product $product, int $limit = 5, string $contentType = 'combined'): Collection
    {
        $productEmbedding = ProductEmbedding::where('product_id', $product->id)
            ->where('content_type', $contentType)
            ->first();

        if (!$productEmbedding) {
            // Generate embeddings if not exists
            $this->embeddingService->generateProductEmbeddings($product);
            $productEmbedding = ProductEmbedding::where('product_id', $product->id)
                ->where('content_type', $contentType)
                ->first();
        }

        if (!$productEmbedding) {
            return collect();
        }

        return $this->searchByEmbedding($productEmbedding->embedding, $limit, $product->id, $contentType);
    }

    /**
     * Search products by embedding vector.
     */
    public function searchByEmbedding(array $embedding, int $limit = 5, ?int $excludeProductId = null, string $contentType = 'combined'): Collection
    {
        try {
            $excludeClause = $excludeProductId ? 'AND pe.product_id != ?' : '';
            $params = [json_encode($embedding), $contentType];
            
            if ($excludeProductId) {
                $params[] = $excludeProductId;
            }
            
            $params[] = $limit;

            $results = DB::select("
                SELECT 
                    pe.product_id,
                    pe.content_type,
                    pe.content_text,
                    p.name,
                    p.brand,
                    p.selling_price,
                    p.image,
                    p.fragrance_family,
                    p.gender,
                    -- Calculate cosine similarity
                    (
                        SELECT SUM(a.value * b.value) / 
                        (SQRT(SUM(a.value * a.value)) * SQRT(SUM(b.value * b.value)))
                        FROM json_each(pe.embedding) a
                        CROSS JOIN json_each(?) b
                        WHERE CAST(a.key AS INTEGER) = CAST(b.key AS INTEGER)
                    ) as similarity
                FROM product_embeddings pe
                JOIN products p ON pe.product_id = p.id
                WHERE pe.content_type = ?
                AND p.is_active = 1
                {$excludeClause}
                ORDER BY similarity DESC
                LIMIT ?
            ", $params);

            return collect($results)->map(function ($item) {
                return (object) [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'brand' => $item->brand,
                    'selling_price' => $item->selling_price,
                    'image' => $item->image,
                    'fragrance_family' => $item->fragrance_family,
                    'gender' => $item->gender,
                    'similarity' => round($item->similarity, 4),
                    'match_type' => 'semantic'
                ];
            });

        } catch (\Exception $e) {
            Log::error('Embedding search failed', [
                'embedding_length' => count($embedding),
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Hybrid search combining semantic and keyword search.
     */
    public function hybridSearch(string $query, int $limit = 5): Collection
    {
        // Get semantic results
        $semanticResults = $this->searchSimilarProducts($query, $limit);
        
        // Get keyword results as fallback
        $keywordResults = $this->keywordSearch($query, $limit);
        
        // Combine and deduplicate results
        $combined = collect();
        $seenIds = [];
        
        // Add semantic results first (higher priority)
        foreach ($semanticResults as $result) {
            if (!in_array($result->product_id, $seenIds)) {
                $combined->push($result);
                $seenIds[] = $result->product_id;
            }
        }
        
        // Add keyword results if we need more
        foreach ($keywordResults as $result) {
            if (!in_array($result->product_id, $seenIds) && $combined->count() < $limit) {
                $result->match_type = 'keyword';
                $result->similarity = 0.5; // Default similarity for keyword matches
                $combined->push($result);
                $seenIds[] = $result->product_id;
            }
        }
        
        return $combined->take($limit);
    }

    /**
     * Keyword-based search as fallback.
     */
    private function keywordSearch(string $query, int $limit = 5): Collection
    {
        $keywords = explode(' ', strtolower($query));
        
        $products = Product::where('is_active', 1)
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('brand', 'like', "%{$keyword}%")
                      ->orWhere('fragrance_family', 'like', "%{$keyword}%");
                }
            })
            ->limit($limit)
            ->get();

        return $products->map(function ($product) {
            return (object) [
                'product_id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'selling_price' => $product->selling_price,
                'image' => $product->image,
                'fragrance_family' => $product->fragrance_family,
                'gender' => $product->gender,
                'similarity' => 0.5,
                'match_type' => 'keyword'
            ];
        });
    }

    /**
     * Get search suggestions based on product names.
     */
    public function getSearchSuggestions(string $query, int $limit = 5): Collection
    {
        $semanticResults = $this->searchSimilarProducts($query, $limit, 'name');
        
        return $semanticResults->map(function ($result) {
            return [
                'text' => $result->name,
                'brand' => $result->brand,
                'similarity' => $result->similarity,
                'type' => 'product_name'
            ];
        });
    }

    /**
     * Analyze search performance.
     */
    public function analyzeSearchPerformance(string $query): array
    {
        $startTime = microtime(true);
        
        $semanticResults = $this->searchSimilarProducts($query, 10);
        $keywordResults = $this->keywordSearch($query, 10);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // milliseconds
        
        return [
            'query' => $query,
            'response_time_ms' => $responseTime,
            'semantic_results_count' => $semanticResults->count(),
            'keyword_results_count' => $keywordResults->count(),
            'avg_semantic_similarity' => $semanticResults->avg('similarity'),
            'has_embeddings' => $semanticResults->count() > 0
        ];
    }

    /**
     * Search products using semantic similarity (alias for searchSimilarProducts).
     * This method is used by ChatAgent and other services.
     */
    public function searchProducts(string $query, int $limit = 5, string $contentType = 'combined'): array
    {
        $results = $this->searchSimilarProducts($query, $limit, $contentType);
        
        return $results->map(function ($item) {
            return [
                'product_id' => $item->product_id ?? 0,
                'name' => $item->name ?? 'Unknown Product',
                'brand' => $item->brand ?? 'Unknown Brand',
                'price' => $item->selling_price ?? 0,
                'category' => $item->fragrance_family ?? 'N/A',
                'description' => $item->content_text ?? '',
                'similarity' => $item->similarity ?? 0,
                'match_type' => $item->match_type ?? 'semantic'
            ];
        })->toArray();
    }

    /**
     * Get embedding coverage statistics.
     */
    public function getCoverageStats(): array
    {
        $totalProducts = Product::where('is_active', 1)->count();
        $productsWithEmbeddings = ProductEmbedding::distinct('product_id')->count();
        
        $embeddingTypes = ProductEmbedding::select('content_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('content_type')
            ->get()
            ->pluck('count', 'content_type');

        return [
            'total_active_products' => $totalProducts,
            'products_with_embeddings' => $productsWithEmbeddings,
            'coverage_percentage' => $totalProducts > 0 ? round(($productsWithEmbeddings / $totalProducts) * 100, 2) : 0,
            'embedding_types' => $embeddingTypes,
            'total_embeddings' => ProductEmbedding::count()
        ];
    }
}



