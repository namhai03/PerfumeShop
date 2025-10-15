<?php

namespace App\Services;

use App\Models\UniversalEmbedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UniversalVectorSearchService
{
    private UniversalEmbeddingService $embeddingService;

    public function __construct(UniversalEmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Search across all data types using semantic similarity.
     */
    public function universalSearch(string $query, int $limit = 10, array $types = []): Collection
    {
        try {
            $queryEmbedding = $this->embeddingService->generateEmbedding($query);
            
            if (empty($queryEmbedding)) {
                return collect();
            }

            $typeFilter = '';
            $params = [json_encode($queryEmbedding)];
            
            if (!empty($types)) {
                $placeholders = str_repeat('?,', count($types) - 1) . '?';
                $typeFilter = "AND ue.embeddable_type IN ($placeholders)";
                $params = array_merge($params, $types);
            }
            
            $params[] = $limit;

            $results = DB::select("
                SELECT 
                    ue.embeddable_type,
                    ue.embeddable_id,
                    ue.content_type,
                    ue.content_text,
                    ue.metadata,
                    -- Calculate cosine similarity
                    (
                        SELECT SUM(a.value * b.value) / 
                        (SQRT(SUM(a.value * a.value)) * SQRT(SUM(b.value * b.value)))
                        FROM json_each(ue.embedding) a
                        CROSS JOIN json_each(?) b
                        WHERE CAST(a.key AS INTEGER) = CAST(b.key AS INTEGER)
                    ) as similarity
                FROM universal_embeddings ue
                WHERE ue.content_type = 'combined'
                {$typeFilter}
                ORDER BY similarity DESC
                LIMIT ?
            ", $params);

            return collect($results)->map(function ($item) {
                return (object) [
                    'type' => $this->getShortTypeName($item->embeddable_type),
                    'id' => $item->embeddable_id,
                    'content_type' => $item->content_type,
                    'content_text' => $item->content_text,
                    'metadata' => json_decode($item->metadata, true),
                    'similarity' => round($item->similarity, 4),
                    'match_type' => 'semantic'
                ];
            });

        } catch (\Exception $e) {
            Log::error('Universal vector search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Search products using semantic similarity.
     */
    public function searchProducts(string $query, int $limit = 5): Collection
    {
        return $this->universalSearch($query, $limit, ['App\\Models\\Product']);
    }

    /**
     * Search orders using semantic similarity.
     */
    public function searchOrders(string $query, int $limit = 5): Collection
    {
        return $this->universalSearch($query, $limit, ['App\\Models\\Order']);
    }

    /**
     * Search shipments using semantic similarity.
     */
    public function searchShipments(string $query, int $limit = 5): Collection
    {
        return $this->universalSearch($query, $limit, ['App\\Models\\Shipment']);
    }

    /**
     * Search customers using semantic similarity.
     */
    public function searchCustomers(string $query, int $limit = 5): Collection
    {
        return $this->universalSearch($query, $limit, ['App\\Models\\Customer']);
    }

    /**
     * Search promotions using semantic similarity.
     */
    public function searchPromotions(string $query, int $limit = 5): Collection
    {
        return $this->universalSearch($query, $limit, ['App\\Models\\Promotion']);
    }

    /**
     * Hybrid search combining semantic and keyword search.
     */
    public function hybridSearch(string $query, int $limit = 10): Collection
    {
        // Get semantic results
        $semanticResults = $this->universalSearch($query, $limit);
        
        // Get keyword results as fallback
        $keywordResults = $this->keywordSearch($query, $limit);
        
        // Combine and deduplicate results
        $combined = collect();
        $seenKeys = [];
        
        // Add semantic results first (higher priority)
        foreach ($semanticResults as $result) {
            $key = $result->type . '_' . $result->id;
            if (!in_array($key, $seenKeys)) {
                $combined->push($result);
                $seenKeys[] = $key;
            }
        }
        
        // Add keyword results if we need more
        foreach ($keywordResults as $result) {
            $key = $result->type . '_' . $result->id;
            if (!in_array($key, $seenKeys) && $combined->count() < $limit) {
                $result->match_type = 'keyword';
                $result->similarity = 0.5; // Default similarity for keyword matches
                $combined->push($result);
                $seenKeys[] = $key;
            }
        }
        
        return $combined->take($limit);
    }

    /**
     * Keyword-based search as fallback.
     */
    private function keywordSearch(string $query, int $limit = 10): Collection
    {
        $keywords = explode(' ', strtolower($query));
        $results = collect();
        
        // Search in products
        $products = DB::table('products')
            ->where('is_active', 1)
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('brand', 'like', "%{$keyword}%");
                }
            })
            ->limit($limit)
            ->get();

        foreach ($products as $product) {
            $results->push((object) [
                'type' => 'product',
                'id' => $product->id,
                'content_type' => 'keyword_match',
                'content_text' => $product->name,
                'metadata' => [
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'price' => $product->selling_price
                ],
                'similarity' => 0.5,
                'match_type' => 'keyword'
            ]);
        }

        // Search in orders
        $orders = DB::table('orders')
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('order_number', 'like', "%{$keyword}%")
                      ->orWhere('customer_name', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                }
            })
            ->limit($limit)
            ->get();

        foreach ($orders as $order) {
            $results->push((object) [
                'type' => 'order',
                'id' => $order->id,
                'content_type' => 'keyword_match',
                'content_text' => "Order: " . $order->order_number,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount
                ],
                'similarity' => 0.5,
                'match_type' => 'keyword'
            ]);
        }

        // Search in customers
        $customers = DB::table('customers')
            ->where('is_active', 1)
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                }
            })
            ->limit($limit)
            ->get();

        foreach ($customers as $customer) {
            $results->push((object) [
                'type' => 'customer',
                'id' => $customer->id,
                'content_type' => 'keyword_match',
                'content_text' => "Customer: " . $customer->name,
                'metadata' => [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'total_spent' => $customer->total_spent
                ],
                'similarity' => 0.5,
                'match_type' => 'keyword'
            ]);
        }

        return $results->take($limit);
    }

    /**
     * Get search suggestions based on all data types.
     */
    public function getSearchSuggestions(string $query, int $limit = 10): Collection
    {
        $results = $this->universalSearch($query, $limit);
        
        return $results->map(function ($result) {
            $suggestion = '';
            $metadata = $result->metadata ?? [];
            
            switch ($result->type) {
                case 'product':
                    $suggestion = $metadata['name'] ?? 'Product #' . $result->id;
                    break;
                case 'order':
                    $suggestion = 'Order: ' . ($metadata['order_number'] ?? '#' . $result->id);
                    break;
                case 'customer':
                    $suggestion = 'Customer: ' . ($metadata['name'] ?? '#' . $result->id);
                    break;
                case 'shipment':
                    $suggestion = 'Shipment: ' . ($metadata['tracking_code'] ?? '#' . $result->id);
                    break;
                case 'promotion':
                    $suggestion = 'Promotion: ' . ($metadata['code'] ?? '#' . $result->id);
                    break;
                default:
                    $suggestion = ucfirst($result->type) . ' #' . $result->id;
            }
            
            return [
                'text' => $suggestion,
                'type' => $result->type,
                'id' => $result->id,
                'similarity' => $result->similarity,
                'metadata' => $metadata
            ];
        });
    }

    /**
     * Analyze search performance.
     */
    public function analyzeSearchPerformance(string $query): array
    {
        $startTime = microtime(true);
        
        $semanticResults = $this->universalSearch($query, 10);
        $keywordResults = $this->keywordSearch($query, 10);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // milliseconds
        
        return [
            'query' => $query,
            'response_time_ms' => $responseTime,
            'semantic_results_count' => $semanticResults->count(),
            'keyword_results_count' => $keywordResults->count(),
            'avg_semantic_similarity' => $semanticResults->avg('similarity'),
            'has_embeddings' => $semanticResults->count() > 0,
            'results_by_type' => $semanticResults->groupBy('type')->map->count()
        ];
    }

    /**
     * Get embedding coverage statistics.
     */
    public function getCoverageStats(): array
    {
        return $this->embeddingService->getComprehensiveStats();
    }

    /**
     * Convert full class name to short type name.
     */
    private function getShortTypeName(string $fullClassName): string
    {
        $map = [
            'App\\Models\\Product' => 'product',
            'App\\Models\\Order' => 'order',
            'App\\Models\\Shipment' => 'shipment',
            'App\\Models\\Customer' => 'customer',
            'App\\Models\\Promotion' => 'promotion'
        ];
        
        return $map[$fullClassName] ?? strtolower(class_basename($fullClassName));
    }
}
