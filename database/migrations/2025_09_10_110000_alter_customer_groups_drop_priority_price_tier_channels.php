<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            if (Schema::hasColumn('customer_groups', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('customer_groups', 'price_tier')) {
                $table->dropColumn('price_tier');
            }
            if (Schema::hasColumn('customer_groups', 'applies_online')) {
                $table->dropColumn('applies_online');
            }
            if (Schema::hasColumn('customer_groups', 'applies_offline')) {
                $table->dropColumn('applies_offline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0);
            $table->string('price_tier', 50)->nullable();
            $table->boolean('applies_online')->default(true);
            $table->boolean('applies_offline')->default(true);
        });
    }
};


