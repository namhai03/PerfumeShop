<?php
/**
 * Script test tính năng lịch sử kho
 * Chạy: php test_inventory_history.php
 */

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\InventoryMovement;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST TÍNH NĂNG LỊCH SỬ KHO ===\n\n";

// 1. Tạo dữ liệu test
echo "1. Tạo dữ liệu test...\n";

$product = Product::first();
if (!$product) {
    echo "Không tìm thấy sản phẩm nào. Vui lòng chạy seeder trước.\n";
    exit(1);
}

echo "Sản phẩm: {$product->name} (SKU: {$product->sku})\n";
echo "Tồn kho hiện tại: {$product->stock}\n\n";

// 2. Tạo các giao dịch test
echo "2. Tạo các giao dịch test...\n";

$movements = [
    [
        'type' => 'import',
        'quantity_change' => 50,
        'note' => 'Nhập hàng từ nhà cung cấp ABC',
        'supplier' => 'ABC Perfume',
        'unit_cost' => 150000,
        'reference_id' => 'PO-2024-001'
    ],
    [
        'type' => 'export',
        'quantity_change' => -5,
        'note' => 'Bán hàng cho khách VIP',
        'reference_id' => 'SO-2024-001'
    ],
    [
        'type' => 'adjust',
        'quantity_change' => 2,
        'note' => 'Điều chỉnh do kiểm kê',
        'reference_id' => 'ADJ-2024-001'
    ],
    [
        'type' => 'damage',
        'quantity_change' => -1,
        'note' => 'Hàng hỏng do vận chuyển',
        'reference_id' => 'DMG-2024-001'
    ],
    [
        'type' => 'return',
        'quantity_change' => 3,
        'note' => 'Khách hàng trả hàng',
        'reference_id' => 'RTN-2024-001'
    ]
];

$beforeStock = $product->stock;
$currentStock = $beforeStock;

foreach ($movements as $index => $movementData) {
    $movementData['product_id'] = $product->id;
    $movementData['before_stock'] = $currentStock;
    $movementData['after_stock'] = $currentStock + $movementData['quantity_change'];
    $movementData['transaction_date'] = now()->subDays(rand(1, 30));
    $movementData['performed_by'] = null;
    
    $movement = InventoryMovement::create($movementData);
    $currentStock = $movementData['after_stock'];
    
    echo "  - {$movement->type_text}: {$movement->quantity_change_formatted} (Tồn: {$movement->before_stock} → {$movement->after_stock})\n";
}

// Cập nhật stock sản phẩm
$product->update(['stock' => $currentStock]);

echo "\nTồn kho sau test: {$currentStock}\n\n";

// 3. Test các method mới
echo "3. Test các method mới...\n";

// Test accessors
$movement = InventoryMovement::latest()->first();
echo "Accessors test:\n";
echo "  - Type text: {$movement->type_text}\n";
echo "  - Type icon: {$movement->type_icon}\n";
echo "  - Quantity formatted: {$movement->quantity_change_formatted}\n";
echo "  - Date formatted: {$movement->transaction_date_formatted}\n";
echo "  - Is increase: " . ($movement->is_increase ? 'Yes' : 'No') . "\n";
echo "  - Total value: " . number_format($movement->total_value, 0, ',', '.') . "₫\n\n";

// Test scopes
echo "Scopes test:\n";
echo "  - Increases count: " . InventoryMovement::byProduct($product->id)->increases()->count() . "\n";
echo "  - Decreases count: " . InventoryMovement::byProduct($product->id)->decreases()->count() . "\n";
echo "  - Recent (7 days): " . InventoryMovement::byProduct($product->id)->recent(7)->count() . "\n\n";

// Test stats
echo "Stats test:\n";
$stats = InventoryMovement::getMovementStats($product->id);
echo "  - Total movements: {$stats['total_movements']}\n";
echo "  - Total increases: {$stats['total_increases']}\n";
echo "  - Total decreases: {$stats['total_decreases']}\n";
echo "  - Net change: {$stats['net_change']}\n";
echo "  - By type: " . json_encode($stats['by_type'], JSON_UNESCAPED_UNICODE) . "\n\n";

// 4. Test filtering
echo "4. Test filtering...\n";

$importMovements = InventoryMovement::byProduct($product->id)->byType('import')->get();
echo "Import movements: {$importMovements->count()}\n";

$recentMovements = InventoryMovement::byProduct($product->id)->recent(7)->get();
echo "Recent movements (7 days): {$recentMovements->count()}\n";

$dateRangeMovements = InventoryMovement::byProduct($product->id)
    ->byDateRange(now()->subDays(15), now())
    ->get();
echo "Movements in last 15 days: {$dateRangeMovements->count()}\n\n";

echo "=== HOÀN THÀNH TEST ===\n";
echo "Bạn có thể truy cập: /inventory/{$product->id} để xem giao diện\n";
