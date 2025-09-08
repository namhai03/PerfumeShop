<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type', // manual | smart | system
        'sales_channel', // online | offline | null
        'conditions', // json rule for smart category
        'is_active',
        'description',
        'image',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}


