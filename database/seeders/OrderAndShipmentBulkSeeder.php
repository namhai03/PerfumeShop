<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentEvent;

class OrderAndShipmentBulkSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $this->command?->warn('Không có sản phẩm để tạo đơn. Hãy seed Product trước.');
            return;
        }

        $createdOrders = 0;
        for ($i=1; $i<=200; $i++) {
            // random date from 2024-01-01 to now
            $start = now()->setTimezone(config('app.timezone'))->setDate(2024,1,1)->startOfDay();
            $randTs = rand($start->timestamp, now()->timestamp);
            $orderDate = now()->setTimestamp($randTs);

            // build simple order
            $orderNumber = 'DH' . $orderDate->format('Ymd') . Str::upper(Str::random(4));
            $statusPool = ['confirmed','processing','shipping','delivered'];
            $status = $statusPool[array_rand($statusPool)];

            // lấy (hoặc tạo) customer tạm để thỏa ràng buộc customer_id
            $customerId = \App\Models\Customer::query()->inRandomOrder()->value('id');
            if (!$customerId) {
                $customer = \App\Models\Customer::create([
                    'name' => 'Khách Tạm',
                    'phone' => '09' . rand(10000000,99999999),
                    'address' => 'Địa chỉ tạm',
                    'is_active' => true,
                ]);
                $customerId = $customer->id;
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'customer_name' => 'Khách ' . Str::upper(Str::random(3)),
                'status' => match($status){
                    'confirmed' => 'confirmed',
                    'processing' => 'processing',
                    'shipping' => 'shipping',
                    'delivered' => 'delivered',
                    default => 'confirmed',
                },
                'type' => 'sale',
                'total_amount' => 0,
                'discount_amount' => 0,
                'final_amount' => 0,
                'order_date' => $orderDate->toDateString(),
                'delivery_date' => $orderDate->copy()->addDays(rand(0,5))->toDateString(),
                'payment_method' => 'cash',
                'delivery_address' => 'Số '.rand(1,999).' Đường A',
                'ward' => 'Phường '.rand(1,15),
                'city' => rand(0,1) ? 'TP. Hồ Chí Minh' : 'Hà Nội',
                'phone' => '09' . rand(10000000,99999999),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // items 1-2
            $itemCount = rand(1,2);
            $selected = $itemCount > 1 ? $products->random($itemCount) : collect([$products->random()]);
            $total = 0;
            foreach ($selected as $p) {
                $qty = rand(1,2);
                $price = $p->selling_price;
                $total += $qty * $price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $qty * $price,
                ]);
            }
            $order->update([
                'total_amount' => $total,
                'final_amount' => $total,
            ]);

            // shipment close to order time (within 0-24h)
            $shipTs = $orderDate->timestamp + rand(0, 24*3600);
            $shipTime = now()->setTimestamp($shipTs);
            $shipStatusPool = ['pending_pickup','picked_up','in_transit','delivered'];
            $shipStatus = $shipStatusPool[array_rand($shipStatusPool)];

            $shipment = Shipment::create([
                'order_code' => $order->order_number,
                'tracking_code' => 'AUTO-' . $shipTime->format('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'carrier' => ['GHN','GHTK','VTP','Nội bộ'][array_rand(['GHN','GHTK','VTP','Nội bộ'])],
                'branch' => 'Cửa hàng chính',
                'region' => $order->city === 'Hà Nội' ? 'HN' : 'HCM',
                'recipient_name' => $order->customer_name,
                'recipient_phone' => $order->phone,
                'address_line' => $order->delivery_address,
                'province' => $order->city,
                'ward' => $order->ward,
                'status' => $shipStatus,
                'cod_amount' => (float)$order->final_amount,
                'shipping_fee' => $this->randomShippingFee($order->city === 'Hà Nội' ? 'HN' : 'HCM'),
                'weight_grams' => rand(300, 2000),
                'created_at' => $shipTime,
                'updated_at' => $shipTime,
            ]);
            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $shipStatus,
                'event_at' => $shipTime,
                'note' => 'Bulk seeded',
            ]);
            $createdOrders++;
        }

        $this->command?->info("Đã tạo {$createdOrders} đơn hàng và vận đơn tương ứng (thời gian gần nhau, từ 01/2024 đến nay).");
    }

    private function randomShippingFee(string $region): float
    {
        if ($region === 'HN') return (float)(rand(200, 300) * 100);
        return (float)(rand(150, 250) * 100);
    }
}


