<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\N8nService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock {--send-alert : Gửi thông báo qua n8n}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra sản phẩm có tồn kho thấp và gửi thông báo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra tồn kho thấp...');

        // Lấy danh sách sản phẩm có tồn kho thấp
        $lowStockProducts = Product::where('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'sku',
                'stock',
                'low_stock_threshold',
                'selling_price',
                'category'
            ])
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('Không có sản phẩm nào có tồn kho thấp.');
            return 0;
        }

        $this->info("Tìm thấy {$lowStockProducts->count()} sản phẩm có tồn kho thấp:");

        // Hiển thị danh sách sản phẩm
        $headers = ['ID', 'Tên sản phẩm', 'SKU', 'Tồn kho', 'Ngưỡng cảnh báo', 'Giá bán'];
        $rows = [];

        foreach ($lowStockProducts as $product) {
            $rows[] = [
                $product->id,
                $product->name,
                $product->sku,
                $product->stock,
                $product->low_stock_threshold,
                number_format($product->selling_price, 0, ',', '.') . ' VNĐ'
            ];
        }

        $this->table($headers, $rows);

        // Gửi thông báo qua n8n nếu có option
        if ($this->option('send-alert')) {
            $this->info('Đang gửi thông báo qua n8n...');
            
            $n8nService = new N8nService();
            $success = $n8nService->sendLowStockAlert($lowStockProducts->toArray());

            if ($success) {
                $this->info('✅ Thông báo đã được gửi thành công qua n8n!');
                Log::info('Low stock alert sent via n8n', [
                    'products_count' => $lowStockProducts->count(),
                    'products' => $lowStockProducts->pluck('name')->toArray()
                ]);
            } else {
                $this->error('❌ Không thể gửi thông báo qua n8n. Vui lòng kiểm tra log.');
            }
        } else {
            $this->info('💡 Sử dụng --send-alert để gửi thông báo qua n8n');
        }

        return 0;
    }
}





