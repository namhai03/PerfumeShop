<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('gender')->nullable(); // male, female, other
            $table->date('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('customer_type')->nullable(); // vip, frequent, walkin, ...
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->string('source')->nullable(); // online/offline, tiktok, shopee, ...
            $table->string('tax_number')->nullable();
            $table->string('company')->nullable();
            $table->text('note')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};


