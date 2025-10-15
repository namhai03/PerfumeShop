<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductEmbedding extends Model
{
    protected $fillable = [
        'product_id',
        'content_type',
        'content_text',
        'embedding',
        'model_name'
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    /**
     * Get the product that owns the embedding.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get embeddings by content type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope to get embeddings by model.
     */
    public function scopeByModel($query, string $model)
    {
        return $query->where('model_name', $model);
    }

    /**
     * Get the embedding as a normalized array.
     */
    public function getEmbeddingArray(): array
    {
        return is_array($this->embedding) ? $this->embedding : [];
    }

    /**
     * Calculate cosine similarity with another embedding.
     */
    public function cosineSimilarity(array $otherEmbedding): float
    {
        $embedding1 = $this->getEmbeddingArray();
        $embedding2 = $otherEmbedding;

        if (count($embedding1) !== count($embedding2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $norm1 += $embedding1[$i] * $embedding1[$i];
            $norm2 += $embedding2[$i] * $embedding2[$i];
        }

        if ($norm1 == 0 || $norm2 == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }
}

