<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite không hỗ trợ ALTER ENUM trực tiếp, cần recreate table
        if (DB::getDriverName() === 'sqlite') {
            // Tạo bảng tạm với enum mới
            Schema::create('shipment_events_temp', function (Blueprint $table) {
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
                    'cancelled',
                ]);
                $table->timestamp('event_at')->nullable();
                $table->string('note')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['shipment_id', 'event_at']);
                $table->index(['status']);
            });

            // Copy dữ liệu từ bảng cũ
            DB::statement('INSERT INTO shipment_events_temp SELECT * FROM shipment_events');

            // Drop bảng cũ và rename bảng tạm
            Schema::dropIfExists('shipment_events');
            Schema::rename('shipment_events_temp', 'shipment_events');
        } else {
            // MySQL/PostgreSQL có thể ALTER ENUM
            DB::statement("ALTER TABLE shipment_events MODIFY COLUMN status ENUM(
                'pending_pickup',
                'picked_up', 
                'in_transit',
                'retry',
                'returning',
                'returned',
                'delivered',
                'failed',
                'cancelled'
            )");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Tạo bảng tạm với enum cũ
            Schema::create('shipment_events_temp', function (Blueprint $table) {
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

            // Copy dữ liệu từ bảng cũ (loại bỏ cancelled)
            DB::statement('INSERT INTO shipment_events_temp SELECT * FROM shipment_events WHERE status != "cancelled"');

            // Drop bảng cũ và rename bảng tạm
            Schema::dropIfExists('shipment_events');
            Schema::rename('shipment_events_temp', 'shipment_events');
        } else {
            // MySQL/PostgreSQL rollback
            DB::statement("ALTER TABLE shipment_events MODIFY COLUMN status ENUM(
                'pending_pickup',
                'picked_up', 
                'in_transit',
                'retry',
                'returning',
                'returned',
                'delivered',
                'failed'
            )");
        }
    }
};