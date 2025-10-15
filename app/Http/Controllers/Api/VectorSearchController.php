<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VectorEmbeddingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VectorSearchController extends Controller
{
    private VectorEmbeddingService $embeddingService;
    
    public function __construct()
    {
        $this->embeddingService = new VectorEmbeddingService();
    }
    
    /**
     * Search across all data types
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50',
            'type' => 'string|in:Product,Order,Shipment,Customer,Promotion'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        $type = $request->input('type');
        
        try {
            $results = $this->embeddingService->search($query, $limit, $type);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => $type,
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search products only
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        
        try {
            $results = $this->embeddingService->searchProducts($query, $limit);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => 'Product',
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search orders only
     */
    public function searchOrders(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        
        try {
            $results = $this->embeddingService->searchOrders($query, $limit);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => 'Order',
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search customers only
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        
        try {
            $results = $this->embeddingService->searchCustomers($query, $limit);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => 'Customer',
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search shipments only
     */
    public function searchShipments(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        
        try {
            $results = $this->embeddingService->searchShipments($query, $limit);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => 'Shipment',
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search promotions only
     */
    public function searchPromotions(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'limit' => 'integer|min:1|max:50'
        ]);
        
        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        
        try {
            $results = $this->embeddingService->searchPromotions($query, $limit);
            
            return response()->json([
                'success' => true,
                'query' => $query,
                'limit' => $limit,
                'type' => 'Promotion',
                'results' => $results,
                'count' => count($results)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get vector store statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->embeddingService->getStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear all embeddings
     */
    public function clearAll(): JsonResponse
    {
        try {
            $result = $this->embeddingService->clearAll();
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'All embeddings cleared successfully' : 'Failed to clear embeddings'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check service configuration
     */
    public function checkConfig(): JsonResponse
    {
        $isConfigured = $this->embeddingService->isConfigured();
        
        return response()->json([
            'success' => true,
            'configured' => $isConfigured,
            'message' => $isConfigured ? 'Service is properly configured' : 'Service is not configured. Please set OPENAI_API_KEY.'
        ]);
    }
}
