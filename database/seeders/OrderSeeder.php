<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách sản phẩm và nhóm KH; tạo một vài khách nếu chưa có
        $products = Product::where('is_active', true)->get();
        if ($products->isEmpty()) {
            $this->command->warn('Không có sản phẩm nào. Vui lòng chạy ProductSeeder trước.');
            return;
        }

        // Đảm bảo có đủ khách hàng, và có thể tạo thêm trong lúc sinh đơn
        $targetCustomers = 40;
        $current = Customer::count();
        if ($current < $targetCustomers) {
            for ($i=$current+1; $i<=$targetCustomers; $i++) {
                Customer::create([
                    'name' => self::fakeVietnameseName($i),
                    'phone' => '09' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'address' => 'Số ' . rand(1,999) . ' Đường '. chr(64+($i%26 ?: 1)) . ', TP.HCM',
                    'customer_type' => 'walkin',
                    'source' => rand(0,1) ? 'online' : 'offline',
                    'is_active' => true,
                ]);
            }
        }
        $customers = Customer::all();
        $groups = CustomerGroup::where('is_active', true)->get();

        // Trạng thái theo UI hiện tại
        $possibleStatuses = ['draft','confirmed','processing','shipping','delivered','failed','returned'];
        $paymentMethods = ['cash','bank_transfer','credit_card','other'];
        $deliveryAddresses = [
            '123 Đường ABC, Quận 1, TP.HCM',
            '456 Đường XYZ, Quận 2, TP.HCM',
            '789 Đường DEF, Quận 3, TP.HCM',
            '321 Đường GHI, Quận 4, TP.HCM',
            '654 Đường JKL, Quận 5, TP.HCM'
        ];

        DB::beginTransaction();

        try {
            $total = (int) (env('ORDER_SEED_COUNT', 50));
            for ($i = 1; $i <= $total; $i++) {
                // Chọn ngẫu nhiên khách hàng và sản phẩm
                // 30% cơ hội tạo khách mới khi sinh đơn
                if (rand(1,100) <= 30) {
                    $new = Customer::create([
                        'name' => self::fakeVietnameseName($i) . ' ' . Str::upper(Str::random(2)),
                        'phone' => '09' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'address' => 'Số ' . rand(1,999) . ' Đường '. chr(64+($i%26 ?: 1)) . ', Hà Nội',
                        'customer_type' => 'walkin',
                        'source' => rand(0,1) ? 'online' : 'offline',
                        'is_active' => true,
                    ]);
                    $customer = $new;
                    // refresh tập khách
                    $customers = Customer::all();
                } else {
                    $customer = $customers->random();
                }
                // Chọn ngẫu nhiên trạng thái và map sang type giống controller
                $status = $possibleStatuses[array_rand($possibleStatuses)];
                $mappedType = match($status) {
                    'draft' => 'draft',
                    'failed', 'returned' => 'return',
                    default => 'sale',
                };
                
                // Tạo số đơn hàng
                $orderNumber = 'DH' . date('Ymd') . Str::upper(Str::random(4));
                
                // Tạo ngày đơn hàng (trong vòng 30 ngày gần đây)
                $orderDate = now()->subDays(rand(0, 30));
                
                // Tạo đơn hàng
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'status' => $status,
                    'type' => $mappedType,
                    'total_amount' => 0, // Sẽ tính sau
                    'discount_amount' => 0, // tính dưới
                    'final_amount' => 0, // Sẽ tính sau
                    'notes' => $this->getRandomNotes($mappedType),
                    'order_date' => $orderDate,
                    'delivery_date' => $mappedType !== 'draft' ? (clone $orderDate)->addDays(rand(1, 7)) : null,
                    'payment_method' => in_array($status, ['confirmed','processing','shipping','delivered']) ? $paymentMethods[array_rand($paymentMethods)] : null,
                    'delivery_address' => $deliveryAddresses[array_rand($deliveryAddresses)],
                    'phone' => $customer->phone,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Tạo chi tiết đơn hàng (1-3 sản phẩm mỗi đơn)
                $itemCount = rand(1, 3);
                $totalAmount = 0;
                $selectedProducts = $products->random($itemCount);

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $unitPrice = $product->selling_price;
                    $totalPrice = $quantity * $unitPrice;
                    $totalAmount += $totalPrice;

                    // Tạo chi tiết đơn hàng
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'custom_notes' => rand(0, 1) ? 'Ghi chú đặc biệt cho sản phẩm' : null,
                    ]);

                    // Cập nhật tồn kho nếu là đơn bán hoặc trả hàng
                    if ($mappedType !== 'draft') {
                        $this->updateInventoryForOrder($order, $product, $quantity);
                    }
                }

                // Tính chiết khấu nhóm (giống controller) + giảm thủ công ngẫu nhiên
                $groupDiscount = 0;
                $appliedGroupId = null;
                if ($groups->isNotEmpty() && rand(0,1)) {
                    $group = $groups->random();
                    $appliedGroupId = $group->id;
                    if (!$group->min_order_amount || $totalAmount >= (float)$group->min_order_amount) {
                        $rate = (float)($group->discount_rate ?? 0);
                        if ($rate > 0) {
                            $groupDiscount = round($totalAmount * ($rate / 100), 2);
                            if ($group->max_discount_amount) {
                                $groupDiscount = min($groupDiscount, (float)$group->max_discount_amount);
                            }
                        }
                    }
                }

                $manualDiscount = rand(0, 1) ? rand(0, 100000) : 0;
                $discountAmount = $groupDiscount + $manualDiscount;
                $finalAmount = $totalAmount - $discountAmount;

                $order->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $finalAmount,
                    'customer_group_id' => $appliedGroupId,
                ]);

                // Cập nhật thống kê khách hàng
                if ($mappedType === 'sale') {
                    $customer->increment('total_orders');
                    $customer->increment('total_spent', $finalAmount);
                }
            }

            DB::commit();
            $this->command->info('Đã tạo thành công ' . $total . ' đơn hàng mẫu!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Lỗi khi tạo đơn hàng mẫu: ' . $e->getMessage());
        }
    }

    private function getRandomNotes($orderType)
    {
        $notes = [
            'sale' => [
                'Khách hàng VIP',
                'Đơn hàng khuyến mãi',
                'Giao hàng nhanh',
                'Khách hàng thân thiết',
                'Đơn hàng đặc biệt'
            ],
            'return' => [
                'Sản phẩm lỗi',
                'Khách hàng không hài lòng',
                'Đổi size',
                'Hàng bị hỏng trong quá trình vận chuyển',
                'Khách hàng yêu cầu trả hàng'
            ],
            'draft' => [
                'Đơn hàng tạm thời',
                'Chờ xác nhận',
                'Đơn hàng dự thảo',
                'Chờ thanh toán',
                'Đơn hàng chưa hoàn thiện'
            ]
        ];

        $typeNotes = $notes[$orderType] ?? ['Ghi chú mặc định'];
        return $typeNotes[array_rand($typeNotes)];
    }

    private function updateInventoryForOrder($order, $product, $quantity)
    {
        $beforeStock = $product->stock;
        
        if ($order->type === 'sale') {
            // Đơn bán: giảm tồn kho
            $afterStock = $beforeStock - $quantity;
            $quantityChange = -$quantity;
            $movementType = 'export';
        } else {
            // Đơn trả: tăng tồn kho
            $afterStock = $beforeStock + $quantity;
            $quantityChange = $quantity;
            $movementType = 'return';
        }

        // Cập nhật tồn kho sản phẩm
        $product->update(['stock' => $afterStock]);

        // Tạo bản ghi chuyển động kho
        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $movementType,
            'quantity_change' => $quantityChange,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'performed_by' => null,
            'note' => "Đơn hàng {$order->order_number} - {$order->type_text}",
            'transaction_date' => $order->created_at,
            'reference_id' => $order->order_number,
            'order_id' => $order->id,
        ]);
    }

    private static function fakeVietnameseName(int $seed = 0): string
    {
        $lastNames = ['Nguyễn','Trần','Lê','Phạm','Hoàng','Huỳnh','Phan','Vũ','Võ','Đặng','Bùi','Đỗ','Hồ','Ngô','Dương','Lý'];
        $middle = ['Văn','Thị','Hồng','Minh','Quốc','Thế','Anh','Ngọc','Gia','Nhật','Bảo','Thanh'];
        $first = ['An','Bình','Châu','Dũng','Duy','Giang','Hà','Hải','Hạnh','Hiếu','Huy','Khanh','Khôi','Lan','Linh','Long','Mai','My','Nam','Ngân','Nhung','Phương','Quân','Quỳnh','Sơn','Tâm','Trang','Trung','Tuấn','Vy'];
        return $lastNames[array_rand($lastNames)] . ' ' . $middle[array_rand($middle)] . ' ' . $first[array_rand($first)];
    }
}
