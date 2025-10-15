<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Promotion;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataService
{
    /**
     * Get comprehensive business context for AI agents
     */
    public function getBusinessContext(): array
    {
        try {
            $context = [
                'overview' => $this->getBusinessOverview(),
            ];
            
            try {
                $context['sales'] = $this->getSalesContext();
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting sales context', ['error' => $e->getMessage()]);
                $context['sales'] = [];
            }
            
            try {
                $context['inventory'] = $this->getInventoryContext();
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting inventory context', ['error' => $e->getMessage()]);
                $context['inventory'] = [];
            }
            
            try {
                $customerContext = $this->getCustomerContext();
                $context['customers'] = $customerContext['customers'] ?? [];
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting customer context', ['error' => $e->getMessage()]);
                $context['customers'] = [];
            }
            
            try {
                $context['products'] = $this->getProductContext();
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting product context', ['error' => $e->getMessage()]);
                $context['products'] = [];
            }
            
            try {
                $context['promotions'] = $this->getPromotionContext();
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting promotion context', ['error' => $e->getMessage()]);
                $context['promotions'] = [];
            }
            
            try {
                $context['recent_activity'] = $this->getRecentActivity();
            } catch (\Exception $e) {
                Log::warning('DataService: Error getting recent activity', ['error' => $e->getMessage()]);
                $context['recent_activity'] = [];
            }
            
            return $context;
        } catch (\Throwable $e) {
            Log::error('DataService: Error getting business context', ['error' => $e->getMessage()]);
            return ['error' => 'Không thể truy xuất dữ liệu kinh doanh'];
        }
    }

    /**
     * Get business overview data
     */
    private function getBusinessOverview(): array
    {
        $today = today();
        $thisMonth = now()->startOfMonth();
        
        return [
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'today_revenue' => (float)Order::whereDate('created_at', $today)->sum('final_amount'),
            'month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            'month_revenue' => (float)Order::where('created_at', '>=', $thisMonth)->sum('final_amount'),
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'active_promotions' => Promotion::where('is_active', true)->count(),
            'pending_orders' => Order::where('status', 'pending')->count()
        ];
    }

    /**
     * Get sales context data
     */
    private function getSalesContext(): array
    {
        $last30Days = now()->subDays(30);
        
        $recentOrders = Order::where('created_at', '>=', $last30Days)
            ->with(['customer', 'items.product'])
            ->get();

        $topProducts = $recentOrders->flatMap(function ($order) {
            return $order->items;
        })->groupBy('product_id')
        ->map(function ($items) {
            return [
                'product_id' => $items->first()->product_id,
                'product_name' => $items->first()->product->name ?? 'Unknown',
                'total_quantity' => $items->sum('quantity'),
                'total_revenue' => $items->sum(function ($item) {
                    return (float)$item->quantity * (float)$item->price;
                })
            ];
        })->sortByDesc('total_revenue')->take(5);

        return [
            'recent_orders_count' => $recentOrders->count(),
            'recent_revenue' => (float)$recentOrders->sum('final_amount'),
            'avg_order_value' => $recentOrders->count() > 0 ? (float)($recentOrders->sum('final_amount') / $recentOrders->count()) : 0,
            'top_products' => $topProducts->values()->toArray(),
            'orders_by_status' => $recentOrders->groupBy('status')->map->count()->toArray(),
            'revenue_trend' => $this->getRevenueTrend()
        ];
    }

    /**
     * Get inventory context data
     */
    private function getInventoryContext(): array
    {
        $products = Product::with(['variants', 'categories'])->get();
        
        $lowStockProducts = $products->filter(function ($product) {
            $stockQuantity = $product->variants ? (float)$product->variants->sum('stock_quantity') : (float)($product->stock ?? 0);
            return $stockQuantity <= 5;
        });

        $outOfStockProducts = $products->filter(function ($product) {
            $stockQuantity = $product->variants ? (float)$product->variants->sum('stock_quantity') : (float)($product->stock ?? 0);
            return $stockQuantity == 0;
        });

        $totalStockValue = (float)$products->sum(function ($product) {
            if ($product->variants && $product->variants->count() > 0) {
                return (float)$product->variants->sum(function ($variant) {
                    return (float)$variant->stock_quantity * (float)$variant->selling_price;
                });
            } else {
                return (float)($product->stock ?? 0) * (float)($product->price ?? 0);
            }
        });

        return [
            'total_products' => $products->count(),
            'low_stock_count' => $lowStockProducts->count(),
            'out_of_stock_count' => $outOfStockProducts->count(),
            'total_stock_value' => (float)$totalStockValue,
            'low_stock_products' => $lowStockProducts->take(10)->map(function ($product) {
                $stockQuantity = $product->variants ? (float)$product->variants->sum('stock_quantity') : (float)($product->stock ?? 0);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->categories->first() ? $product->categories->first()->name : 'N/A',
                    'total_stock' => $stockQuantity,
                    'variants' => $product->variants ? $product->variants->map(function ($variant) {
                        return [
                            'name' => $variant->name,
                            'stock' => (float)$variant->stock_quantity,
                            'price' => (float)$variant->selling_price
                        ];
                    })->toArray() : []
                ];
            })->toArray(),
            'recent_movements' => $this->getRecentInventoryMovements()
        ];
    }

    /**
     * Get customer context data
     */
    private function getCustomerContext(): array
    {
        $last30Days = now()->subDays(30);
        
        $recentCustomers = Customer::where('created_at', '>=', $last30Days)->get();
        $allCustomers = Customer::with(['orders'])
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'total_spent' => (float)$customer->orders->sum('final_amount'),
                    'orders_count' => $customer->orders->count(),
                    'last_order' => $customer->orders->max('created_at')
                ];
            });
            
        $topCustomers = $allCustomers->sortByDesc('total_spent')->take(10);

        return [
            'total_customers' => Customer::count(),
            'new_customers_30d' => $recentCustomers->count(),
            'customers' => $allCustomers->values()->toArray(), // All customers for lookup
            'top_customers' => $topCustomers->values()->toArray(),
            'customer_segments' => $this->getCustomerSegments(),
            'customer_retention' => $this->getCustomerRetention()
        ];
    }

    /**
     * Get product context data
     */
    private function getProductContext(): array
    {
        $products = Product::with(['categories', 'variants'])->get();
        
        $categories = Category::withCount('products')->get();
        
        $priceRanges = [
            'under_500k' => $products->filter(fn($p) => $p->price < 500000)->count(),
            '500k_1m' => $products->filter(fn($p) => $p->price >= 500000 && $p->price < 1000000)->count(),
            '1m_2m' => $products->filter(fn($p) => $p->price >= 1000000 && $p->price < 2000000)->count(),
            'over_2m' => $products->filter(fn($p) => $p->price >= 2000000)->count()
        ];

        return [
            'total_products' => $products->count(),
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category' => $product->categories->first() ? $product->categories->first()->name : 'N/A',
                    'is_active' => $product->is_active,
                    'description' => $product->description,
                    'variants' => $product->variants ? $product->variants->map(function ($variant) {
                        return [
                            'name' => $variant->name,
                            'stock_quantity' => $variant->stock_quantity,
                            'selling_price' => $variant->selling_price
                        ];
                    })->toArray() : []
                ];
            })->toArray(),
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products_count' => $category->products_count
                ];
            })->toArray(),
            'price_ranges' => $priceRanges,
            'avg_price' => (float)$products->avg('price'),
            'top_categories' => $categories->sortByDesc('products_count')->take(5)->values()->toArray()
        ];
    }

    /**
     * Get promotion context data
     */
    private function getPromotionContext(): array
    {
        $activePromotions = Promotion::where('is_active', true)->get();
        
        return [
            'active_count' => $activePromotions->count(),
            'promotions' => $activePromotions->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'type' => $promotion->type,
                    'discount_value' => $promotion->discount_value,
                    'start_date' => $promotion->start_date,
                    'end_date' => $promotion->end_date,
                    'usage_count' => (int)($promotion->usage_count ?? 0)
                ];
            })->toArray()
        ];
    }

    /**
     * Get recent activity data
     */
    private function getRecentActivity(): array
    {
        $last24Hours = now()->subDay();
        
        return [
            'recent_orders' => Order::where('created_at', '>=', $last24Hours)
                ->with(['customer'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer->name ?? 'N/A',
                        'amount' => $order->final_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('d/m/Y H:i')
                    ];
                })->toArray(),
            'recent_customers' => Customer::where('created_at', '>=', $last24Hours)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'created_at' => $customer->created_at->format('d/m/Y H:i')
                    ];
                })->toArray(),
            'recent_movements' => InventoryMovement::where('created_at', '>=', $last24Hours)
                ->with(['product'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'product_name' => $movement->product->name ?? 'Unknown',
                        'type' => $movement->type,
                        'quantity' => $movement->quantity,
                        'reason' => $movement->reason,
                        'user_name' => 'System',
                        'created_at' => $movement->created_at->format('d/m/Y H:i')
                    ];
                })->toArray()
        ];
    }

    /**
     * Get revenue trend data
     */
    private function getRevenueTrend(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $revenue = (float)Order::whereDate('created_at', $date)->sum('final_amount');
            return [
                'date' => $date->format('d/m'),
                'revenue' => $revenue
            ];
        });

        return $last7Days->toArray();
    }

    /**
     * Get recent inventory movements
     */
    private function getRecentInventoryMovements(): array
    {
        try {
            return InventoryMovement::with(['product'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'product_name' => $movement->product->name ?? 'Unknown',
                        'type' => $movement->type,
                        'quantity' => $movement->quantity,
                        'reason' => $movement->reason,
                        'user_name' => 'System',
                        'created_at' => $movement->created_at->format('d/m/Y H:i')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::warning('DataService: Error getting inventory movements', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get customer segments
     */
    private function getCustomerSegments(): array
    {
        $customers = Customer::with(['orders'])->get();
        
        $segments = [
            'vip' => 0,
            'premium' => 0,
            'regular' => 0,
            'new' => 0
        ];

        foreach ($customers as $customer) {
            $totalSpent = (float)$customer->orders->sum('final_amount');
            $ordersCount = $customer->orders->count();
            
            if ($totalSpent >= 10000000) {
                $segments['vip']++;
            } elseif ($totalSpent >= 5000000) {
                $segments['premium']++;
            } elseif ($totalSpent >= 1000000 || $ordersCount >= 3) {
                $segments['regular']++;
            } else {
                $segments['new']++;
            }
        }

        return $segments;
    }

    /**
     * Get customer retention rate
     */
    private function getCustomerRetention(): float
    {
        $last30Days = now()->subDays(30);
        $last60Days = now()->subDays(60);
        
        $customers30Days = Customer::where('created_at', '>=', $last30Days)->count();
        $customers60Days = Customer::where('created_at', '>=', $last60Days)->count();
        
        if ($customers60Days == 0) return 0;
        
        return (float)($customers30Days / $customers60Days) * 100;
    }

    /**
     * Format business context for LLM
     */
    public function formatBusinessContextForLLM(array $context): string
    {
        $formatted = "📊 **DỮ LIỆU KINH DOANH HIỆN TẠI**\n\n";
        
        // Overview
        $overview = $context['overview'] ?? [];
        $formatted .= "🏢 **TỔNG QUAN**\n";
        
        $todayOrders = $overview['today_orders'] ?? 0;
        $todayRevenue = $overview['today_revenue'] ?? 0;
        $monthOrders = $overview['month_orders'] ?? 0;
        $monthRevenue = $overview['month_revenue'] ?? 0;
        $totalProducts = $overview['total_products'] ?? 0;
        $totalCustomers = $overview['total_customers'] ?? 0;
        $activePromotions = $overview['active_promotions'] ?? 0;
        $pendingOrders = $overview['pending_orders'] ?? 0;
        
        $formatted .= "• Đơn hàng hôm nay: {$todayOrders} đơn (" . number_format($todayRevenue) . "đ)\n";
        $formatted .= "• Đơn hàng tháng này: {$monthOrders} đơn (" . number_format($monthRevenue) . "đ)\n";
        $formatted .= "• Tổng sản phẩm: {$totalProducts} sản phẩm\n";
        $formatted .= "• Tổng khách hàng: {$totalCustomers} khách\n";
        $formatted .= "• CTKM đang chạy: {$activePromotions} chương trình\n";
        $formatted .= "• Đơn chờ xử lý: {$pendingOrders} đơn\n\n";

        // Sales context
        $sales = $context['sales'] ?? [];
        $formatted .= "💰 **BÁN HÀNG (30 NGÀY GẦN NHẤT)**\n";
        
        $recentRevenue = $sales['recent_revenue'] ?? 0;
        $recentOrdersCount = $sales['recent_orders_count'] ?? 0;
        $avgOrderValue = $sales['avg_order_value'] ?? 0;
        
        $formatted .= "• Tổng doanh thu: " . number_format($recentRevenue) . "đ\n";
        $formatted .= "• Số đơn hàng: {$recentOrdersCount} đơn\n";
        $formatted .= "• Giá trị đơn TB: " . number_format($avgOrderValue) . "đ\n";
        
        if (!empty($sales['top_products'])) {
            $formatted .= "• Top sản phẩm:\n";
            foreach (array_slice($sales['top_products'], 0, 3) as $index => $product) {
                $rank = $index + 1;
                $productName = $product['product_name'] ?? 'Unknown';
                $totalRevenue = $product['total_revenue'] ?? 0;
                $formatted .= "  {$rank}. {$productName} - " . number_format($totalRevenue) . "đ\n";
            }
        }
        $formatted .= "\n";

        // Inventory context
        $inventory = $context['inventory'] ?? [];
        $formatted .= "📦 **TỒN KHO**\n";
        
        $totalProducts = $inventory['total_products'] ?? 0;
        $lowStockCount = $inventory['low_stock_count'] ?? 0;
        $outOfStockCount = $inventory['out_of_stock_count'] ?? 0;
        
        $formatted .= "• Tổng sản phẩm: {$totalProducts} sản phẩm\n";
        $formatted .= "• Tồn thấp (≤5): {$lowStockCount} sản phẩm\n";
        $formatted .= "• Hết hàng: {$outOfStockCount} sản phẩm\n";
        
        $totalStockValue = $inventory['total_stock_value'] ?? 0;
        $formatted .= "• Giá trị tồn kho: " . number_format($totalStockValue) . "đ\n\n";

        // Customer context
        $customers = $context['customers'] ?? [];
        $formatted .= "👥 **KHÁCH HÀNG**\n";
        
        $totalCustomers = $customers['total_customers'] ?? 0;
        $newCustomers = $customers['new_customers_30d'] ?? 0;
        $customerRetention = $customers['customer_retention'] ?? 0;
        
        $formatted .= "• Tổng khách hàng: {$totalCustomers} khách\n";
        $formatted .= "• Khách mới (30 ngày): {$newCustomers} khách\n";
        $formatted .= "• Tỷ lệ giữ chân: " . number_format((float)$customerRetention, 1) . "%\n\n";

        return $formatted;
    }

    /**
     * Get specific data for agent context
     */
    public function getAgentSpecificContext(string $agentType): array
    {
        $context = $this->getBusinessContext();
        
        switch ($agentType) {
            case 'sales':
                return [
                    'sales_data' => $context['sales'],
                    'recent_orders' => $context['recent_activity']['recent_orders'],
                    'top_customers' => $context['customers'], // Use all customers, not just top 10
                    'promotions' => $context['promotions']
                ];
                
            case 'inventory':
                return [
                    'inventory_data' => $context['inventory'] ?? [],
                    'low_stock_products' => $context['inventory']['low_stock_products'] ?? [],
                    'recent_movements' => $context['recent_activity']['recent_movements'] ?? [],
                    'product_categories' => $context['products']['categories'] ?? []
                ];
                
            case 'report':
                return [
                    'overview' => $context['overview'] ?? [],
                    'sales_trend' => $context['sales']['revenue_trend'] ?? [],
                    'customer_segments' => $context['customers']['customer_segments'] ?? [],
                    'top_products' => $context['sales']['top_products'] ?? []
                ];
                
            case 'chat':
                return [
                    'product_categories' => $context['products']['categories'] ?? [],
                    'top_products' => $context['sales']['top_products'] ?? [],
                    'price_ranges' => $context['products']['price_ranges'] ?? [],
                    'recent_activity' => $context['recent_activity'] ?? []
                ];
                
            default:
                return $context;
        }
    }

    /**
     * Get real data formatted for LLM consumption
     */
    public function getRealDataForLLM(string $context = ''): string
    {
        try {
            $businessContext = $this->getBusinessContext();
            return $this->formatBusinessContextForLLM($businessContext);
        } catch (\Exception $e) {
            Log::error('DataService: Error getting real data for LLM', ['error' => $e->getMessage()]);
            return "Không thể truy xuất dữ liệu kinh doanh hiện tại.";
        }
    }
}