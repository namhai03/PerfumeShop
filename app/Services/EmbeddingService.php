<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = (string) config('services.openai.embedding_model', 'text-embedding-3-small');
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
                    'input' => mb_convert_encoding($text, 'UTF-8', 'auto'),
                    'encoding_format' => 'float'
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI Embedding API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to generate embedding: ' . $response->status());
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? [];

        } catch (\Exception $e) {
            Log::error('Embedding Service Error', [
                'message' => $e->getMessage(),
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Generate embeddings for a product.
     */
    public function generateProductEmbeddings(Product $product): void
    {
        $embeddings = [
            'name' => $this->generateEmbedding($product->name),
            'description' => $this->generateEmbedding($product->description ?? ''),
            'notes' => $this->generateEmbedding($this->formatFragranceNotes($product)),
            'combined' => $this->generateEmbedding($this->formatCombinedContent($product))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                ProductEmbedding::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getContentByType($product, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model
                    ]
                );
            }
        }

        Log::info('Generated embeddings for product', [
            'product_id' => $product->id,
            'product_name' => $product->name
        ]);
    }

    /**
     * Format fragrance notes for embedding.
     */
    private function formatFragranceNotes(Product $product): string
    {
        $notes = [];
        
        if ($product->top_notes) {
            $notes[] = "Top notes: " . $product->top_notes;
        }
        if ($product->heart_notes) {
            $notes[] = "Heart notes: " . $product->heart_notes;
        }
        if ($product->base_notes) {
            $notes[] = "Base notes: " . $product->base_notes;
        }
        if ($product->fragrance_family) {
            $notes[] = "Fragrance family: " . $product->fragrance_family;
        }

        return implode('. ', $notes);
    }

    /**
     * Format combined content for embedding.
     */
    private function formatCombinedContent(Product $product): string
    {
        $content = [];
        
        $content[] = $product->name;
        
        if ($product->brand) {
            $content[] = "Brand: " . $product->brand;
        }
        
        if ($product->description) {
            $content[] = $product->description;
        }
        
        if ($product->fragrance_family) {
            $content[] = "Fragrance family: " . $product->fragrance_family;
        }
        
        if ($product->gender) {
            $content[] = "Gender: " . $product->gender;
        }
        
        if ($product->style) {
            $content[] = "Style: " . $product->style;
        }
        
        if ($product->season) {
            $content[] = "Season: " . $product->season;
        }
        
        $notes = $this->formatFragranceNotes($product);
        if ($notes) {
            $content[] = $notes;
        }
        
        if ($product->ingredients) {
            $content[] = "Ingredients: " . $product->ingredients;
        }

        return implode('. ', $content);
    }

    /**
     * Get content by type for storage.
     */
    private function getContentByType(Product $product, string $type): string
    {
        switch ($type) {
            case 'name':
                return $product->name;
            case 'description':
                return $product->description ?? '';
            case 'notes':
                return $this->formatFragranceNotes($product);
            case 'combined':
                return $this->formatCombinedContent($product);
            default:
                return '';
        }
    }

    /**
     * Generate embeddings for multiple products in batch.
     */
    public function generateBatchEmbeddings(array $productIds, int $batchSize = 5): void
    {
        $products = Product::whereIn('id', $productIds)->get();
        
        Log::info('Starting batch embedding generation', [
            'product_count' => $products->count(),
            'batch_size' => $batchSize
        ]);

        $products->chunk($batchSize)->each(function ($chunk) {
            foreach ($chunk as $product) {
                try {
                    $this->generateProductEmbeddings($product);
                    
                    // Rate limiting - 50ms delay between requests
                    usleep(50000);
                    
                } catch (\Exception $e) {
                    Log::error('Failed to generate embeddings for product', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        Log::info('Batch embedding generation completed');
    }

    /**
     * Check if product has embeddings.
     */
    public function hasEmbeddings(Product $product): bool
    {
        return ProductEmbedding::where('product_id', $product->id)
            ->where('content_type', 'combined')
            ->exists();
    }

    /**
     * Get embedding statistics.
     */
    public function getEmbeddingStats(): array
    {
        $totalProducts = Product::count();
        $productsWithEmbeddings = ProductEmbedding::distinct('product_id')->count();
        
        return [
            'total_products' => $totalProducts,
            'products_with_embeddings' => $productsWithEmbeddings,
            'coverage_percentage' => $totalProducts > 0 ? round(($productsWithEmbeddings / $totalProducts) * 100, 2) : 0,
            'total_embeddings' => ProductEmbedding::count(),
            'model_used' => $this->model
        ];
    }
}





