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
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['draft','confirmed','processing','shipping','delivered','failed','returned'])
                ->default('draft')
                ->after('customer_name')
                ->comment('Trạng thái: Đơn nháp, Đã xác nhận, Đang xử lý, Đang giao, Đã giao, Thất bại, Hoàn trả');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['unpaid','paid'])
                ->default('unpaid')
                ->after('customer_name')
                ->comment('Trạng thái: Chưa thanh toán, Đã thanh toán');
        });
    }
};


