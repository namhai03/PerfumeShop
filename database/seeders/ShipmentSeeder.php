<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\ShipmentEvent;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::orderBy('created_at','desc')->get();
        if ($orders->isEmpty()) {
            $this->command?->warn('Không có đơn hàng để tạo vận đơn. Hãy seed Order trước.');
            return;
        }

        // Tạo 20 vận đơn, mỗi vận đơn gắn đúng 1 đơn hàng
        for ($i=1; $i<=20; $i++) {
            $order = $orders->random();
            $statusPool = ['pending_pickup','picked_up','in_transit','delivered'];
            $status = $statusPool[array_rand($statusPool)];

            // thời điểm vận đơn gần ngày đơn hàng: trong vòng 0-72 giờ từ order_date (hoặc created_at nếu thiếu)
            $baseTime = $order->order_date ? \Carbon\Carbon::parse($order->order_date) : ($order->created_at ?? now());
            $shipTime = (clone $baseTime)->addSeconds(rand(0, 72*3600));

            $shipment = Shipment::create([
                'order_code' => $order->order_number,
                'tracking_code' => 'AUTO-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'carrier' => ['GHN','GHTK','VTP','Nội bộ'][array_rand(['GHN','GHTK','VTP','Nội bộ'])],
                'branch' => 'Cửa hàng chính',
                'region' => ($order->city ?? '') === 'Hà Nội' ? 'HN' : 'HCM',
                'recipient_name' => $order->customer_name ?? 'Khách lẻ',
                'recipient_phone' => $order->phone ?? ('09' . rand(10000000,99999999)),
                'address_line' => $order->delivery_address ?? 'Địa chỉ bất kỳ',
                'province' => $order->city ?? 'TP. Hồ Chí Minh',
                'ward' => $order->ward ?? 'Phường 1',
                'status' => $status,
                // Giá trị vận đơn = tiền đơn hàng (COD)
                'cod_amount' => (float)($order->final_amount ?? 0),
                // Phí vận chuyển mẫu theo khu vực
                'shipping_fee' => $this->randomShippingFee(($order->city ?? '') === 'Hà Nội' ? 'HN' : 'HCM'),
                'weight_grams' => rand(200, 1500),
                'created_at' => $shipTime,
                'updated_at' => $shipTime,
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $status,
                'event_at' => $shipTime,
                'note' => 'Seeded',
            ]);
        }

        $this->command?->info('Đã tạo 20 vận đơn mẫu (mỗi vận đơn 1 đơn hàng).');
    }

    private function randomShippingFee(string $region): float
    {
        if ($region === 'HN') {
            return (float) (rand(200, 300) * 100); // 20,000 - 30,000
        }
        return (float) (rand(150, 250) * 100); // 15,000 - 25,000
    }
}


