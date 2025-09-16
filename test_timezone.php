<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST MÚI GIỜ VIỆT NAM ===\n\n";

// Test helper
use App\Helpers\DateTimeHelper;

echo "1. Test DateTimeHelper:\n";
echo "   Current time (UTC): " . now()->format('d/m/Y H:i:s') . "\n";
echo "   Vietnamese time: " . DateTimeHelper::formatVietnamese(now()) . "\n";
echo "   Vietnamese time (full): " . DateTimeHelper::formatVietnamese(now(), 'd/m/Y H:i:s') . "\n\n";

// Test với sản phẩm thực tế
echo "2. Test với sản phẩm thực tế:\n";
$product = \App\Models\Product::first();
if ($product) {
    echo "   Sản phẩm: {$product->name}\n";
    echo "   Created at (UTC): " . $product->created_at->format('d/m/Y H:i:s') . "\n";
    echo "   Created at (VN): " . DateTimeHelper::formatVietnamese($product->created_at, 'd/m/Y H:i:s') . "\n";
    echo "   Updated at (UTC): " . $product->updated_at->format('d/m/Y H:i:s') . "\n";
    echo "   Updated at (VN): " . DateTimeHelper::formatVietnamese($product->updated_at, 'd/m/Y H:i:s') . "\n";
} else {
    echo "   Không có sản phẩm nào!\n";
}

echo "\n3. So sánh múi giờ:\n";
echo "   UTC: " . now()->utc()->format('d/m/Y H:i:s') . "\n";
echo "   VN:  " . now()->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') . "\n";
echo "   Chênh lệch: +7 giờ\n";
