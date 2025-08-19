<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_movements', 'transaction_date')) {
                $table->dateTime('transaction_date')->nullable()->after('note');
            }
            if (!Schema::hasColumn('inventory_movements', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('inventory_movements', 'supplier')) {
                $table->string('supplier')->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('inventory_movements', 'reference_id')) {
                $table->string('reference_id')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('inventory_movements', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('reference_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_movements', 'transaction_date')) { $table->dropColumn('transaction_date'); }
            if (Schema::hasColumn('inventory_movements', 'unit_cost')) { $table->dropColumn('unit_cost'); }
            if (Schema::hasColumn('inventory_movements', 'supplier')) { $table->dropColumn('supplier'); }
            if (Schema::hasColumn('inventory_movements', 'reference_id')) { $table->dropColumn('reference_id'); }
            if (Schema::hasColumn('inventory_movements', 'order_id')) { $table->dropColumn('order_id'); }
        });
    }
};


