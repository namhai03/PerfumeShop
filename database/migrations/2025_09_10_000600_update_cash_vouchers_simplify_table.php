<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('cash_vouchers', 'payer_group')) $table->dropColumn('payer_group');
            if (Schema::hasColumn('cash_vouchers', 'payer_id')) $table->dropColumn('payer_id');
            if (Schema::hasColumn('cash_vouchers', 'payer_type')) $table->dropColumn('payer_type');
            if (Schema::hasColumn('cash_vouchers', 'reference')) $table->dropColumn('reference');
            if (Schema::hasColumn('cash_vouchers', 'from_account_id')) $table->dropForeign(['from_account_id']);
            if (Schema::hasColumn('cash_vouchers', 'to_account_id')) $table->dropForeign(['to_account_id']);
        });

        Schema::table('cash_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('cash_vouchers', 'from_account_id')) $table->dropColumn('from_account_id');
            if (Schema::hasColumn('cash_vouchers', 'to_account_id')) $table->dropColumn('to_account_id');
            if (Schema::hasColumn('cash_vouchers', 'branch_id')) $table->dropColumn('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('cash_vouchers', function (Blueprint $table) {
            // Note: Down migration recreates columns without foreign keys to avoid errors.
            if (!Schema::hasColumn('cash_vouchers', 'payer_group')) $table->string('payer_group')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'payer_id')) $table->unsignedBigInteger('payer_id')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'payer_type')) $table->string('payer_type')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'reference')) $table->string('reference')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'from_account_id')) $table->unsignedBigInteger('from_account_id')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'to_account_id')) $table->unsignedBigInteger('to_account_id')->nullable();
            if (!Schema::hasColumn('cash_vouchers', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable();
        });
    }
};


