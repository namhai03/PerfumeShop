<?php
/**
 * Script test bộ lọc lịch sử kho mới
 * Chạy: php test_inventory_filters.php
 */

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\InventoryMovement;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST BỘ LỌC LỊCH SỬ KHO MỚI ===\n\n";

// 1. Tạo dữ liệu test
echo "1. Tạo dữ liệu test...\n";

$product = Product::first();
if (!$product) {
    echo "Không tìm thấy sản phẩm nào. Vui lòng chạy seeder trước.\n";
    exit(1);
}

echo "Sản phẩm: {$product->name} (SKU: {$product->sku})\n\n";

// 2. Tạo các giao dịch test với thời gian khác nhau
echo "2. Tạo các giao dịch test...\n";

$testMovements = [
    [
        'type' => 'import',
        'quantity_change' => 100,
        'note' => 'Nhập hàng từ nhà cung cấp ABC Perfume',
        'supplier' => 'ABC Perfume',
        'unit_cost' => 150000,
        'reference_id' => 'PO-2024-001',
        'transaction_date' => now()->subDays(1) // Hôm qua
    ],
    [
        'type' => 'export',
        'quantity_change' => -5,
        'note' => 'Bán hàng cho khách VIP',
        'supplier' => null,
        'unit_cost' => null,
        'reference_id' => 'SO-2024-001',
        'transaction_date' => now()->subHours(2) // Hôm nay
    ],
    [
        'type' => 'adjust',
        'quantity_change' => 2,
        'note' => 'Điều chỉnh do kiểm kê',
        'supplier' => null,
        'unit_cost' => null,
        'reference_id' => 'ADJ-2024-001',
        'transaction_date' => now()->subDays(7) // Tuần trước
    ],
    [
        'type' => 'damage',
        'quantity_change' => -1,
        'note' => 'Hàng hỏng do vận chuyển',
        'supplier' => 'ABC Perfume',
        'unit_cost' => null,
        'reference_id' => 'DMG-2024-001',
        'transaction_date' => now()->subDays(15) // 2 tuần trước
    ],
    [
        'type' => 'return',
        'quantity_change' => 3,
        'note' => 'Khách hàng trả hàng',
        'supplier' => null,
        'unit_cost' => null,
        'reference_id' => 'RTN-2024-001',
        'transaction_date' => now()->subDays(30) // Tháng trước
    ]
];

foreach ($testMovements as $index => $movementData) {
    $movementData['product_id'] = $product->id;
    $movementData['before_stock'] = $product->stock;
    $movementData['after_stock'] = $product->stock + $movementData['quantity_change'];
    $movementData['performed_by'] = null;
    
    $movement = InventoryMovement::create($movementData);
    $product->update(['stock' => $movementData['after_stock']]);
    
    echo "  - {$movement->type_text}: {$movement->quantity_change_formatted} ({$movement->transaction_date_formatted})\n";
}

echo "\nTồn kho hiện tại: {$product->stock}\n\n";

// 3. Test các bộ lọc
echo "3. Test các bộ lọc...\n";

// Test lọc theo loại
$importMovements = InventoryMovement::byProduct($product->id)->byType('import')->count();
echo "Giao dịch nhập kho: {$importMovements}\n";

// Test lọc theo thời gian
$todayMovements = InventoryMovement::byProduct($product->id)
    ->whereDate('transaction_date', now())
    ->count();
echo "Giao dịch hôm nay: {$todayMovements}\n";

$yesterdayMovements = InventoryMovement::byProduct($product->id)
    ->whereDate('transaction_date', now()->subDay())
    ->count();
echo "Giao dịch hôm qua: {$yesterdayMovements}\n";

// Test lọc theo nhà cung cấp
$supplierMovements = InventoryMovement::byProduct($product->id)
    ->where('supplier', 'like', '%ABC%')
    ->count();
echo "Giao dịch từ nhà cung cấp ABC: {$supplierMovements}\n";

// Test lọc theo mã tham chiếu
$referenceMovements = InventoryMovement::byProduct($product->id)
    ->where('reference_id', 'like', '%2024%')
    ->count();
echo "Giao dịch có mã 2024: {$referenceMovements}\n";

// Test lọc tăng/giảm
$increaseMovements = InventoryMovement::byProduct($product->id)->increases()->count();
$decreaseMovements = InventoryMovement::byProduct($product->id)->decreases()->count();
echo "Giao dịch tăng kho: {$increaseMovements}\n";
echo "Giao dịch giảm kho: {$decreaseMovements}\n\n";

// 4. Test thống kê
echo "4. Test thống kê...\n";
$stats = InventoryMovement::getMovementStats($product->id);
echo "Tổng giao dịch: {$stats['total_movements']}\n";
echo "Tổng tăng: {$stats['total_increases']}\n";
echo "Tổng giảm: {$stats['total_decreases']}\n";
echo "Thay đổi ròng: {$stats['net_change']}\n";
echo "Theo loại: " . json_encode($stats['by_type'], JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== HOÀN THÀNH TEST ===\n";
echo "Bạn có thể truy cập: /inventory/{$product->id} để xem giao diện mới\n";
echo "Các tính năng mới:\n";
echo "- Bỏ tickbox ở trước mỗi dòng\n";
echo "- Bộ lọc thời gian: Hôm nay, Hôm qua, Tuần này, Tháng này, Tùy chỉnh\n";
echo "- Bộ lọc nâng cao: Nhà cung cấp, Mã tham chiếu\n";
echo "- Giao diện đẹp như trong ảnh\n";
