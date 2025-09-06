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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột customer_name
            $table->string('customer_name')->nullable()->after('customer_id')->comment('Tên khách hàng');
            
            // Cập nhật enum status
            $table->dropColumn('status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid')->after('customer_name')->comment('Trạng thái: Chưa thanh toán, Đã thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Xóa cột customer_name
            $table->dropColumn('customer_name');
            
            // Khôi phục enum status cũ
            $table->dropColumn('status');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['new', 'processing', 'completed'])->default('new')->after('customer_id')->comment('Trạng thái: Mới, Đang xử lý, Hoàn thành');
        });
    }
};
