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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Mã đơn hàng');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['new', 'processing', 'completed'])->default('new')->comment('Trạng thái: Mới, Đang xử lý, Hoàn thành');
            $table->enum('type', ['sale', 'return', 'draft'])->default('sale')->comment('Loại: Bán hàng, Trả hàng, Đơn nháp');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Tổng tiền');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Giảm giá');
            $table->decimal('final_amount', 15, 2)->default(0)->comment('Thành tiền');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->date('order_date')->comment('Ngày đặt hàng');
            $table->date('delivery_date')->nullable()->comment('Ngày giao hàng');
            $table->string('payment_method')->nullable()->comment('Phương thức thanh toán');
            $table->string('delivery_address')->nullable()->comment('Địa chỉ giao hàng');
            $table->string('phone')->nullable()->comment('Số điện thoại');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
