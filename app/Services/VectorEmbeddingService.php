<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VectorEmbeddingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private VectorStoreService $vectorStore;
    
    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = (string) config('services.openai.embedding_model', 'text-embedding-3-small');
        $this->vectorStore = new VectorStoreService();
    }
    
    /**
     * Check if the service is configured.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }
    
    /**
     * Generate embedding for a given text.
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Embedding service not configured. Please set OPENAI_API_KEY.');
        }
        
        if (empty(trim($text))) {
            return [];
        }
        
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/embeddings', [
                    'model' => $this->model,
                    'input' => $text,
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'][0]['embedding'] ?? [];
            } else {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error('Embedding generation failed', [
                'text' => substr($text, 0, 100) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate embeddings for a product and store in vector store
     */
    public function generateProductEmbeddings(Product $product): void
    {
        $embeddings = [
            'name' => $this->generateEmbedding($product->name),
            'description' => $this->generateEmbedding($product->description ?? ''),
            'fragrance_notes' => $this->generateEmbedding($this->formatFragranceNotes($product)),
            'combined' => $this->generateEmbedding($this->formatCombinedContent($product))
        ];
        
        foreach ($embeddings as $contentType => $embedding) {
            if (!empty($embedding)) {
                $this->vectorStore->storeEmbedding([
                    'embeddable_type' => get_class($product),
                    'embeddable_id' => $product->id,
                    'content_type' => $contentType,
                    'content_text' => $this->getContentByType($product, $contentType),
                    'embedding' => $embedding,
                    'model_name' => $this->model,
                    'metadata' => [
                        'brand' => $product->brand ?? '',
                        'category' => $product->category ?? '',
                        'price' => $product->price ?? 0,
                        'stock' => $product->stock ?? 0,
                        'image' => $product->image ?? '',
                        'is_active' => $product->is_active ?? true
                    ]
                ]);
            }
        }
    }
    
    /**
     * Generate embeddings for an order and store in vector store
     */
    public function generateOrderEmbeddings(Order $order): void
    {
        $content = "Order: {$order->order_number}. Status: {$order->status}. Type: {$order->type}. Total: " . 
                  number_format($order->total_amount) . " VND. Final: " . 
                  number_format($order->final_amount) . " VND. Notes: " . ($order->notes ?? '');
        
        $embedding = $this->generateEmbedding($content);
        
        if (!empty($embedding)) {
            $this->vectorStore->storeEmbedding([
                'embeddable_type' => get_class($order),
                'embeddable_id' => $order->id,
                'content_type' => 'order_info',
                'content_text' => $content,
                'embedding' => $embedding,
                'model_name' => $this->model,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'type' => $order->type,
                    'total_amount' => $order->total_amount,
                    'final_amount' => $order->final_amount,
                    'order_date' => $order->order_date,
                    'customer_id' => $order->customer_id
                ]
            ]);
        }
    }
    
    /**
     * Generate embeddings for a shipment and store in vector store
     */
    public function generateShipmentEmbeddings(Shipment $shipment): void
    {
        $content = "Tracking Code: {$shipment->tracking_code}. Order Code: {$shipment->order_code}. " .
                  "Status: {$shipment->status}. Carrier: {$shipment->carrier}";
        
        $embedding = $this->generateEmbedding($content);
        
        if (!empty($embedding)) {
            $this->vectorStore->storeEmbedding([
                'embeddable_type' => get_class($shipment),
                'embeddable_id' => $shipment->id,
                'content_type' => 'tracking_info',
                'content_text' => $content,
                'embedding' => $embedding,
                'model_name' => $this->model,
                'metadata' => [
                    'order_code' => $shipment->order_code,
                    'tracking_code' => $shipment->tracking_code,
                    'status' => $shipment->status,
                    'carrier' => $shipment->carrier,
                    'cod_amount' => $shipment->cod_amount,
                    'shipping_fee' => $shipment->shipping_fee
                ]
            ]);
        }
    }
    
    /**
     * Generate embeddings for a customer and store in vector store
     */
    public function generateCustomerEmbeddings(Customer $customer): void
    {
        $content = "Name: {$customer->name}. Type: {$customer->customer_type}. " .
                  "Gender: " . ($customer->gender ?? 'N/A') . ". Total Spent: " .
                  number_format($customer->total_spent ?? 0) . " VND. Total Orders: " .
                  ($customer->total_orders ?? 0);
        
        $embedding = $this->generateEmbedding($content);
        
        if (!empty($embedding)) {
            $this->vectorStore->storeEmbedding([
                'embeddable_type' => get_class($customer),
                'embeddable_id' => $customer->id,
                'content_type' => 'profile',
                'content_text' => $content,
                'embedding' => $embedding,
                'model_name' => $this->model,
                'metadata' => [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'customer_type' => $customer->customer_type,
                    'total_spent' => $customer->total_spent,
                    'total_orders' => $customer->total_orders,
                    'is_active' => $customer->is_active
                ]
            ]);
        }
    }
    
    /**
     * Generate embeddings for a promotion and store in vector store
     */
    public function generatePromotionEmbeddings(Promotion $promotion): void
    {
        $content = "Code: {$promotion->code}. Name: {$promotion->name}. Type: {$promotion->type}. " .
                  "Scope: {$promotion->scope}. Discount: {$promotion->discount_value}%. " .
                  "Max Discount: " . number_format($promotion->max_discount ?? 0) . " VND. " .
                  "Min Order: " . number_format($promotion->min_order ?? 0) . " VND";
        
        $embedding = $this->generateEmbedding($content);
        
        if (!empty($embedding)) {
            $this->vectorStore->storeEmbedding([
                'embeddable_type' => get_class($promotion),
                'embeddable_id' => $promotion->id,
                'content_type' => 'promotion_info',
                'content_text' => $content,
                'embedding' => $embedding,
                'model_name' => $this->model,
                'metadata' => [
                    'code' => $promotion->code,
                    'name' => $promotion->name,
                    'type' => $promotion->type,
                    'discount_value' => $promotion->discount_value,
                    'is_active' => $promotion->is_active,
                    'start_at' => $promotion->start_at,
                    'end_at' => $promotion->end_at
                ]
            ]);
        }
    }
    
    /**
     * Search across all embeddings
     */
    public function search(string $query, int $limit = 10, ?string $embeddableType = null): array
    {
        // Generate embedding for query
        $queryEmbedding = $this->generateEmbedding($query);
        
        if (empty($queryEmbedding)) {
            return [];
        }
        
        // Perform hybrid search
        return $this->vectorStore->hybridSearch($query, $queryEmbedding, $limit, $embeddableType);
    }
    
    /**
     * Search products only
     */
    public function searchProducts(string $query, int $limit = 10): array
    {
        return $this->search($query, $limit, 'App\\Models\\Product');
    }
    
    /**
     * Search orders only
     */
    public function searchOrders(string $query, int $limit = 10): array
    {
        return $this->search($query, $limit, 'App\\Models\\Order');
    }
    
    /**
     * Search customers only
     */
    public function searchCustomers(string $query, int $limit = 10): array
    {
        return $this->search($query, $limit, 'App\\Models\\Customer');
    }
    
    /**
     * Search shipments only
     */
    public function searchShipments(string $query, int $limit = 10): array
    {
        return $this->search($query, $limit, 'App\\Models\\Shipment');
    }
    
    /**
     * Search promotions only
     */
    public function searchPromotions(string $query, int $limit = 10): array
    {
        return $this->search($query, $limit, 'App\\Models\\Promotion');
    }
    
    /**
     * Get vector store statistics
     */
    public function getStats(): array
    {
        return $this->vectorStore->getStats();
    }
    
    /**
     * Clear all embeddings
     */
    public function clearAll(): bool
    {
        return $this->vectorStore->clearAll();
    }
    
    /**
     * Format fragrance notes for embedding
     */
    private function formatFragranceNotes(Product $product): string
    {
        $notes = [];
        
        if ($product->top_notes) $notes[] = "Top notes: " . $product->top_notes;
        if ($product->middle_notes) $notes[] = "Middle notes: " . $product->middle_notes;
        if ($product->base_notes) $notes[] = "Base notes: " . $product->base_notes;
        
        return implode('. ', $notes);
    }
    
    /**
     * Format combined content for embedding
     */
    private function formatCombinedContent(Product $product): string
    {
        $content = [];
        
        $content[] = "Product: " . $product->name;
        if ($product->brand) $content[] = "Brand: " . $product->brand;
        if ($product->description) $content[] = "Description: " . $product->description;
        if ($product->category) $content[] = "Category: " . $product->category;
        
        $notes = $this->formatFragranceNotes($product);
        if ($notes) $content[] = $notes;
        
        if ($product->price) $content[] = "Price: " . number_format($product->price) . " VND";
        if ($product->stock !== null) $content[] = "Stock: " . $product->stock;
        
        return implode('. ', $content);
    }
    
    /**
     * Get content by type
     */
    private function getContentByType(Product $product, string $type): string
    {
        switch ($type) {
            case 'name':
                return $product->name;
            case 'description':
                return $product->description ?? '';
            case 'fragrance_notes':
                return $this->formatFragranceNotes($product);
            case 'combined':
                return $this->formatCombinedContent($product);
            default:
                return '';
        }
    }
}
