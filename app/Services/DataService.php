<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class DataService
{
    /**
     * Lấy dữ liệu thực tế cho LLM dựa trên message
     */
    public function getRealDataForLLM(string $message): string
    {
        $data = [];
        
        try {
            // Debug logging
            Log::info('DataService Debug', [
                'original_message' => $message,
                'message_length' => strlen($message),
                'message_bytes' => bin2hex($message)
            ]);
            
            // Kiểm tra từ khóa trong message để lấy dữ liệu phù hợp
            $message = strtolower($message);
            
            // Debug từ khóa
            $keywords = ['nước hoa', 'nuoc hoa', 'nu?c hoa', 'sản phẩm', 'san pham', 's?n ph?m', 'tong', 'tổng'];
            $foundKeywords = [];
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $foundKeywords[] = $keyword;
                }
            }
            Log::info('Keywords found', ['keywords' => $foundKeywords]);
            
            // Sản phẩm cụ thể
            if (strpos($message, 'nước hoa') !== false || strpos($message, 'nuoc hoa') !== false || strpos($message, 'nu?c hoa') !== false || strpos($message, 'sản phẩm') !== false || strpos($message, 'san pham') !== false || strpos($message, 's?n ph?m') !== false) {
                $products = Product::with(['variants'])
                    ->limit(8)
                    ->get();
                
            if ($products->count() > 0) {
                $data[] = "DU LIEU SAN PHAM THUC TE:\n";
                foreach ($products as $product) {
                    $data[] = "- {$product->name} ({$product->brand})";
                    if ($product->price) {
                        $data[] = "  Gia: " . number_format($product->price) . " VND";
                    }
                    if ($product->variants->count() > 0) {
                        $totalStock = $product->variants->sum('stock_quantity');
                        $data[] = "  Ton kho: {$totalStock} chai";
                    }
                    if ($product->description) {
                        $data[] = "  Mo ta: " . substr($product->description, 0, 100) . "...";
                    }
                    $data[] = "";
                }
            }
            }
            
            // Đơn hàng cụ thể
            if (strpos($message, 'đơn hàng') !== false || strpos($message, 'đơn số') !== false) {
                $orders = Order::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                
            if ($orders->count() > 0) {
                $data[] = "DU LIEU DON HANG THUC TE:\n";
                foreach ($orders as $order) {
                    $data[] = "- Don #{$order->order_number}";
                    $data[] = "  Khach hang: {$order->customer_name}";
                    $data[] = "  Tong tien: " . number_format($order->total_amount) . " VND";
                    $data[] = "  Trang thai: {$order->status}";
                    $data[] = "  Ngay tao: " . $order->created_at->format('d/m/Y H:i');
                    $data[] = "";
                }
            }
            }
            
            // Tồn kho thấp hoặc kiểm tra tồn kho
            if (strpos($message, 'tồn thấp') !== false || strpos($message, 'hết hàng') !== false || 
                strpos($message, 'trong kho') !== false || strpos($message, 'còn nhiều') !== false ||
                strpos($message, 'tồn kho') !== false || strpos($message, 'còn lại') !== false) {
                
                // Kiểm tra xem có tên sản phẩm cụ thể không
                $specificProduct = $this->extractProductName($message);
                
                if ($specificProduct) {
                    // Tìm sản phẩm cụ thể
                    $product = Product::with('variants')
                        ->where('name', 'like', "%{$specificProduct}%")
                        ->first();
                    
                    if ($product) {
                        $data[] = "DU LIEU TON KHO SAN PHAM CU THE:\n";
                        $data[] = "- {$product->name} ({$product->brand})";
                        
                        if ($product->variants->count() > 0) {
                            $totalStock = $product->variants->sum('stock_quantity');
                            $data[] = "  Tong ton kho: {$totalStock} chai";
                            
                            foreach ($product->variants as $variant) {
                                $data[] = "  + {$variant->variant_name}: {$variant->stock_quantity} chai";
                            }
                        } else {
                            // Kiểm tra stock trong bảng products
                            if (isset($product->stock) && $product->stock > 0) {
                                $data[] = "  Ton kho: {$product->stock} chai";
                            } else {
                                $data[] = "  Ton kho: Khong co thong tin";
                            }
                        }
                        $data[] = "";
                    } else {
                        $data[] = "DU LIEU TON KHO:\n";
                        $data[] = "Khong tim thay san pham '{$specificProduct}' trong he thong";
                    }
                } else {
                    // Hiển thị tồn kho thấp
                    $lowStockVariants = ProductVariant::with('product')
                        ->where('stock_quantity', '<=', 5)
                        ->get();
                    
                    if ($lowStockVariants->count() > 0) {
                        $data[] = "DU LIEU TON KHO THAP THUC TE:\n";
                        foreach ($lowStockVariants as $variant) {
                            $data[] = "- {$variant->product->name} ({$variant->variant_name})";
                            $data[] = "  Con lai: {$variant->stock_quantity} chai";
                            $data[] = "  Canh bao: Ton kho thap!";
                            $data[] = "";
                        }
                    } else {
                        $data[] = "DU LIEU TON KHO:\n";
                        $data[] = "Khong co san pham nao co ton kho <= 5 chai";
                    }
                }
            }
            
            // Khách hàng VIP
            if (strpos($message, 'khách vip') !== false || strpos($message, 'khách hàng') !== false) {
                $vipCustomers = Customer::where('total_spent', '>', 1000000)
                    ->orderBy('total_spent', 'desc')
                    ->limit(5)
                    ->get();
                
                if ($vipCustomers->count() > 0) {
                    $data[] = "DU LIEU KHACH HANG VIP THUC TE:\n";
                    foreach ($vipCustomers as $customer) {
                        $data[] = "- {$customer->name}";
                        $data[] = "  SDT: {$customer->phone}";
                        $data[] = "  Tong chi tieu: " . number_format($customer->total_spent) . " VND";
                        $data[] = "";
                    }
                }
            }
            
            // Thống kê tổng quan
            if (strpos($message, 'tổng') !== false || strpos($message, 'tong') !== false || strpos($message, 'thống kê') !== false || strpos($message, 'thong ke') !== false) {
                $totalProducts = Product::count();
                $totalOrders = Order::count();
                $totalCustomers = Customer::count();
                $totalRevenue = Order::sum('total_amount');
                $totalVariants = ProductVariant::count();
                $totalStock = ProductVariant::sum('stock_quantity');
                
                $data[] = "DU LIEU THONG KE TONG QUAN THUC TE:\n";
                $data[] = "- Tong san pham: {$totalProducts} loai";
                $data[] = "- Tong variants: {$totalVariants} loai";
                $data[] = "- Tong don hang: {$totalOrders} don";
                $data[] = "- Tong khach hang: {$totalCustomers} nguoi";
                $data[] = "- Tong doanh thu: " . number_format($totalRevenue) . " VND";
                $data[] = "- Tong ton kho: {$totalStock} chai";
            }
            
            // Nếu không có dữ liệu phù hợp
            if (empty($data)) {
                $data[] = "DU LIEU HE THONG:\n";
                $data[] = "Khong tim thay du lieu phu hop voi cau hoi cua ban.";
                $data[] = "Hay thu hoi ve: san pham, don hang, ton kho, khach hang, hoac thong ke.";
            }
            
        } catch (\Exception $e) {
            Log::error('Error getting real data for LLM', ['error' => $e->getMessage()]);
            $data[] = "LOI HE THONG:\n";
            $data[] = "Khong the truy cap du lieu luc nay. Vui long thu lai sau.";
        }
        
        return mb_convert_encoding(implode("\n", $data), 'UTF-8', 'auto');
    }
    
    /**
     * Trích xuất tên sản phẩm từ câu hỏi
     */
    private function extractProductName(string $message): ?string
    {
        // Danh sách các từ khóa sản phẩm phổ biến
        $productKeywords = [
            'Roja Enigma Pour Homme',
            'Xerjoff Casamorati 1888',
            'Gucci Guilty Pour Homme',
            'Atelier Cologne Pomelo Paradis',
            'BDK Gris Charnel Extrait',
            'Byredo Blanche EDP',
            'Lancome Idole Power EDP Intense',
            'Roja Burlington 1819'
        ];
        
        $message = strtolower($message);
        
        foreach ($productKeywords as $keyword) {
            if (strpos($message, strtolower($keyword)) !== false) {
                return $keyword;
            }
        }
        
        // Tìm kiếm pattern chung cho tên sản phẩm
        if (preg_match('/([A-Za-z\s]+(?:Pour Homme|EDP|EDT|Extrait|Intense))/i', $message, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
}
