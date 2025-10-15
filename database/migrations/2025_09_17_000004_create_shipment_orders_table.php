<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->string('order_number')->index();
            $table->timestamps();
            $table->unique(['shipment_id','order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_orders');
    }
};


