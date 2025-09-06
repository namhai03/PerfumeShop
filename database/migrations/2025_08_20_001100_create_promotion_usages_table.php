<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('order_code')->nullable(); // mã đơn để truy vết
            $table->decimal('discount_amount', 15, 2);
            $table->json('context')->nullable(); // lưu giỏ hàng, điều kiện
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};


