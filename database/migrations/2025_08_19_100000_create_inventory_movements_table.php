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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('type'); // import, export, adjust, stocktake, return, damage
            $table->integer('quantity_change'); // có thể âm/dương
            $table->integer('before_stock');
            $table->integer('after_stock');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};


