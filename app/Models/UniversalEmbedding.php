<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UniversalEmbedding extends Model
{
    protected $fillable = [
        'embeddable_type',
        'embeddable_id',
        'content_type',
        'content_text',
        'embedding',
        'model_name',
        'metadata'
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the parent embeddable model.
     */
    public function embeddable(): MorphTo
    {
        return $this->morphTo();
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
     * Scope to get embeddings by embeddable type.
     */
    public function scopeByEmbeddableType($query, string $type)
    {
        return $query->where('embeddable_type', $type);
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

    /**
     * Get embedding statistics by type.
     */
    public static function getStatsByType(): array
    {
        return self::select('embeddable_type', 'content_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('embeddable_type', 'content_type')
            ->get()
            ->groupBy('embeddable_type')
            ->map(function ($group) {
                return $group->pluck('count', 'content_type');
            })
            ->toArray();
    }
}
