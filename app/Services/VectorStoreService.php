<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class VectorStoreService
{
    private string $vectorStorePath;
    private string $indexPath;
    
    public function __construct()
    {
        $this->vectorStorePath = storage_path('app/vector_store');
        $this->indexPath = $this->vectorStorePath . '/index.json';
        
        // Tạo thư mục vector store nếu chưa có
        if (!File::exists($this->vectorStorePath)) {
            File::makeDirectory($this->vectorStorePath, 0755, true);
        }
    }
    
    /**
     * Lưu embedding vào vector store
     */
    public function storeEmbedding(array $data): bool
    {
        try {
            $id = $this->generateId($data);
            $embeddingFile = $this->vectorStorePath . '/' . $id . '.json';
            
            $embeddingData = [
                'id' => $id,
                'embeddable_type' => $data['embeddable_type'],
                'embeddable_id' => $data['embeddable_id'],
                'content_type' => $data['content_type'],
                'content_text' => $data['content_text'],
                'embedding' => $data['embedding'],
                'model_name' => $data['model_name'],
                'metadata' => $data['metadata'] ?? [],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];
            
            // Lưu embedding file
            File::put($embeddingFile, json_encode($embeddingData, JSON_PRETTY_PRINT));
            
            // Cập nhật index
            $this->updateIndex($id, $embeddingData);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error storing embedding', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }
    
    /**
     * Tìm kiếm embeddings tương tự
     */
    public function searchSimilar(array $queryEmbedding, int $limit = 10, ?string $embeddableType = null): array
    {
        try {
            $index = $this->loadIndex();
            $results = [];
            
            foreach ($index as $id => $metadata) {
                // Filter by type nếu có
                if ($embeddableType && $metadata['embeddable_type'] !== $embeddableType) {
                    continue;
                }
                
                // Load embedding
                $embeddingFile = $this->vectorStorePath . '/' . $id . '.json';
                if (!File::exists($embeddingFile)) {
                    continue;
                }
                
                $embeddingData = json_decode(File::get($embeddingFile), true);
                $similarity = $this->cosineSimilarity($queryEmbedding, $embeddingData['embedding']);
                
                $results[] = [
                    'id' => $id,
                    'similarity' => $similarity,
                    'data' => $embeddingData
                ];
            }
            
            // Sắp xếp theo similarity giảm dần
            usort($results, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            return array_slice($results, 0, $limit);
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error searching embeddings', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Tìm kiếm theo text (sử dụng keyword matching)
     */
    public function searchByText(string $query, int $limit = 10, ?string $embeddableType = null): array
    {
        try {
            $index = $this->loadIndex();
            $results = [];
            $queryLower = strtolower($query);
            
            foreach ($index as $id => $metadata) {
                // Filter by type nếu có
                if ($embeddableType && $metadata['embeddable_type'] !== $embeddableType) {
                    continue;
                }
                
                // Load embedding
                $embeddingFile = $this->vectorStorePath . '/' . $id . '.json';
                if (!File::exists($embeddingFile)) {
                    continue;
                }
                
                $embeddingData = json_decode(File::get($embeddingFile), true);
                $contentText = strtolower($embeddingData['content_text']);
                
                // Tính điểm similarity dựa trên keyword matching
                $score = $this->calculateTextSimilarity($queryLower, $contentText);
                
                if ($score > 0) {
                    $results[] = [
                        'id' => $id,
                        'similarity' => $score,
                        'content_text' => $embeddingData['content_text'],
                        'embeddable_type' => $embeddingData['embeddable_type'],
                        'content_type' => $embeddingData['content_type'],
                        'metadata' => $embeddingData['metadata']
                    ];
                }
            }
            
            // Sắp xếp theo score giảm dần
            usort($results, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            return array_slice($results, 0, $limit);
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error searching by text', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Hybrid search (kết hợp semantic và keyword)
     */
    public function hybridSearch(string $query, array $queryEmbedding, int $limit = 10, ?string $embeddableType = null): array
    {
        // Semantic search
        $semanticResults = $this->searchSimilar($queryEmbedding, $limit * 2, $embeddableType);
        
        // Keyword search
        $keywordResults = $this->searchByText($query, $limit * 2, $embeddableType);
        
        // Combine và rank results
        $combinedResults = [];
        $seenIds = [];
        
        // Add semantic results với weight 0.7
        foreach ($semanticResults as $result) {
            $id = $result['id'];
            $similarity = is_numeric($result['similarity']) ? (float)$result['similarity'] : 0.0;
            $combinedResults[$id] = [
                'id' => $id,
                'semantic_score' => $similarity * 0.7,
                'keyword_score' => 0.0,
                'data' => $result['data']
            ];
            $seenIds[] = $id;
        }
        
        // Add keyword results với weight 0.3
        foreach ($keywordResults as $result) {
            $id = $result['id'];
            $score = is_numeric($result['similarity']) ? (float)$result['similarity'] : 0.0;
            if (isset($combinedResults[$id])) {
                $combinedResults[$id]['keyword_score'] = $score * 0.3;
            } else {
                $combinedResults[$id] = [
                    'id' => $id,
                    'semantic_score' => 0.0,
                    'keyword_score' => $score * 0.3,
                    'data' => $result
                ];
            }
        }
        
        // Calculate final score
        foreach ($combinedResults as $id => $result) {
            $combinedResults[$id]['final_score'] = $result['semantic_score'] + $result['keyword_score'];
        }
        
        // Sort by final score
        uasort($combinedResults, function($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });
        
        return array_slice($combinedResults, 0, $limit);
    }
    
    /**
     * Lấy thống kê vector store
     */
    public function getStats(): array
    {
        try {
            $index = $this->loadIndex();
            $stats = [
                'total_embeddings' => count($index),
                'by_type' => [],
                'by_model' => [],
                'storage_size' => 0
            ];
            
            foreach ($index as $id => $metadata) {
                // Count by type
                $type = $metadata['embeddable_type'];
                $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
                
                // Count by model
                $model = $metadata['model_name'];
                $stats['by_model'][$model] = ($stats['by_model'][$model] ?? 0) + 1;
                
                // Calculate storage size
                $embeddingFile = $this->vectorStorePath . '/' . $id . '.json';
                if (File::exists($embeddingFile)) {
                    $stats['storage_size'] += File::size($embeddingFile);
                }
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error getting stats', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Xóa embedding
     */
    public function deleteEmbedding(string $id): bool
    {
        try {
            $embeddingFile = $this->vectorStorePath . '/' . $id . '.json';
            
            if (File::exists($embeddingFile)) {
                File::delete($embeddingFile);
            }
            
            // Remove from index
            $this->removeFromIndex($id);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error deleting embedding', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return false;
        }
    }
    
    /**
     * Xóa tất cả embeddings
     */
    public function clearAll(): bool
    {
        try {
            // Xóa tất cả files
            $files = File::files($this->vectorStorePath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    File::delete($file);
                }
            }
            
            // Xóa index
            if (File::exists($this->indexPath)) {
                File::delete($this->indexPath);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('VectorStore: Error clearing all', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Generate unique ID
     */
    private function generateId(array $data): string
    {
        $base = $data['embeddable_type'] . '_' . $data['embeddable_id'] . '_' . $data['content_type'];
        return md5($base);
    }
    
    /**
     * Load index
     */
    private function loadIndex(): array
    {
        if (!File::exists($this->indexPath)) {
            return [];
        }
        
        $indexContent = File::get($this->indexPath);
        return json_decode($indexContent, true) ?? [];
    }
    
    /**
     * Update index
     */
    private function updateIndex(string $id, array $data): void
    {
        $index = $this->loadIndex();
        
        $index[$id] = [
            'embeddable_type' => $data['embeddable_type'],
            'embeddable_id' => $data['embeddable_id'],
            'content_type' => $data['content_type'],
            'model_name' => $data['model_name'],
            'created_at' => $data['created_at']
        ];
        
        File::put($this->indexPath, json_encode($index, JSON_PRETTY_PRINT));
    }
    
    /**
     * Remove from index
     */
    private function removeFromIndex(string $id): void
    {
        $index = $this->loadIndex();
        unset($index[$id]);
        File::put($this->indexPath, json_encode($index, JSON_PRETTY_PRINT));
    }
    
    /**
     * Calculate cosine similarity
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }
        
        $normA = sqrt($normA);
        $normB = sqrt($normB);
        
        if ($normA == 0 || $normB == 0) {
            return 0;
        }
        
        return $dotProduct / ($normA * $normB);
    }
    
    /**
     * Calculate text similarity
     */
    private function calculateTextSimilarity(string $query, string $text): float
    {
        $queryWords = explode(' ', $query);
        $textWords = explode(' ', $text);
        
        $matches = 0;
        $totalQueryWords = count($queryWords);
        
        foreach ($queryWords as $queryWord) {
            foreach ($textWords as $textWord) {
                if (strpos($textWord, $queryWord) !== false || strpos($queryWord, $textWord) !== false) {
                    $matches++;
                    break;
                }
            }
        }
        
        return $totalQueryWords > 0 ? $matches / $totalQueryWords : 0;
    }
    
    /**
     * Get statistics about the vector store
     */
    public function getStatistics(): array
    {
        try {
            $index = $this->loadIndex();
            $totalEmbeddings = count($index);
            
            $stats = [
                'total_embeddings' => $totalEmbeddings,
                'product_embeddings' => 0,
                'order_embeddings' => 0,
                'customer_embeddings' => 0,
                'shipment_embeddings' => 0,
                'promotion_embeddings' => 0,
                'content_types' => []
            ];
            
            foreach ($index as $id => $metadata) {
                $embeddableType = $metadata['embeddable_type'] ?? '';
                $contentType = $metadata['content_type'] ?? '';
                
                if (strpos($embeddableType, 'Product') !== false) {
                    $stats['product_embeddings']++;
                } elseif (strpos($embeddableType, 'Order') !== false) {
                    $stats['order_embeddings']++;
                } elseif (strpos($embeddableType, 'Customer') !== false) {
                    $stats['customer_embeddings']++;
                } elseif (strpos($embeddableType, 'Shipment') !== false) {
                    $stats['shipment_embeddings']++;
                } elseif (strpos($embeddableType, 'Promotion') !== false) {
                    $stats['promotion_embeddings']++;
                }
                
                if (!isset($stats['content_types'][$contentType])) {
                    $stats['content_types'][$contentType] = 0;
                }
                $stats['content_types'][$contentType]++;
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('VectorStoreService: Error getting statistics', ['error' => $e->getMessage()]);
            return [
                'total_embeddings' => 0,
                'product_embeddings' => 0,
                'order_embeddings' => 0,
                'customer_embeddings' => 0,
                'shipment_embeddings' => 0,
                'promotion_embeddings' => 0,
                'content_types' => []
            ];
        }
    }
}
