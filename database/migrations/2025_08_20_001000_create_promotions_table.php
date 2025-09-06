<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // type: percent, fixed_amount, free_shipping
            $table->string('type');
            // scope: order, product
            $table->string('scope')->default('order');
            // discount_value: percent(0-100) hoặc số tiền cố định
            $table->decimal('discount_value', 15, 2)->nullable();
            // Giới hạn số tiền giảm tối đa (áp dụng cho percent)
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            // Điều kiện áp dụng
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->unsignedInteger('min_items')->nullable();
            // Phạm vi áp dụng (lọc)
            $table->json('applicable_product_ids')->nullable();
            $table->json('applicable_category_ids')->nullable();
            $table->json('applicable_customer_group_ids')->nullable();
            $table->json('applicable_sales_channels')->nullable(); // online, offline
            // Quy tắc
            $table->boolean('is_stackable')->default(false);
            $table->integer('priority')->default(0); // cao hơn xét trước
            // Hiệu lực
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            // Giới hạn sử dụng
            $table->unsignedInteger('usage_limit')->nullable(); // tổng lượt
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};


