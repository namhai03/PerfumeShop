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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên sản phẩm
            $table->text('description')->nullable(); // Mô tả
            $table->decimal('import_price', 10, 2); // Giá nhập
            $table->decimal('selling_price', 10, 2); // Giá bán
            $table->string('category'); // Danh mục
            $table->string('brand')->nullable(); // Thương hiệu
            $table->string('sku')->unique(); // Mã sản phẩm
            $table->integer('stock')->default(0); // Số lượng tồn kho
            $table->string('image')->nullable(); // Hình ảnh
            $table->string('volume')->nullable(); // Dung tích (ml)
            $table->string('concentration')->nullable(); // Nồng độ
            $table->string('origin')->nullable(); // Xuất xứ
            $table->date('import_date')->nullable(); // Ngày nhập hàng
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
