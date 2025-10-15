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
        Schema::table('shipment_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_orders', 'cod_amount')) {
                $table->decimal('cod_amount', 12, 2)->default(0)->after('order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_orders', function (Blueprint $table) {
            $table->dropColumn('cod_amount');
        });
    }
};
