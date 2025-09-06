<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SyncOrderCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ khách hàng cho các đơn hàng cũ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu đồng bộ khách hàng cho các đơn hàng...');
        
        $orders = Order::whereNull('customer_id')
            ->whereNotNull('customer_name')
            ->get();
        
        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();
        
        $created = 0;
        $updated = 0;
        
        foreach ($orders as $order) {
            // Tìm khách hàng theo tên và số điện thoại
            $customer = Customer::where('name', $order->customer_name)
                ->where('phone', $order->phone)
                ->first();
            
            if (!$customer) {
                // Tạo khách hàng mới
                $customer = Customer::create([
                    'name' => $order->customer_name,
                    'phone' => $order->phone,
                    'address' => $order->delivery_address,
                    'customer_type' => 'walkin',
                    'source' => 'offline',
                    'is_active' => true,
                ]);
                $created++;
            } else {
                // Cập nhật thông tin khách hàng nếu cần
                $updateData = [];
                if ($order->phone && $customer->phone !== $order->phone) {
                    $updateData['phone'] = $order->phone;
                }
                if ($order->delivery_address && $customer->address !== $order->delivery_address) {
                    $updateData['address'] = $order->delivery_address;
                }
                if (!empty($updateData)) {
                    $customer->update($updateData);
                    $updated++;
                }
            }
            
            // Cập nhật đơn hàng với customer_id
            $order->update(['customer_id' => $customer->id]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Hoàn thành! Đã tạo {$created} khách hàng mới và cập nhật {$updated} khách hàng.");
        
        // Cập nhật thống kê khách hàng
        $this->info('Đang cập nhật thống kê khách hàng...');
        $this->updateCustomerStatistics();
        
        $this->info('Đồng bộ hoàn tất!');
    }
    
    private function updateCustomerStatistics()
    {
        $customers = Customer::all();
        
        foreach ($customers as $customer) {
            $orders = Order::where('customer_id', $customer->id)->get();
            
            $totalOrders = $orders->count();
            $totalSpent = $orders->sum('final_amount');
            
            $customer->update([
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
            ]);
        }
    }
}
