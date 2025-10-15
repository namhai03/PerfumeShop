<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\EmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateProductEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-embeddings 
                            {--product-id= : Generate embeddings for specific product ID}
                            {--batch-size=5 : Number of products to process in each batch}
                            {--force : Regenerate embeddings even if they exist}
                            {--content-type=combined : Content type to generate (name, description, notes, combined)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for products using OpenAI API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $embeddingService = app(EmbeddingService::class);
        
        if (!$embeddingService->isConfigured()) {
            $this->error('Embedding service not configured. Please set OPENAI_API_KEY in .env file.');
            return 1;
        }

        $productId = $this->option('product-id');
        $batchSize = (int) $this->option('batch-size');
        $force = $this->option('force');
        $contentType = $this->option('content-type');

        $this->info('🚀 Starting embedding generation...');
        $this->info("📊 Configuration:");
        $this->info("   - Batch size: {$batchSize}");
        $this->info("   - Content type: {$contentType}");
        $this->info("   - Force regenerate: " . ($force ? 'Yes' : 'No'));

        // Build query
        $query = Product::query();
        
        if ($productId) {
            $query->where('id', $productId);
            $this->info("   - Target product ID: {$productId}");
        }

        $products = $query->get();
        
        if ($products->isEmpty()) {
            $this->warn('No products found matching criteria.');
            return 0;
        }

        $this->info("📦 Found {$products->count()} products to process");

        // Show stats before processing
        $stats = $embeddingService->getEmbeddingStats();
        $this->info("📈 Current embedding stats:");
        $this->info("   - Total products: {$stats['total_products']}");
        $this->info("   - Products with embeddings: {$stats['products_with_embeddings']}");
        $this->info("   - Coverage: {$stats['coverage_percentage']}%");

        // Confirm before proceeding
        if (!$this->confirm('Do you want to proceed with embedding generation?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        $processed = 0;
        $errors = 0;

        $products->chunk($batchSize)->each(function ($chunk) use ($embeddingService, $force, $progressBar, &$processed, &$errors) {
            foreach ($chunk as $product) {
                try {
                    // Check if embeddings already exist
                    if (!$force && $embeddingService->hasEmbeddings($product)) {
                        $this->line("\n⏭️  Skipping {$product->name} (embeddings already exist)");
                        $progressBar->advance();
                        continue;
                    }

                    $this->line("\n🔄 Processing: {$product->name}");
                    
                    $embeddingService->generateProductEmbeddings($product);
                    
                    $processed++;
                    
                    // Rate limiting - 100ms delay between requests
                    usleep(100000);
                    
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("\n❌ Failed to process {$product->name}: " . $e->getMessage());
                    
                    Log::error('Embedding generation failed for product', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $this->info('✅ Embedding generation completed!');
        $this->info("📊 Results:");
        $this->info("   - Successfully processed: {$processed}");
        $this->info("   - Errors: {$errors}");
        
        // Show updated stats
        $updatedStats = $embeddingService->getEmbeddingStats();
        $this->info("📈 Updated embedding stats:");
        $this->info("   - Total products: {$updatedStats['total_products']}");
        $this->info("   - Products with embeddings: {$updatedStats['products_with_embeddings']}");
        $this->info("   - Coverage: {$updatedStats['coverage_percentage']}%");
        $this->info("   - Total embeddings: {$updatedStats['total_embeddings']}");

        if ($errors > 0) {
            $this->warn("⚠️  {$errors} products failed to process. Check logs for details.");
            return 1;
        }

        return 0;
    }
}





