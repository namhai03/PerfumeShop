<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearSampleOrders extends Command
{
    protected $signature = 'orders:clear-samples {--force : Bỏ qua kiểm tra âm kho khi hoàn tác đơn trả} {--partial : Xóa những đơn có thể hoàn tác (bỏ qua đơn không đủ kho)}';

    protected $description = 'Xóa toàn bộ đơn hàng (mẫu) và hoàn tác tồn kho an toàn';

    public function handle(): int
    {
        $this->warn('Thao tác sẽ xóa tất cả đơn hàng và hoàn tác tồn kho liên quan.');

        if ($this->option('partial')) {
            $this->info('Chế độ partial: chỉ xóa các đơn có thể hoàn tác kho an toàn.');
            $orders = Order::with('items')->get();
            $deleted = 0; $skipped = 0;
            foreach ($orders as $order) {
                DB::beginTransaction();
                try {
                    $mvs = InventoryMovement::where('order_id', $order->id)->orderBy('id','desc')->get();
                    $ok = true;
                    // kiểm tra trước
                    foreach ($mvs as $mv) {
                        $product = Product::find($mv->product_id);
                        if (!$product) continue;
                        $revert = (int)$mv->quantity_change;
                        $newStock = (int)$product->stock - $revert;
                        if ($newStock < 0 && !$this->option('force')) { $ok = false; break; }
                    }
                    if (!$ok) {
                        DB::rollBack();
                        $skipped++;
                        continue;
                    }
                    // áp dụng revert
                    foreach ($mvs as $mv) {
                        $product = Product::find($mv->product_id);
                        if (!$product) continue;
                        $revert = (int)$mv->quantity_change;
                        $newStock = max(0, (int)$product->stock - $revert);
                        $product->update(['stock' => $newStock]);
                    }
                    InventoryMovement::where('order_id', $order->id)->delete();
                    OrderItem::where('order_id', $order->id)->delete();
                    $order->delete();
                    DB::commit();
                    $deleted++;
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $skipped++;
                }
            }
            // Recalc customer stats
            $this->recalculateCustomerStats();
            $this->info("Đã xóa {$deleted} đơn. Bỏ qua {$skipped} đơn vì không đủ kho.");
            return 0;
        }

        DB::beginTransaction();
        try {
            // Hoàn tác tất cả một lượt (có thể dừng nếu âm kho và không --force)
            $movements = InventoryMovement::whereNotNull('order_id')->orderBy('id', 'desc')->get();
            $this->info('Đang hoàn tác tồn kho từ ' . $movements->count() . ' chuyển động kho...');
            foreach ($movements as $mv) {
                $product = Product::find($mv->product_id);
                if (!$product) { continue; }
                $revert = (int)$mv->quantity_change;
                $newStock = (int)$product->stock - $revert;
                if ($newStock < 0 && !$this->option('force')) {
                    DB::rollBack();
                    $this->error("Dừng: Hoàn tác chuyển động #{$mv->id} sẽ làm tồn kho âm cho sản phẩm ID {$product->id} ({$product->sku}). Dùng --force để bỏ qua.");
                    return 1;
                }
                $product->update(['stock' => max(0, $newStock)]);
            }
            InventoryMovement::whereNotNull('order_id')->delete();
            OrderItem::query()->delete();
            Order::query()->delete();
            Customer::query()->update(['total_orders' => 0, 'total_spent' => 0]);
            DB::commit();
            $this->info('Đã xóa tất cả đơn hàng và hoàn tác tồn kho thành công.');
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }

    private function recalculateCustomerStats(): void
    {
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $orders = Order::where('customer_id', $customer->id)->get();
            $customer->update([
                'total_orders' => $orders->count(),
                'total_spent' => $orders->sum('final_amount'),
            ]);
        }
    }
}


