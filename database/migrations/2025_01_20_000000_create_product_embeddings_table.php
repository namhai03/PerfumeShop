<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('content_type'); // 'name', 'description', 'notes', 'combined'
            $table->text('content_text');
            $table->json('embedding'); // Store as JSON array
            $table->string('model_name')->default('text-embedding-3-small');
            $table->timestamps();
            
            $table->index(['product_id', 'content_type']);
            $table->index('model_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_embeddings');
    }
};

