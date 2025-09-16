<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột tạm kiểu string
            if (!Schema::hasColumn('orders', 'status_v2')) {
                $table->string('status_v2')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'type_v2')) {
                $table->string('type_v2')->nullable()->after('status_v2');
            }
        });

        // Copy dữ liệu từ cột cũ sang cột mới
        DB::table('orders')->update([
            'status_v2' => DB::raw('status'),
            'type_v2' => DB::raw('type'),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            // Xóa cột enum cũ (SQLite sẽ coi là TEXT CHECK), rồi đổi tên cột mới
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'status_v2')) {
                $table->renameColumn('status_v2', 'status');
            }
            if (Schema::hasColumn('orders', 'type_v2')) {
                $table->renameColumn('type_v2', 'type');
            }
        });
    }

    public function down(): void
    {
        // Không thể khôi phục enum ban đầu dễ dàng mà không có DBAL, giữ nguyên dạng string
        // Tuy nhiên, để dọn cột tạm nếu có
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'status_v2')) {
                $table->dropColumn('status_v2');
            }
            if (Schema::hasColumn('orders', 'type_v2')) {
                $table->dropColumn('type_v2');
            }
        });
    }
};


