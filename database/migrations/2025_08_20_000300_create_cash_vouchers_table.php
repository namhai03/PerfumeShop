<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_code')->unique();
            $table->string('type'); // receipt, payment, transfer
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('reason')->nullable();
            $table->string('payer_group')->nullable(); // customer, supplier, employee, other
            $table->string('payer_name')->nullable();
            $table->unsignedBigInteger('payer_id')->nullable(); // customer_id, supplier_id, etc.
            $table->string('payer_type')->nullable(); // customer, supplier, employee, other
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('transaction_date');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending, approved, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('from_account_id')->references('id')->on('cash_accounts')->nullOnDelete();
            $table->foreign('to_account_id')->references('id')->on('cash_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_vouchers');
    }
};
