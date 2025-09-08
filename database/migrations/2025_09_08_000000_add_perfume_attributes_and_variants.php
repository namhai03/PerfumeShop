<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thuộc tính mùi hương & hiển thị sản phẩm
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'fragrance_family')) {
                $table->string('fragrance_family')->nullable()->after('concentration');
            }
            if (!Schema::hasColumn('products', 'top_notes')) {
                $table->text('top_notes')->nullable()->after('fragrance_family');
            }
            if (!Schema::hasColumn('products', 'heart_notes')) {
                $table->text('heart_notes')->nullable()->after('top_notes');
            }
            if (!Schema::hasColumn('products', 'base_notes')) {
                $table->text('base_notes')->nullable()->after('heart_notes');
            }
            if (!Schema::hasColumn('products', 'gender')) {
                $table->string('gender')->nullable()->after('base_notes'); // male|female|unisex (string)
            }
            if (!Schema::hasColumn('products', 'style')) {
                $table->string('style')->nullable()->after('gender'); // CSV hoặc chuỗi ngắn
            }
            if (!Schema::hasColumn('products', 'season')) {
                $table->string('season')->nullable()->after('style'); // CSV: Spring,Summer,Autumn,Winter
            }
        });

        // Bảng biến thể theo dung tích
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('sku')->unique();
                $table->integer('volume_ml');
                $table->decimal('import_price', 10, 2)->nullable();
                $table->decimal('selling_price', 10, 2)->nullable();
                $table->integer('stock')->default(0);
                $table->string('barcode')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->unique(['product_id', 'volume_ml']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'fragrance_family','top_notes','heart_notes','base_notes','gender','style','season'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('product_variants');
    }
};


