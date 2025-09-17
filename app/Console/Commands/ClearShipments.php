<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearShipments extends Command
{
    protected $signature = 'shipments:clear {--force : Bỏ xác nhận}';

    protected $description = 'Xóa toàn bộ dữ liệu vận đơn (shipments, shipment_events, shipment_orders)';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Xác nhận xóa toàn bộ dữ liệu vận đơn?')) {
                $this->info('Đã hủy.');
                return self::SUCCESS;
            }
        }

        DB::beginTransaction();
        try {
            DB::table('shipment_events')->delete();
            if (DB::getSchemaBuilder()->hasTable('shipment_orders')) {
                DB::table('shipment_orders')->delete();
            }
            DB::table('shipments')->delete();
            DB::commit();
            $this->info('Đã xóa tất cả dữ liệu vận đơn.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Lỗi: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}


