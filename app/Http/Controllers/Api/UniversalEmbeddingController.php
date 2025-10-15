<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UniversalEmbeddingService;
use App\Services\UniversalVectorSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UniversalEmbeddingController extends Controller
{
    private UniversalEmbeddingService $embeddingService;
    private UniversalVectorSearchService $searchService;

    public function __construct(
        UniversalEmbeddingService $embeddingService,
        UniversalVectorSearchService $searchService
    ) {
        $this->embeddingService = $embeddingService;
        $this->searchService = $searchService;
    }

    /**
     * Generate embeddings for all data types.
     */
    public function generateAll(Request $request): JsonResponse
    {
        try {
            if (!$this->embeddingService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Embedding service not configured. Please set OPENAI_API_KEY.'
                ], 400);
            }

            $results = $this->embeddingService->generateAllEmbeddings();

            return response()->json([
                'success' => true,
                'message' => 'Embeddings generated successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate embeddings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate embeddings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Universal search across all data types.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 10);
            $types = $request->input('types', []);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->universalSearch($query, $limit, $types);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Universal search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products using semantic similarity.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 5);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->searchProducts($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Product search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search orders using semantic similarity.
     */
    public function searchOrders(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 5);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->searchOrders($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Order search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers using semantic similarity.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 5);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->searchCustomers($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Customer search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Customer search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search shipments using semantic similarity.
     */
    public function searchShipments(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 5);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->searchShipments($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Shipment search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Shipment search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search promotions using semantic similarity.
     */
    public function searchPromotions(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 5);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->searchPromotions($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Promotion search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Promotion search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hybrid search combining semantic and keyword search.
     */
    public function hybridSearch(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 10);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $results = $this->searchService->hybridSearch($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $results->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Hybrid search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hybrid search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions.
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            $limit = $request->input('limit', 10);

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $suggestions = $this->searchService->getSearchSuggestions($query, $limit);

            return response()->json([
                'success' => true,
                'query' => $query,
                'suggestions' => $suggestions,
                'count' => $suggestions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Search suggestions failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search suggestions failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze search performance.
     */
    public function analyzePerformance(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');

            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter is required'
                ], 400);
            }

            $analysis = $this->searchService->analyzeSearchPerformance($query);

            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            Log::error('Search performance analysis failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Performance analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get embedding statistics.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->searchService->getCoverageStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get embedding stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if embedding service is configured.
     */
    public function checkConfiguration(): JsonResponse
    {
        $isConfigured = $this->embeddingService->isConfigured();

        return response()->json([
            'success' => true,
            'configured' => $isConfigured,
            'message' => $isConfigured ? 'Embedding service is configured' : 'Embedding service not configured'
        ]);
    }
}
