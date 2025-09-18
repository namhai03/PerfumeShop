<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipment;
use App\Models\Order;
use App\Models\ShipmentEvent;
use Carbon\Carbon;

class DeliveredShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::orderBy('created_at','desc')->get();
        if ($orders->isEmpty()) {
            $this->command?->warn('Không có đơn hàng để tạo vận đơn. Hãy seed Order trước.');
            return;
        }

        // Tạo 50 vận đơn với trạng thái "delivered" từ 1/6 đến hiện tại
        $startDate = Carbon::parse('2024-06-01');
        $endDate = now();

        for ($i = 1; $i <= 50; $i++) {
            $order = $orders->random();
            
            // Tạo ngày ngẫu nhiên từ 1/6 đến hiện tại
            $randomDays = rand(0, $startDate->diffInDays($endDate));
            $createdAt = $startDate->copy()->addDays($randomDays);
            
            // Thời gian giao hàng: 1-5 ngày sau khi tạo vận đơn
            $deliveredAt = $createdAt->copy()->addDays(rand(1, 5));
            
            // Thời gian lấy hàng: 0-1 ngày sau khi tạo
            $pickedUpAt = $createdAt->copy()->addHours(rand(0, 24));

            $shipment = Shipment::create([
                'order_code' => $order->order_number,
                'tracking_code' => 'DEL-' . date('ymd', $createdAt->timestamp) . '-' . strtoupper(bin2hex(random_bytes(3))),
                'carrier' => ['GHN','GHTK','VTP','Nội bộ','Viettel Post'][array_rand(['GHN','GHTK','VTP','Nội bộ','Viettel Post'])],
                'branch' => 'Cửa hàng chính',
                'region' => ($order->city ?? '') === 'Hà Nội' ? 'HN' : 'HCM',
                'recipient_name' => $order->customer_name ?? 'Khách lẻ',
                'recipient_phone' => $order->phone ?? ('09' . rand(10000000,99999999)),
                'address_line' => $order->delivery_address ?? 'Địa chỉ bất kỳ',
                'province' => $order->city ?? 'TP. Hồ Chí Minh',
                'ward' => $order->ward ?? 'Phường 1',
                'status' => 'delivered',
                'cod_amount' => (float)($order->final_amount ?? rand(100000, 2000000)),
                'shipping_fee' => $this->randomShippingFee(($order->city ?? '') === 'Hà Nội' ? 'HN' : 'HCM'),
                'weight_grams' => rand(200, 1500),
                'created_at' => $createdAt,
                'updated_at' => $deliveredAt,
                'picked_up_at' => $pickedUpAt,
                'delivered_at' => $deliveredAt,
            ]);

            // Tạo các sự kiện vận đơn
            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'pending_pickup',
                'event_at' => $createdAt,
                'note' => 'Tạo vận đơn',
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'picked_up',
                'event_at' => $pickedUpAt,
                'note' => 'Đã lấy hàng',
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'in_transit',
                'event_at' => $pickedUpAt->copy()->addHours(rand(1, 12)),
                'note' => 'Đang giao hàng',
            ]);

            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'delivered',
                'event_at' => $deliveredAt,
                'note' => 'Giao hàng thành công',
            ]);
        }

        $this->command?->info('Đã tạo 50 vận đơn với trạng thái "delivered" từ 1/6 đến hiện tại.');
    }

    private function randomShippingFee(string $region): float
    {
        if ($region === 'HN') {
            return (float) (rand(200, 300) * 100); // 20,000 - 30,000
        }
        return (float) (rand(150, 250) * 100); // 15,000 - 25,000
    }
}
