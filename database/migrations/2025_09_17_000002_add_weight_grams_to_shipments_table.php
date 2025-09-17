<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shipments', 'weight_grams')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->unsignedInteger('weight_grams')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shipments', 'weight_grams')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('weight_grams');
            });
        }
    }
};


