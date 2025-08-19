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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->after('sku');
            }

            if (!Schema::hasColumn('products', 'sales_channel')) {
                $table->string('sales_channel')->nullable()->after('import_date');
            }

            if (!Schema::hasColumn('products', 'tags')) {
                $table->string('tags')->nullable()->after('sales_channel');
            }

            // Thêm các trường mới cho bộ lọc nâng cao
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->string('product_type')->nullable()->after('tags');
            }
            
            if (!Schema::hasColumn('products', 'product_form')) {
                $table->string('product_form')->nullable()->after('product_type');
            }
            
            if (!Schema::hasColumn('products', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('product_form');
            }
            
            if (!Schema::hasColumn('products', 'branch_price')) {
                $table->json('branch_price')->nullable()->after('expiry_date');
            }
            
            if (!Schema::hasColumn('products', 'customer_group_price')) {
                $table->json('customer_group_price')->nullable()->after('branch_price');
            }
            
            if (!Schema::hasColumn('products', 'created_date')) {
                $table->date('created_date')->nullable()->after('customer_group_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type', 
                'product_form', 
                'expiry_date', 
                'branch_price', 
                'customer_group_price', 
                'created_date'
            ]);
        });
    }
};
