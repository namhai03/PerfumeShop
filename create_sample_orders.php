<?php

require_once 'vendor/autoload.php';

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Bắt đầu tạo 10 đơn hàng mẫu...\n";

// Lấy danh sách sản phẩm và khách hàng
$products = Product::where('is_active', true)->get();
$customers = Customer::all();

if ($products->isEmpty() || $customers->isEmpty()) {
    echo "Lỗi: Không có sản phẩm hoặc khách hàng nào. Vui lòng chạy ProductSeeder và CustomerSeeder trước.\n";
    exit(1);
}

echo "Tìm thấy {$products->count()} sản phẩm và {$customers->count()} khách hàng.\n";

$orderTypes = ['sale', 'return', 'draft'];
$statuses = ['paid', 'unpaid'];
$paymentMethods = ['Tiền mặt', 'Chuyển khoản', 'Thẻ tín dụng'];
$deliveryAddresses = [
    '123 Đường ABC, Quận 1, TP.HCM',
    '456 Đường XYZ, Quận 2, TP.HCM',
    '789 Đường DEF, Quận 3, TP.HCM',
    '321 Đường GHI, Quận 4, TP.HCM',
    '654 Đường JKL, Quận 5, TP.HCM'
];

DB::beginTransaction();

try {
    for ($i = 1; $i <= 10; $i++) {
        // Chọn ngẫu nhiên khách hàng và sản phẩm
        $customer = $customers->random();
        $orderType = $orderTypes[array_rand($orderTypes)];
        $status = $statuses[array_rand($statuses)];
        
        // Tạo số đơn hàng
        $orderNumber = 'DH' . date('Ymd') . strtoupper(substr(md5($i . time()), 0, 4));
        
        // Tạo ngày đơn hàng (trong vòng 30 ngày gần đây)
        $orderDate = now()->subDays(rand(0, 30));
        
        // Tạo đơn hàng
        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'status' => $status,
            'type' => $orderType,
            'total_amount' => 0, // Sẽ tính sau
            'discount_amount' => rand(0, 100000),
            'final_amount' => 0, // Sẽ tính sau
            'notes' => getRandomNotes($orderType),
            'order_date' => $orderDate,
            'delivery_date' => $orderType !== 'draft' ? $orderDate->addDays(rand(1, 7)) : null,
            'payment_method' => $status === 'paid' ? $paymentMethods[array_rand($paymentMethods)] : null,
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
            if ($orderType !== 'draft') {
                updateInventoryForOrder($order, $product, $quantity);
            }
        }

        // Cập nhật tổng tiền đơn hàng
        $discountAmount = $order->discount_amount;
        $finalAmount = $totalAmount - $discountAmount;

        $order->update([
            'total_amount' => $totalAmount,
            'final_amount' => $finalAmount,
        ]);

        // Cập nhật thống kê khách hàng
        if ($orderType === 'sale') {
            $customer->increment('total_orders');
            $customer->increment('total_spent', $finalAmount);
        }

        echo "Đã tạo đơn hàng {$i}/10: {$orderNumber}\n";
    }

    DB::commit();
    echo "Đã tạo thành công 10 đơn hàng mẫu!\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "Lỗi khi tạo đơn hàng mẫu: " . $e->getMessage() . "\n";
    exit(1);
}

function getRandomNotes($orderType)
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

function updateInventoryForOrder($order, $product, $quantity)
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
