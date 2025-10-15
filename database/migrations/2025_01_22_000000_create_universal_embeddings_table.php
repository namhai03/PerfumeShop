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
        Schema::create('universal_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('embeddable_type');
            $table->unsignedBigInteger('embeddable_id');
            $table->string('content_type'); // name, description, combined, etc.
            $table->text('content_text');
            $table->json('embedding'); // Vector embedding array
            $table->string('model_name'); // OpenAI model used
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // Indexes for performance
            $table->index(['embeddable_type', 'embeddable_id']);
            $table->index(['embeddable_type', 'content_type']);
            $table->index('model_name');
            
            // Composite index for polymorphic relationship
            $table->index(['embeddable_type', 'embeddable_id', 'content_type'], 'universal_embeddings_polymorphic_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universal_embeddings');
    }
};
