<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra sản phẩm có tồn kho thấp';

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

        // Thông báo về sản phẩm sắp hết hàng
        if ($lowStockProducts->count() > 0) {
            $this->info('⚠️  Có ' . $lowStockProducts->count() . ' sản phẩm sắp hết hàng!');
        } else {
            $this->info('✅ Tất cả sản phẩm đều có đủ hàng trong kho.');
        }

        return 0;
    }
}











