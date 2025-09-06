<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('carrier')->nullable();
            $table->string('branch')->nullable();
            $table->string('region')->nullable();

            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('address_line')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();

            $table->enum('status', [
                'pending_pickup',
                'picked_up',
                'in_transit',
                'retry',
                'returning',
                'returned',
                'delivered',
                'failed',
            ])->default('pending_pickup');

            $table->unsignedInteger('weight_grams')->default(0);
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);

            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['branch']);
            $table->index(['region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};


