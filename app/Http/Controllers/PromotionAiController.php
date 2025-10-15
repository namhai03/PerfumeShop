<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\DataService;
use App\Services\PromotionService;
use App\Services\LLMService;
use App\Models\Promotion;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class PromotionAiController extends Controller
{
    public function index()
    {
        return view('promotions.ai.index');
    }

    public function suggest(Request $request, DataService $dataService, LLMService $llm)
    {
        $objective = (string) $request->input('objective', 'push_stock');
        $maxDiscount = (int) min(50, max(5, (int)$request->input('max_discount_percent', 20)));
        $minOrderAmount = (float) max(0, (float)$request->input('min_order_amount', 0));
        $windowDays = (int) max(7, (int)$request->input('window_days', 30));
        $seed = (int) $request->input('seed', (int) (microtime(true) * 1000));

        // Seed RNG for diversity per request
        mt_srand($seed);

        $since = now()->subDays($windowDays);

        // AOV và phân vị đơn giản
        $aov = (float) Order::sales()
            ->where('created_at', '>=', $since)
            ->avg('final_amount');
        $aov = $aov > 0 ? $aov : 300000; // fallback

        $amounts = Order::sales()
            ->where('created_at', '>=', $since)
            ->orderBy('final_amount')
            ->pluck('final_amount')
            ->all();
        $p75 = $this->percentile($amounts, 0.75) ?: max($aov * 1.2, 400000);

        // KPIs trong cửa sổ thời gian
        $ordersQuery = Order::sales()->where('created_at', '>=', $since);
        $ordersTotal = (int) $ordersQuery->count();
        $revenue = (float) Order::sales()->where('created_at', '>=', $since)->sum('final_amount');
        $delivered = (int) Order::sales()->where('created_at', '>=', $since)->where('status', Order::STATUS_DELIVERED)->count();
        $failed = (int) Order::sales()->where('created_at', '>=', $since)->where('status', Order::STATUS_FAILED)->count();
        $returned = (int) Order::sales()->where('created_at', '>=', $since)->where('status', Order::STATUS_RETURNED)->count();
        $terminal = max(1, $delivered + $failed + $returned);
        $successRate = $delivered / max(1, $ordersTotal);
        $successRateTerminal = $delivered / $terminal;
        // Repeat rate xấp xỉ: tỷ lệ KH có >=2 đơn trong cửa sổ
        $repeatCustomers = (int) Order::sales()->where('created_at','>=',$since)
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->count();
        $distinctCustomers = (int) Order::sales()->where('created_at','>=',$since)->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');
        $repeatRate = $distinctCustomers > 0 ? $repeatCustomers / $distinctCustomers : 0.0;

        // Doanh số 30 ngày theo sản phẩm
        $sales30 = OrderItem::query()
            ->select(['product_id', DB::raw('SUM(quantity) as qty30'), DB::raw('SUM(total_price) as rev30')])
            ->whereHas('order', function($q) use ($since) {
                $q->where('type', Order::TYPE_SALE)->where('created_at', '>=', $since);
            })
            ->groupBy('product_id')
            ->pluck('qty30', 'product_id');

        // Tồn kho hiện tại
        $stocks = ProductVariant::query()
            ->select(['product_id', DB::raw('SUM(stock) as stock')])
            ->groupBy('product_id')
            ->pluck('stock', 'product_id');

        // Điểm ưu tiên tồn cao/chậm bán
        $products = Product::query()->select(['id', 'name', 'brand', 'selling_price', 'import_price'])->get();
        $scored = [];
        foreach ($products as $p) {
            $qty30 = (int) ($sales30[$p->id] ?? 0);
            $stock = (int) ($stocks[$p->id] ?? (int)($p->stock ?? 0));
            $daily = max(0.1, $qty30 / max(1, $windowDays));
            $doc = $daily > 0 ? ($stock / $daily) : ($stock > 0 ? 999 : 0); // days of cover
            $priority = ($stock > 0 ? min(999, $doc) : 0) + ($qty30 === 0 ? 50 : 0);
            $scored[] = [
                'product_id' => $p->id,
                'name' => $p->name,
                'brand' => $p->brand,
                'stock' => $stock,
                'qty30' => $qty30,
                'doc' => $doc,
                'priority' => $priority,
                'price' => (float)($p->selling_price ?? 0),
            ];
        }
        usort($scored, fn($a,$b)=> $b['priority'] <=> $a['priority']);
        $topCandidates = array_slice($scored, 0, 10);
        shuffle($topCandidates);
        $topSlow = array_slice($topCandidates, 0, 5);
        $topSlowIds = array_column($topSlow, 'product_id');
        $topSlowMeta = [];
        foreach ($topSlow as $s) {
            $prod = $products->firstWhere('id', $s['product_id']);
            if ($prod) {
                $topSlowMeta[] = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'brand' => $prod->brand,
                    'image' => $prod->image ?? null,
                ];
            }
        }

        // Đề xuất theo mục tiêu
        $suggestions = [];

        // Skip AI suggestions to use detailed heuristic analysis
        // AI suggestions disabled to ensure detailed economic analysis is shown

        // Helper: get empty economic metrics
        $getEmptyEconomicMetrics = function(): array {
            return [
                'avg_margin_rate' => 0,
                'total_stock_value' => 0,
                'avg_turnover_rate' => 0,
                'inventory_turnover_days' => 999,
                'product_count' => 0,
                'total_selling_value' => 0,
                'total_cost_value' => 0,
                'gross_profit_potential' => 0,
            ];
        };
        
        // Helper: calculate risk score based on multiple factors
        $calculateRiskScore = function(array $economicMetrics, float $discountPct, int $predictedUsage): float {
            $riskFactors = [];
            
            // Margin risk (higher discount vs margin)
            $marginRisk = $economicMetrics['avg_margin_rate'] > 0 ? 
                min(1.0, $discountPct / $economicMetrics['avg_margin_rate']) : 1.0;
            $riskFactors[] = $marginRisk * 0.3;
            
            // Inventory turnover risk
            $turnoverRisk = $economicMetrics['inventory_turnover_days'] > 90 ? 0.8 : 
                           ($economicMetrics['inventory_turnover_days'] > 30 ? 0.4 : 0.1);
            $riskFactors[] = $turnoverRisk * 0.25;
            
            // Usage prediction risk
            $usageRisk = $predictedUsage < 20 ? 0.8 : ($predictedUsage < 50 ? 0.4 : 0.1);
            $riskFactors[] = $usageRisk * 0.25;
            
            // Stock value concentration risk
            $concentrationRisk = $economicMetrics['total_stock_value'] > 10000000 ? 0.1 : 
                               ($economicMetrics['total_stock_value'] > 5000000 ? 0.3 : 0.6);
            $riskFactors[] = $concentrationRisk * 0.2;
            
            return array_sum($riskFactors);
        };
        
        // Helper: calculate confidence level
        $calculateConfidenceLevel = function(float $riskScore, float $safetyMargin): string {
            if ($riskScore < 0.3 && $safetyMargin > 0.5) return 'Cao';
            if ($riskScore < 0.5 && $safetyMargin > 0.3) return 'Trung bình';
            if ($riskScore < 0.7 && $safetyMargin > 0.1) return 'Thấp';
            return 'Rất thấp';
        };
        
        // Enhanced helper: comprehensive economic analysis for product sets
        $calcEconomicMetrics = function(array $productIds, array $productData = []) use ($products, $aov, $revenue, $ordersTotal, $getEmptyEconomicMetrics): array {
            if (empty($productIds)) return $getEmptyEconomicMetrics();
            
            $subset = $products->whereIn('id', $productIds);
            $totalValue = 0;
            $totalCost = 0;
            $margins = [];
            $stockValues = [];
            $turnoverRates = [];
            
            foreach ($subset as $p) {
                $sell = (float) ($p->selling_price ?? 0);
                $imp = (float) ($p->import_price ?? 0);
                $stock = (float) ($p->stock ?? 0);
                
                if ($sell > 0) {
                    $totalValue += $sell;
                    $totalCost += $imp;
                    
                    if ($sell > $imp) {
                        $margin = max(0.0, min(0.95, ($sell - $imp) / $sell));
                        $margins[] = $margin;
                    }
                    
                    $stockValues[] = $sell * $stock;
                    
                    // Find turnover rate from product data
                    $productInfo = collect($productData)->firstWhere('product_id', $p->id);
                    $qty30 = $productInfo['qty30'] ?? 0;
                    $turnoverRate = $stock > 0 ? $qty30 / $stock : 0;
                    $turnoverRates[] = $turnoverRate;
                }
            }
            
            $avgMargin = count($margins) > 0 ? array_sum($margins) / count($margins) : 0;
            $totalStockValue = array_sum($stockValues);
            $avgTurnoverRate = count($turnoverRates) > 0 ? array_sum($turnoverRates) / count($turnoverRates) : 0;
            $inventoryTurnoverDays = $avgTurnoverRate > 0 ? 30 / $avgTurnoverRate : 999;
            
            return [
                'avg_margin_rate' => $avgMargin,
                'total_stock_value' => $totalStockValue,
                'avg_turnover_rate' => $avgTurnoverRate,
                'inventory_turnover_days' => $inventoryTurnoverDays,
                'product_count' => count($productIds),
                'total_selling_value' => $totalValue,
                'total_cost_value' => $totalCost,
                'gross_profit_potential' => $totalValue - $totalCost,
            ];
        };
        
        // Helper: calculate comprehensive ROI and financial projections
        $calculateFinancialProjections = function(array $economicMetrics, float $discountRate, string $type, int $predictedUsage, float $baselineRevenue) use ($aov, $revenue, $calculateRiskScore, $calculateConfidenceLevel): array {
            $avgMargin = $economicMetrics['avg_margin_rate'];
            $totalStockValue = $economicMetrics['total_stock_value'];
            
            // Calculate discount impact
            $discountPct = $type === 'percent' ? ($discountRate / 100.0) : min(0.3, $discountRate / max(1, $aov));
            
            // Revenue projections
            $upliftPct = min(0.40, 0.05 + 0.8 * $discountPct);
            $projectedRevenue = $baselineRevenue * (1 + $upliftPct);
            $additionalRevenue = $projectedRevenue - $baselineRevenue;
            
            // Cost analysis
            $discountCost = $projectedRevenue * $discountPct;
            $netRevenue = $projectedRevenue - $discountCost;
            $additionalNetRevenue = $netRevenue - ($baselineRevenue * (1 - $discountPct));
            
            // ROI calculations
            $roi = $avgMargin > 0 ? ($upliftPct * $avgMargin) / max(0.01, $discountPct) : 0;
            $paybackPeriod = $discountCost > 0 ? $discountCost / max(0.01, $additionalNetRevenue) : 0;
            
            // Risk assessment
            $riskScore = $calculateRiskScore($economicMetrics, $discountPct, $predictedUsage);
            
            // Break-even analysis
            $breakEvenUsage = ($discountCost > 0 && $aov > 0 && $avgMargin > 0) ? $discountCost / ($aov * $avgMargin) : 0;
            $safetyMargin = $predictedUsage > 0 ? ($predictedUsage - $breakEvenUsage) / $predictedUsage : 0;
            
            return [
                'discount_percentage' => $discountPct * 100,
                'revenue_uplift_percentage' => $upliftPct * 100,
                'baseline_revenue' => $baselineRevenue,
                'projected_revenue' => $projectedRevenue,
                'additional_revenue' => $additionalRevenue,
                'discount_cost' => $discountCost,
                'net_revenue' => $netRevenue,
                'additional_net_revenue' => $additionalNetRevenue,
                'roi_percentage' => $roi * 100,
                'payback_period_days' => $paybackPeriod,
                'risk_score' => $riskScore,
                'break_even_usage' => $breakEvenUsage,
                'safety_margin_percentage' => $safetyMargin * 100,
                'predicted_usage' => $predictedUsage,
                'confidence_level' => $calculateConfidenceLevel($riskScore, $safetyMargin),
            ];
        };
        

        // 1) Đẩy tồn kho: giảm phần trăm cho nhóm slow-moving
        $discountStock = min($maxDiscount, 25);
        
        // Calculate comprehensive economic metrics for slow-moving products
        $economicMetricsStock = $calcEconomicMetrics($topSlowIds, $topSlow);
        $baselineRevenueStock = (float) (array_sum(array_column($topSlow, 'price')) * 20);
        $predictedUsageStock = max(40, 10 * count($topSlowIds));
        $financialProjectionsStock = $calculateFinancialProjections($economicMetricsStock, $discountStock, 'percent', $predictedUsageStock, $baselineRevenueStock);
        
        $analysisPush = function() use ($topSlow, $discountStock, $minOrderAmount, $economicMetricsStock, $financialProjectionsStock){
            $lines = [];
            $lines[] = "📊 **PHÂN TÍCH KINH TẾ CHI TIẾT**";
            $lines[] = "";
            $lines[] = "🎯 **MỤC TIÊU**: Xả tồn hàng chậm xoay với Days-of-Cover cao";
            $lines[] = "💰 **MỨC GIẢM**: {$discountStock}% | Ngưỡng đơn tối thiểu: " . number_format((float)$minOrderAmount,0,',','.') . "₫";
            $lines[] = "";
            $lines[] = "📈 **DỰ BÁO TÀI CHÍNH**:";
            $lines[] = "• Doanh thu cơ sở: " . number_format($financialProjectionsStock['baseline_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu dự kiến: " . number_format($financialProjectionsStock['projected_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Tăng trưởng doanh thu: +" . number_format($financialProjectionsStock['revenue_uplift_percentage'], 1) . "%";
            $lines[] = "• Chi phí giảm giá: " . number_format($financialProjectionsStock['discount_cost'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu ròng: " . number_format($financialProjectionsStock['net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Lợi nhuận bổ sung: " . number_format($financialProjectionsStock['additional_net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "";
            $lines[] = "📊 **CHỈ SỐ HIỆU SUẤT**:";
            $lines[] = "• ROI: " . number_format($financialProjectionsStock['roi_percentage'], 1) . "%";
            $lines[] = "• Thời gian hoàn vốn: " . number_format($financialProjectionsStock['payback_period_days'], 0) . " ngày";
            $lines[] = "• Biên lợi nhuận TB: " . number_format($economicMetricsStock['avg_margin_rate'] * 100, 1) . "%";
            $lines[] = "• Giá trị tồn kho: " . number_format($economicMetricsStock['total_stock_value'], 0, ',', '.') . "₫";
            $lines[] = "• Tỷ lệ quay vòng: " . number_format($economicMetricsStock['avg_turnover_rate'], 2) . " lần/tháng";
            $lines[] = "";
            $lines[] = "⚠️ **ĐÁNH GIÁ RỦI RO**:";
            $lines[] = "• Điểm rủi ro: " . number_format($financialProjectionsStock['risk_score'] * 100, 0) . "/100";
            $lines[] = "• Mức độ tin cậy: " . $financialProjectionsStock['confidence_level'];
            $lines[] = "• Điểm hòa vốn: " . number_format($financialProjectionsStock['break_even_usage'], 0) . " lượt sử dụng";
            $lines[] = "• Biên an toàn: " . number_format($financialProjectionsStock['safety_margin_percentage'], 1) . "%";
            $lines[] = "";
            $lines[] = "🛍️ **SẢN PHẨM MỤC TIÊU**:";
            foreach (array_slice($topSlow, 0, 3) as $s) {
                $lines[] = "• {$s['name']} | DOC≈" . (int)round($s['doc']) . " | Tồn: {$s['stock']} | Bán 30d: {$s['qty30']}";
            }
            $lines[] = "";
            $lines[] = "💡 **KHUYẾN NGHỊ**: " . ($financialProjectionsStock['confidence_level'] === 'Cao' ? 
                "Chiến dịch có tiềm năng cao, nên triển khai ngay" : 
                ($financialProjectionsStock['confidence_level'] === 'Trung bình' ? 
                    "Chiến dịch khả thi, cần theo dõi sát sao" : 
                    "Cần điều chỉnh tham số hoặc cân nhắc kỹ"));
            return implode("\n", $lines);
        };

        $suggestions[] = [
            'campaign_id' => (string) Str::uuid(),
            'objective' => 'push_stock',
            'name_suggestion' => 'Xả tồn hàng chậm xoay',
            'type' => 'percent',
            'scope' => 'product',
            'discount_value' => $discountStock,
            'min_order_amount' => $minOrderAmount,
            'applicable_product_ids' => $topSlowIds,
            'applicable_category_ids' => [],
            'predicted_uplift_revenue' => (int) $financialProjectionsStock['projected_revenue'],
            'predicted_usage' => $predictedUsageStock,
            'risk_score' => $financialProjectionsStock['risk_score'],
            'insight' => 'Chọn theo Days-of-Cover và doanh số 30 ngày thấp',
            'products' => array_slice($topSlowMeta, 0, 4),
            'analysis' => $analysisPush(),
            'analysis_data' => [
                'items' => array_map(function($s){
                    return [
                        'name' => $s['name'],
                        'stock' => (int)$s['stock'],
                        'qty30' => (int)$s['qty30'],
                        'doc' => (float)$s['doc'],
                    ];
                }, $topSlow),
                'kpi' => [
                    'orders' => $ordersTotal,
                    'revenue' => $revenue,
                    'delivered' => $delivered,
                    'failed' => $failed,
                    'returned' => $returned,
                    'success_rate_overall' => $successRate,
                    'success_rate_terminal' => $successRateTerminal,
                    'repeat_rate' => $repeatRate,
                ],
                'economic_metrics' => $economicMetricsStock,
                'financial_projections' => $financialProjectionsStock,
            ],
        ];

        // 2) Tăng AOV: phần trăm theo đơn với ngưỡng ~ p75 AOV
        $discountAov = min($maxDiscount, 12);
        
        // Calculate economic metrics for AOV campaign
        $economicMetricsAov = $getEmptyEconomicMetrics(); // AOV campaign doesn't target specific products
        $baselineRevenueAov = (float) $revenue;
        $predictedUsageAov = 70;
        $financialProjectionsAov = $calculateFinancialProjections($economicMetricsAov, $discountAov, 'percent', $predictedUsageAov, $baselineRevenueAov);
        
        $analysisAov = function() use ($p75, $discountAov, $successRate, $aov, $financialProjectionsAov){
            $lines = [];
            $lines[] = "📊 **PHÂN TÍCH KINH TẾ CHI TIẾT**";
            $lines[] = "";
            $lines[] = "🎯 **MỤC TIÊU**: Tăng giá trị đơn hàng trung bình (AOV)";
            $lines[] = "💰 **MỨC GIẢM**: {$discountAov}% | Ngưỡng đơn: " . number_format((float)round($p75, -4),0,',','.') . "₫";
            $lines[] = "";
            $lines[] = "📈 **DỰ BÁO TÀI CHÍNH**:";
            $lines[] = "• Doanh thu cơ sở: " . number_format($financialProjectionsAov['baseline_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu dự kiến: " . number_format($financialProjectionsAov['projected_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Tăng trưởng doanh thu: +" . number_format($financialProjectionsAov['revenue_uplift_percentage'], 1) . "%";
            $lines[] = "• Chi phí giảm giá: " . number_format($financialProjectionsAov['discount_cost'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu ròng: " . number_format($financialProjectionsAov['net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Lợi nhuận bổ sung: " . number_format($financialProjectionsAov['additional_net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "";
            $lines[] = "📊 **CHỈ SỐ HIỆU SUẤT**:";
            $lines[] = "• ROI: " . number_format($financialProjectionsAov['roi_percentage'], 1) . "%";
            $lines[] = "• Thời gian hoàn vốn: " . number_format($financialProjectionsAov['payback_period_days'], 0) . " ngày";
            $lines[] = "• AOV hiện tại: " . number_format($aov, 0, ',', '.') . "₫";
            $lines[] = "• AOV mục tiêu (p75): " . number_format($p75, 0, ',', '.') . "₫";
            $lines[] = "• Tỷ lệ đơn thành công: " . number_format($successRate*100,1) . "%";
            $lines[] = "";
            $lines[] = "⚠️ **ĐÁNH GIÁ RỦI RO**:";
            $lines[] = "• Điểm rủi ro: " . number_format($financialProjectionsAov['risk_score'] * 100, 0) . "/100";
            $lines[] = "• Mức độ tin cậy: " . $financialProjectionsAov['confidence_level'];
            $lines[] = "• Điểm hòa vốn: " . number_format($financialProjectionsAov['break_even_usage'], 0) . " lượt sử dụng";
            $lines[] = "• Biên an toàn: " . number_format($financialProjectionsAov['safety_margin_percentage'], 1) . "%";
            $lines[] = "";
            $lines[] = "💡 **KHUYẾN NGHỊ**: " . ($financialProjectionsAov['confidence_level'] === 'Cao' ? 
                "Chiến dịch có tiềm năng cao, nên triển khai ngay" : 
                ($financialProjectionsAov['confidence_level'] === 'Trung bình' ? 
                    "Chiến dịch khả thi, cần theo dõi sát sao" : 
                    "Cần điều chỉnh tham số hoặc cân nhắc kỹ"));
            return implode("\n", $lines);
        };

        $suggestions[] = [
            'campaign_id' => (string) Str::uuid(),
            'objective' => 'increase_aov',
            'name_suggestion' => 'Ưu đãi giỏ hàng lớn',
            'type' => 'percent',
            'scope' => 'order',
            'discount_value' => $discountAov,
            'min_order_amount' => max($minOrderAmount, (int) round($p75, -4)),
            'applicable_product_ids' => [],
            'applicable_category_ids' => [],
            'predicted_uplift_revenue' => (int) $financialProjectionsAov['projected_revenue'],
            'predicted_usage' => $predictedUsageAov,
            'risk_score' => $financialProjectionsAov['risk_score'],
            'insight' => 'Ngưỡng thiết lập gần p75 AOV để kéo AOV lên',
            'analysis' => $analysisAov(),
            'analysis_data' => [
                'aov' => (float) $aov,
                'p75' => (float) $p75,
                'kpi' => [
                    'orders' => $ordersTotal,
                    'revenue' => $revenue,
                    'delivered' => $delivered,
                    'failed' => $failed,
                    'returned' => $returned,
                    'success_rate_overall' => $successRate,
                    'success_rate_terminal' => $successRateTerminal,
                    'repeat_rate' => $repeatRate,
                ],
                'economic_metrics' => $economicMetricsAov,
                'financial_projections' => $financialProjectionsAov,
            ],
        ];

        // 3) Seasonal: giảm nhẹ theo sản phẩm phổ biến (bán > 0 trong 30 ngày)
        $popular = array_values(array_filter($scored, fn($s)=> $s['qty30'] > 0));
        usort($popular, fn($a,$b)=> $b['qty30'] <=> $a['qty30']);
        $popularTop = array_slice($popular, 0, 10);
        shuffle($popularTop);
        $popularSlice = array_slice($popularTop, 0, 5);
        $popularIds = array_column($popularSlice, 'product_id');
        $popularMeta = [];
        foreach ($popularSlice as $s) {
            $prod = $products->firstWhere('id', $s['product_id']);
            if ($prod) {
                $popularMeta[] = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'brand' => $prod->brand,
                    'image' => $prod->image ?? null,
                ];
            }
        }
        // Calculate economic metrics for seasonal campaign
        $economicMetricsSeason = $calcEconomicMetrics($popularIds, $popularSlice);
        $baselineRevenueSeason = (float) ($revenue * 0.3); // Assume 30% of revenue from popular products
        $predictedUsageSeason = 90;
        $discountSeason = 30000;
        $financialProjectionsSeason = $calculateFinancialProjections($economicMetricsSeason, $discountSeason, 'fixed_amount', $predictedUsageSeason, $baselineRevenueSeason);
        
        $analysisSeasonal = function() use ($popularSlice, $discountSeason, $minOrderAmount, $economicMetricsSeason, $financialProjectionsSeason){
            $lines = [];
            $lines[] = "📊 **PHÂN TÍCH KINH TẾ CHI TIẾT**";
            $lines[] = "";
            $lines[] = "🎯 **MỤC TIÊU**: Mở rộng tệp khách hàng với sản phẩm phổ biến";
            $lines[] = "💰 **MỨC GIẢM**: " . number_format($discountSeason, 0, ',', '.') . "₫ | Ngưỡng đơn: " . number_format((float)$minOrderAmount,0,',','.') . "₫";
            $lines[] = "";
            $lines[] = "📈 **DỰ BÁO TÀI CHÍNH**:";
            $lines[] = "• Doanh thu cơ sở: " . number_format($financialProjectionsSeason['baseline_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu dự kiến: " . number_format($financialProjectionsSeason['projected_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Tăng trưởng doanh thu: +" . number_format($financialProjectionsSeason['revenue_uplift_percentage'], 1) . "%";
            $lines[] = "• Chi phí giảm giá: " . number_format($financialProjectionsSeason['discount_cost'], 0, ',', '.') . "₫";
            $lines[] = "• Doanh thu ròng: " . number_format($financialProjectionsSeason['net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "• Lợi nhuận bổ sung: " . number_format($financialProjectionsSeason['additional_net_revenue'], 0, ',', '.') . "₫";
            $lines[] = "";
            $lines[] = "📊 **CHỈ SỐ HIỆU SUẤT**:";
            $lines[] = "• ROI: " . number_format($financialProjectionsSeason['roi_percentage'], 1) . "%";
            $lines[] = "• Thời gian hoàn vốn: " . number_format($financialProjectionsSeason['payback_period_days'], 0) . " ngày";
            $lines[] = "• Biên lợi nhuận TB: " . number_format($economicMetricsSeason['avg_margin_rate'] * 100, 1) . "%";
            $lines[] = "• Giá trị tồn kho: " . number_format($economicMetricsSeason['total_stock_value'], 0, ',', '.') . "₫";
            $lines[] = "• Tỷ lệ quay vòng: " . number_format($economicMetricsSeason['avg_turnover_rate'], 2) . " lần/tháng";
            $lines[] = "";
            $lines[] = "⚠️ **ĐÁNH GIÁ RỦI RO**:";
            $lines[] = "• Điểm rủi ro: " . number_format($financialProjectionsSeason['risk_score'] * 100, 0) . "/100";
            $lines[] = "• Mức độ tin cậy: " . $financialProjectionsSeason['confidence_level'];
            $lines[] = "• Điểm hòa vốn: " . number_format($financialProjectionsSeason['break_even_usage'], 0) . " lượt sử dụng";
            $lines[] = "• Biên an toàn: " . number_format($financialProjectionsSeason['safety_margin_percentage'], 1) . "%";
            $lines[] = "";
            $lines[] = "🛍️ **SẢN PHẨM MỤC TIÊU**:";
            foreach (array_slice($popularSlice, 0, 3) as $s) {
                $lines[] = "• {$s['name']} | Bán 30d: {$s['qty30']} | Tồn: {$s['stock']}";
            }
            $lines[] = "";
            $lines[] = "💡 **KHUYẾN NGHỊ**: " . ($financialProjectionsSeason['confidence_level'] === 'Cao' ? 
                "Chiến dịch có tiềm năng cao, nên triển khai ngay" : 
                ($financialProjectionsSeason['confidence_level'] === 'Trung bình' ? 
                    "Chiến dịch khả thi, cần theo dõi sát sao" : 
                    "Cần điều chỉnh tham số hoặc cân nhắc kỹ"));
            return implode("\n", $lines);
        };

        $suggestions[] = [
            'campaign_id' => (string) Str::uuid(),
            'objective' => 'seasonal',
            'name_suggestion' => 'Mùa hương mới - Ưu đãi nhẹ',
            'type' => 'fixed_amount',
            'scope' => 'product',
            'discount_value' => $discountSeason,
            'min_order_amount' => $minOrderAmount,
            'applicable_product_ids' => $popularIds,
            'applicable_category_ids' => [],
            'predicted_uplift_revenue' => (int) $financialProjectionsSeason['projected_revenue'],
            'predicted_usage' => $predictedUsageSeason,
            'risk_score' => $financialProjectionsSeason['risk_score'],
            'insight' => 'Nhắm vào SKU đang có sức mua, giảm nhẹ để mở rộng tệp',
            'products' => array_slice($popularMeta, 0, 4),
            'analysis' => $analysisSeasonal(),
            'analysis_data' => [
                'items' => array_map(function($s){
                    return [
                        'name' => $s['name'],
                        'stock' => (int)$s['stock'],
                        'qty30' => (int)$s['qty30'],
                    ];
                }, $popularSlice),
                'kpi' => [
                    'orders' => $ordersTotal,
                    'revenue' => $revenue,
                    'delivered' => $delivered,
                    'failed' => $failed,
                    'returned' => $returned,
                    'success_rate_overall' => $successRate,
                    'success_rate_terminal' => $successRateTerminal,
                    'repeat_rate' => $repeatRate,
                ],
                'economic_metrics' => $economicMetricsSeason,
                'financial_projections' => $financialProjectionsSeason,
            ],
        ];

        return response()->json(['success' => true, 'suggestions' => $suggestions, 'meta' => [
            'aov' => (int) $aov,
            'p75' => (int) $p75,
            'window_days' => $windowDays,
            'source' => 'heuristic'
        ]]);
    }

    private function tryParseJson(string $text)
    {
        $trim = trim($text);
        $data = json_decode($trim, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;
        // try extract code block
        if (preg_match('/```[\w-]*\n([\s\S]*?)```/m', $text, $m)) {
            $data = json_decode(trim($m[1]), true);
            if (json_last_error() === JSON_ERROR_NONE) return $data;
        }
        // try find first [ ... ]
        if (preg_match('/\[.*\]/s', $text, $m)) {
            $data = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $data;
        }
        return null;
    }

    public function generateCopy(Request $request, LLMService $llm, DataService $dataService)
    {
        $suggestion = (array) $request->input('suggestion', []);
        $tone = (string) $request->input('tone', 'ngắn gọn, sang trọng, thân thiện');

        $realData = $dataService->getRealDataForLLM('khuyến mại, doanh thu, sản phẩm');
        $prompt = "Hãy viết nội dung cho chiến dịch khuyến mại sau, giọng văn {$tone}.\n" .
                  "Chiến dịch: " . json_encode($suggestion, JSON_UNESCAPED_UNICODE);

        $copy = $llm->chat($prompt, [
            'system' => 'Bạn là trợ lý marketing cho cửa hàng nước hoa. Viết nội dung ngắn gọn, rõ ràng, không hứa hẹn quá mức.',
            'real_data' => $realData,
        ]);

        // Tách cấu trúc tối giản từ đoạn sinh
        $result = [
            'title' => $suggestion['name_suggestion'] ?? 'Ưu đãi đặc biệt',
            'subtitle' => mb_strimwidth($copy, 0, 120, '...'),
            'cta' => 'Mua ngay',
            'long_description' => $copy,
        ];

        return response()->json(['success' => true, 'copy' => $result]);
    }

    public function generateImage(Request $request, LLMService $llm)
    {
        $suggestion = (array) $request->input('suggestion', []);
        $title = (string) ($suggestion['name_suggestion'] ?? 'Ưu đãi đặc biệt');
        $type = (string) ($suggestion['type'] ?? 'percent');
        $discount = (string) ($suggestion['discount_value'] ?? '');

        $prompt = "Thiết kế poster quảng cáo nước hoa sang trọng, nền sạch, typography rõ ràng, " .
                  "tông màu đen trắng xanh chàm, hiển thị text: '" . $title . "' và ưu đãi '".
                  ($type === 'percent' ? ($discount . '% OFF') : (is_numeric($discount) ? (number_format((float)$discount, 0) . '₫ OFF') : 'Ưu đãi')) . "'. " .
                  "Bố cục cân đối, ánh sáng studio, sản phẩm nước hoa, phong cách tối giản, high quality, 4k.";

        $url = $llm->generateImage($prompt, 1024, 1024);
        return response()->json(['success' => (bool)$url, 'image_url' => $url]);
    }

    public function launch(Request $request)
    {
        $suggestion = (array) $request->input('suggestion', []);
        $copy = (array) $request->input('copy', []);
        $settings = (array) $request->input('settings', []);

        // Validate suggestion & settings (an toàn)
        $validated = $request->validate([
            'suggestion.type' => 'required|in:percent,fixed_amount,free_shipping,buy_x_get_y',
            'suggestion.scope' => 'required|in:order,product',
            'suggestion.discount_value' => 'nullable|numeric|min:0|max:10000000',
            'suggestion.min_order_amount' => 'nullable|numeric|min:0|max:100000000',
            'suggestion.applicable_product_ids' => 'array',
            'suggestion.applicable_product_ids.*' => 'integer',
            'settings.priority' => 'nullable|integer|min:1|max:100',
            'settings.is_stackable' => 'nullable|boolean',
            'settings.usage_limit' => 'nullable|integer|min:1|max:100000',
            'settings.usage_limit_per_customer' => 'nullable|integer|min:1|max:1000',
        ]);

        // Trần giảm phần trăm
        if (($suggestion['type'] ?? 'percent') === 'percent') {
            $suggestion['discount_value'] = min(30, (float) ($suggestion['discount_value'] ?? 0));
        }

        $code = 'AI' . now()->format('Ymd') . strtoupper(Str::random(4));

        $promotion = Promotion::create([
            'code' => $code,
            'name' => $copy['title'] ?? ($suggestion['name_suggestion'] ?? 'AI Campaign'),
            'description' => $copy['long_description'] ?? null,
            'type' => $suggestion['type'] ?? 'percent',
            'scope' => $suggestion['scope'] ?? 'order',
            'discount_value' => $suggestion['discount_value'] ?? 0,
            'max_discount_amount' => $suggestion['max_discount_amount'] ?? null,
            'min_order_amount' => $suggestion['min_order_amount'] ?? null,
            'min_items' => $suggestion['min_items'] ?? null,
            'applicable_product_ids' => $suggestion['applicable_product_ids'] ?? [],
            'applicable_category_ids' => $suggestion['applicable_category_ids'] ?? [],
            'applicable_customer_group_ids' => $settings['applicable_customer_group_ids'] ?? [],
            'applicable_sales_channels' => $settings['applicable_sales_channels'] ?? [],
            'is_stackable' => (bool)($settings['is_stackable'] ?? false),
            'priority' => (int)($settings['priority'] ?? 10),
            'start_at' => $settings['start_at'] ?? now(),
            'end_at' => $settings['end_at'] ?? null,
            'is_active' => true,
            'usage_limit' => $settings['usage_limit'] ?? null,
            'usage_limit_per_customer' => $settings['usage_limit_per_customer'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'promotion_id' => $promotion->id,
            'redirect_url' => route('promotions.show', $promotion),
        ]);
    }

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'html' => 'required|string',
            'customer_ids' => 'array',
            'customer_ids.*' => 'integer',
        ]);

        $query = Customer::query();
        if (!empty($data['customer_ids'])) {
            $query->whereIn('id', $data['customer_ids']);
        } else {
            $query->whereNotNull('email');
        }
        $recipients = $query->select('email','name')->where('email','!=','')->limit(500)->get();

        $sent = 0;
        foreach ($recipients as $r) {
            try {
                Mail::send('emails.promotion', ['html' => $data['html'], 'name' => $r->name], function($m) use ($r, $data){
                    $m->to($r->email, $r->name)->subject($data['subject']);
                });
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Send promo email failed', ['email' => $r->email, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'sent' => $sent]);
    }

    private function percentile(array $values, float $percentile): ?float
    {
        $n = count($values);
        if ($n === 0) return null;
        sort($values);
        $rank = ($n - 1) * $percentile;
        $lower = (int) floor($rank);
        $upper = (int) ceil($rank);
        if ($lower === $upper) return (float) $values[$lower];
        $weight = $rank - $lower;
        return (float) ($values[$lower] * (1 - $weight) + $values[$upper] * $weight);
    }
}


