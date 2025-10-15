<?php
/**
 * Script tạo embeddings và lưu vào vector store
 */

echo "🚀 TẠO EMBEDDINGS VỚI VECTOR STORE\n";
echo "==================================\n\n";

// Khởi tạo Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\VectorEmbeddingService;
use App\Models\Product;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Promotion;

// Kiểm tra cấu hình
$embeddingService = new VectorEmbeddingService();
if (!$embeddingService->isConfigured()) {
    echo "❌ Embedding service chưa được cấu hình. Vui lòng thiết lập OPENAI_API_KEY trong .env\n";
    exit(1);
}

echo "✅ Embedding service đã được cấu hình\n";
echo "📁 Vector store: " . storage_path('app/vector_store') . "\n\n";

$startTime = microtime(true);
$stats = [
    'products' => 0,
    'orders' => 0,
    'shipments' => 0,
    'customers' => 0,
    'promotions' => 0,
    'errors' => []
];

// Function để xử lý từng record
function processRecord($record, $type, $embeddingService, &$stats) {
    try {
        switch ($type) {
            case 'Product':
                $embeddingService->generateProductEmbeddings($record);
                break;
            case 'Order':
                $embeddingService->generateOrderEmbeddings($record);
                break;
            case 'Shipment':
                $embeddingService->generateShipmentEmbeddings($record);
                break;
            case 'Customer':
                $embeddingService->generateCustomerEmbeddings($record);
                break;
            case 'Promotion':
                $embeddingService->generatePromotionEmbeddings($record);
                break;
        }
        
        echo "   ✅ {$type} ID {$record->id}: OK\n";
        $stats[strtolower($type . 's')]++;
        return true;
        
    } catch (Exception $e) {
        $errorMsg = "{$type} ID {$record->id}: " . $e->getMessage();
        echo "   ❌ $errorMsg\n";
        $stats['errors'][] = $errorMsg;
        return false;
    }
}

echo "🔄 Bắt đầu tạo embeddings...\n\n";

// 1. Xử lý Products
echo "📦 Xử lý Products...\n";
$products = Product::where('is_active', true)->get();
echo "   📊 Tìm thấy {$products->count()} products\n";

foreach ($products as $product) {
    processRecord($product, 'Product', $embeddingService, $stats);
    usleep(200000); // 0.2 giây delay
}

// 2. Xử lý Orders
echo "\n📋 Xử lý Orders...\n";
$orders = Order::all();
echo "   📊 Tìm thấy {$orders->count()} orders\n";

foreach ($orders as $order) {
    processRecord($order, 'Order', $embeddingService, $stats);
    usleep(200000);
}

// 3. Xử lý Shipments
echo "\n🚚 Xử lý Shipments...\n";
$shipments = Shipment::all();
echo "   📊 Tìm thấy {$shipments->count()} shipments\n";

foreach ($shipments as $shipment) {
    processRecord($shipment, 'Shipment', $embeddingService, $stats);
    usleep(200000);
}

// 4. Xử lý Customers
echo "\n👥 Xử lý Customers...\n";
$customers = Customer::where('is_active', true)->get();
echo "   📊 Tìm thấy {$customers->count()} customers\n";

foreach ($customers as $customer) {
    processRecord($customer, 'Customer', $embeddingService, $stats);
    usleep(200000);
}

// 5. Xử lý Promotions
echo "\n🎁 Xử lý Promotions...\n";
$promotions = Promotion::where('is_active', true)->get();
echo "   📊 Tìm thấy {$promotions->count()} promotions\n";

foreach ($promotions as $promotion) {
    processRecord($promotion, 'Promotion', $embeddingService, $stats);
    usleep(200000);
}

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

// Hiển thị kết quả
echo "\n📊 KẾT QUẢ EMBEDDING:\n";
echo "✅ Products processed: {$stats['products']}\n";
echo "✅ Orders processed: {$stats['orders']}\n";
echo "✅ Shipments processed: {$stats['shipments']}\n";
echo "✅ Customers processed: {$stats['customers']}\n";
echo "✅ Promotions processed: {$stats['promotions']}\n";
echo "⏱️ Thời gian thực hiện: {$executionTime} giây\n";

if (!empty($stats['errors'])) {
    echo "\n❌ Các lỗi gặp phải:\n";
    foreach ($stats['errors'] as $error) {
        echo "• $error\n";
    }
}

// Thống kê vector store
echo "\n📈 THỐNG KÊ VECTOR STORE:\n";
try {
    $vectorStats = $embeddingService->getStats();
    
    echo "• Tổng embeddings: {$vectorStats['total_embeddings']}\n";
    echo "• Kích thước lưu trữ: " . number_format($vectorStats['storage_size'] / 1024, 2) . " KB\n";
    
    echo "\n📋 Phân bố theo loại:\n";
    foreach ($vectorStats['by_type'] as $type => $count) {
        echo "• $type: $count embeddings\n";
    }
    
    echo "\n🤖 Phân bố theo model:\n";
    foreach ($vectorStats['by_model'] as $model => $count) {
        echo "• $model: $count embeddings\n";
    }
    
} catch (Exception $e) {
    echo "❌ Lỗi lấy thống kê: " . $e->getMessage() . "\n";
}

// Test search functionality
echo "\n🧪 TEST TÍNH NĂNG TÌM KIẾM:\n";

$testQueries = [
    'nước hoa nam',
    'đơn hàng ABC123',
    'khách hàng VIP',
    'chương trình khuyến mãi',
    'vận chuyển giao hàng'
];

foreach ($testQueries as $query) {
    echo "🔍 Testing query: '$query'\n";
    try {
        $results = $embeddingService->search($query, 3);
        if (!empty($results)) {
            echo "   ✅ Tìm thấy " . count($results) . " kết quả\n";
            foreach ($results as $i => $result) {
                $score = round($result['final_score'] * 100, 2);
                $type = $result['data']['embeddable_type'];
                $id = $result['data']['embeddable_id'];
                echo "   • #" . ($i + 1) . ": $type ID $id (Score: $score%)\n";
            }
        } else {
            echo "   ⚠️ Không tìm thấy kết quả\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Lỗi: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "🎉 HOÀN THÀNH EMBEDDING VỚI VECTOR STORE!\n";
echo "💡 Bây giờ bạn có thể sử dụng tính năng tìm kiếm semantic!\n";
echo "📁 Vector store được lưu tại: " . storage_path('app/vector_store') . "\n";
