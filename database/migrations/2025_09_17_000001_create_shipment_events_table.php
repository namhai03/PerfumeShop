<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->enum('status', [
                'pending_pickup',
                'picked_up',
                'in_transit',
                'retry',
                'returning',
                'returned',
                'delivered',
                'failed',
            ]);
            $table->timestamp('event_at')->nullable();
            $table->string('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'event_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
    }
};


