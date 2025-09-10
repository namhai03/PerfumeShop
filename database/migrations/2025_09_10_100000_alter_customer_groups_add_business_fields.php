<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('description');
            $table->boolean('is_default')->default(false)->after('is_active');
            $table->string('price_tier', 50)->nullable()->after('is_default');
            $table->decimal('min_order_amount', 12, 2)->nullable()->after('price_tier');
            $table->decimal('max_discount_amount', 12, 2)->nullable()->after('min_order_amount');
            $table->boolean('applies_online')->default(true)->after('max_discount_amount');
            $table->boolean('applies_offline')->default(true)->after('applies_online');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'is_default',
                'price_tier',
                'min_order_amount',
                'max_discount_amount',
                'applies_online',
                'applies_offline',
                'deleted_at',
            ]);
        });
    }
};


