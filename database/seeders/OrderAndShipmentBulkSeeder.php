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
            // random date from 2025-01-01 to now (yêu cầu người dùng)
            $start = now()->setTimezone(config('app.timezone'))->setDate(2025,1,1)->startOfDay();
            $randTs = rand($start->timestamp, now()->timestamp);
            $orderDate = now()->setTimestamp($randTs);

            // build simple order (khớp form tạo đơn: có customer_name, địa chỉ, city/ward, phone)
            $orderNumber = 'DH' . $orderDate->format('Ymd') . Str::upper(Str::random(4));
            $statusPool = ['confirmed','processing','shipping','delivered'];
            $status = $statusPool[array_rand($statusPool)];

            // lấy (hoặc tạo) customer ngẫu nhiên để thỏa ràng buộc customer_id và random hóa thông tin KH
            $customer = \App\Models\Customer::query()->inRandomOrder()->first();
            if (!$customer) {
                [$addr0, $ward0, $city0] = $this->randomLocation();
                $customer = \App\Models\Customer::create([
                    'name' => 'Khách ' . Str::upper(Str::random(3)),
                    'phone' => '09' . rand(10000000,99999999),
                    'address' => $addr0,
                    'ward' => $ward0,
                    'city' => $city0,
                    'is_active' => true,
                    'customer_type' => 'walkin',
                    'source' => rand(0,1) ? 'online' : 'offline',
                ]);
            }

            // random hóa thông tin khách theo yêu cầu: tên/phone có thể khác với bản ghi gốc (form cho phép nhập tự do)
            [$addr, $ward, $city] = $this->randomLocation();
            $randCustomerName = rand(0,1) ? $customer->name : ('Khách ' . Str::upper(Str::random(3)));
            $randPhone = rand(0,1) ? $customer->phone : ('09' . rand(10000000,99999999));

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'customer_name' => $randCustomerName,
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
                'delivery_address' => $addr,
                'ward' => $ward,
                'city' => $city,
                'phone' => $randPhone,
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

            // shipment close to order time (within 0-72h, đảm bảo gần ngày đơn hàng)
            $shipTs = $orderDate->timestamp + rand(0, 72*3600);
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

        $this->command?->info("Đã tạo {$createdOrders} đơn hàng và vận đơn tương ứng (thời gian gần nhau, từ 01/2025 đến nay).");
    }

    private function randomShippingFee(string $region): float
    {
        if ($region === 'HN') return (float)(rand(200, 300) * 100);
        return (float)(rand(150, 250) * 100);
    }

    private function randomLocation(): array
    {
        $cities = [
            'TP. Hồ Chí Minh' => ['Bến Nghé','Bến Thành','Cầu Kho','Cầu Ông Lãnh','Tân Định','Phú Nhuận','Hiệp Phú','Tăng Nhơn Phú A','Tăng Nhơn Phú B','Linh Trung','Linh Chiểu'],
            'Hà Nội' => ['Hàng Trống','Hàng Bạc','Hàng Buồm','Cửa Đông','Cửa Nam','Kim Liên','Khương Trung','Trung Hòa','Nhân Chính','Quan Hoa'],
            'Đà Nẵng' => ['Hải Châu 1','Hải Châu 2','Thạch Thang','Thanh Bình','Thuận Phước','An Hải Bắc','An Hải Đông','An Hải Tây'],
            'Cần Thơ' => ['Tân An','An Nghiệp','An Cư','An Phú','Xuân Khánh','Hưng Lợi'],
        ];
        $city = array_rand($cities);
        $wards = $cities[$city];
        $ward = $wards[array_rand($wards)];
        $streetNo = rand(1,999);
        $streetNamePool = ['Trần Hưng Đạo','Lê Lợi','Nguyễn Huệ','Điện Biên Phủ','Pasteur','Hai Bà Trưng','Võ Thị Sáu','Cách Mạng Tháng 8','Phan Xích Long','Phổ Quang'];
        $streetName = $streetNamePool[array_rand($streetNamePool)];
        $address = "Số {$streetNo} {$streetName}";
        return [$address, $ward, $city];
    }
}


