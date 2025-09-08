<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category')) {
                // Đưa cột category về nullable để đồng nhất với gán nhiều danh mục qua bảng pivot
                $table->string('category')->nullable()->change();
            }

            // Tối ưu tìm kiếm/lọc
            if (!Schema::hasColumn('products', 'barcode')) {
                // an toàn: đã có ở migration khác, chỉ đề phòng môi trường lệch
                $table->string('barcode')->nullable()->after('sku');
            }

            // index hữu ích
            $table->index('brand');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Trả về NOT NULL với giá trị rỗng để an toàn
            if (Schema::hasColumn('products', 'category')) {
                $table->string('category')->default('')->nullable(false)->change();
            }

            // gỡ index đã thêm
            $table->dropIndex(['brand']);
            $table->dropIndex(['is_active']);
        });
    }
};



