<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VectorSearchService;
use App\Services\EmbeddingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SemanticSearchController extends Controller
{
    private VectorSearchService $vectorSearchService;
    private EmbeddingService $embeddingService;

    public function __construct(VectorSearchService $vectorSearchService, EmbeddingService $embeddingService)
    {
        $this->vectorSearchService = $vectorSearchService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Search products using semantic similarity.
     */
    public function searchProducts(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'nullable|integer|min:1|max:20',
            'content_type' => 'nullable|string|in:name,description,notes,combined',
            'min_similarity' => 'nullable|numeric|min:0|max:1'
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 5);
        $contentType = $request->input('content_type', 'combined');
        $minSimilarity = $request->input('min_similarity', 0.3);

        try {
            $results = $this->vectorSearchService->searchSimilarProducts($query, $limit, $contentType);
            
            // Filter by minimum similarity
            $filteredResults = $results->filter(function ($product) use ($minSimilarity) {
                return $product->similarity >= $minSimilarity;
            });

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $filteredResults->values(),
                'total_found' => $filteredResults->count(),
                'avg_similarity' => $filteredResults->avg('similarity'),
                'search_type' => 'semantic'
            ]);

        } catch (\Exception $e) {
            Log::error('Semantic search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Semantic search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find similar products to a given product.
     */
    public function findSimilarProducts(Request $request, $productId)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:10',
            'content_type' => 'nullable|string|in:name,description,notes,combined'
        ]);

        $limit = $request->input('limit', 5);
        $contentType = $request->input('content_type', 'combined');

        try {
            $product = \App\Models\Product::findOrFail($productId);
            $similarProducts = $this->vectorSearchService->findSimilarProducts($product, $limit, $contentType);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand
                ],
                'similar_products' => $similarProducts,
                'total_found' => $similarProducts->count(),
                'avg_similarity' => $similarProducts->avg('similarity')
            ]);

        } catch (\Exception $e) {
            Log::error('Find similar products failed', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to find similar products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hybrid search combining semantic and keyword search.
     */
    public function hybridSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 5);

        try {
            $results = $this->vectorSearchService->hybridSearch($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'total_found' => $results->count(),
                'semantic_count' => $results->where('match_type', 'semantic')->count(),
                'keyword_count' => $results->where('match_type', 'keyword')->count(),
                'search_type' => 'hybrid'
            ]);

        } catch (\Exception $e) {
            Log::error('Hybrid search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Hybrid search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions.
     */
    public function getSuggestions(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:200',
            'limit' => 'nullable|integer|min:1|max:10'
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 5);

        try {
            $suggestions = $this->vectorSearchService->getSearchSuggestions($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'suggestions' => $suggestions,
                'total_found' => $suggestions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get suggestions failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get suggestions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze search performance.
     */
    public function analyzePerformance(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500'
        ]);

        $query = $request->input('query');

        try {
            $analysis = $this->vectorSearchService->analyzeSearchPerformance($query);

            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            Log::error('Search performance analysis failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Performance analysis failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get embedding coverage statistics.
     */
    public function getCoverageStats()
    {
        try {
            $stats = $this->vectorSearchService->getCoverageStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get coverage stats failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get coverage stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate embeddings for a specific product.
     */
    public function generateEmbeddings(Request $request, $productId)
    {
        try {
            $product = \App\Models\Product::findOrFail($productId);
            
            $this->embeddingService->generateProductEmbeddings($product);

            return response()->json([
                'success' => true,
                'message' => 'Embeddings generated successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Generate embeddings failed', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate embeddings',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}





