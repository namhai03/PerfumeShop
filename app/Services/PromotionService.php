<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use Illuminate\Support\Collection;

class PromotionService
{
    /**
     * Tính toán khuyến mại tốt nhất cho giỏ hàng.
     * $cart structure:
     * - items: [ [product_id,int, category_ids:int[], price:float, qty:int], ... ]
     * - subtotal: float (nếu không truyền sẽ tự tính từ items)
     * - sales_channel: string|null ('online'|'offline'|...)
     * - customer_id: int|null
     * - customer_group_id: int|null
     * - shipping_fee: float|null
     */
    public function calculate(array $cart): array
    {
        $items = collect($cart['items'] ?? []);
        $subtotal = isset($cart['subtotal']) ? (float)$cart['subtotal'] : $this->calculateSubtotal($items);
        $salesChannel = $cart['sales_channel'] ?? null;
        $customerGroupId = $cart['customer_group_id'] ?? null;

        $eligible = $this->getEligiblePromotions($subtotal, $items, $salesChannel, $customerGroupId);

        // Nếu cho phép cộng dồn: áp lần lượt theo priority, ngược lại chọn tốt nhất
        $stackablePromos = $eligible->filter(fn($p) => (bool)$p->is_stackable)->sortByDesc('priority');
        $nonStackablePromos = $eligible->filter(fn($p) => !(bool)$p->is_stackable);

        $applied = [];
        $discountTotal = 0.0;
        $shippingDiscount = 0.0;

        // Áp non-stackable: chọn tốt nhất một cái
        if ($nonStackablePromos->isNotEmpty()) {
            $best = $nonStackablePromos->map(function(Promotion $p) use ($items, $subtotal) {
                return [
                    'promotion' => $p,
                    'discount' => $this->estimateDiscount($p, $items, $subtotal),
                    'shipping_discount' => $p->type === 'free_shipping' ? 1 : 0,
                ];
            })->sortByDesc('discount')->first();

            if ($best && $best['discount'] > 0) {
                $applied[] = $best['promotion'];
                $discountTotal += (float)$best['discount'];
            } elseif ($best && $best['shipping_discount']) {
                $applied[] = $best['promotion'];
            }
        }

        // Áp stackable: cộng dồn
        foreach ($stackablePromos as $p) {
            $d = $this->estimateDiscount($p, $items, $subtotal - $discountTotal);
            if ($d > 0 || $p->type === 'free_shipping') {
                $applied[] = $p;
                $discountTotal += (float)$d;
            }
        }

        // Tính free shipping nếu có CTKM free_shipping được áp dụng
        $hasFreeShip = collect($applied)->contains(fn($p) => $p->type === 'free_shipping');
        if ($hasFreeShip && isset($cart['shipping_fee'])) {
            $shippingDiscount = (float) $cart['shipping_fee'];
        }

        return [
            'applied_promotions' => array_values(array_map(fn($p) => $p->toArray(), $applied)),
            'discount_total' => max(0.0, (float)$discountTotal),
            'shipping_discount' => (float)$shippingDiscount,
        ];
    }

    public function recordUsages(array $result, array $cart, ?string $orderCode = null): void
    {
        $customerId = $cart['customer_id'] ?? null;
        foreach ($result['applied_promotions'] as $p) {
            $discount = 0.0;
            if (($p['type'] ?? null) === 'free_shipping') {
                $discount = (float)($result['shipping_discount'] ?? 0);
            } else {
                // tạm thời chia đều discount_total theo số CTKM không free ship
                // thực tế có thể ghi riêng từng mức giảm theo ước tính
                $discount = (float)$result['discount_total'];
            }
            PromotionUsage::create([
                'promotion_id' => $p['id'],
                'customer_id' => $customerId,
                'order_code' => $orderCode,
                'discount_amount' => $discount,
                'context' => [
                    'cart' => $cart,
                ],
            ]);
        }
    }

    private function calculateSubtotal(Collection $items): float
    {
        return (float)$items->sum(function($it){
            return (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 0);
        });
    }

    /** @return Collection<int,Promotion> */
    private function getEligiblePromotions(float $subtotal, Collection $items, ?string $salesChannel, ?int $customerGroupId): Collection
    {
        $now = now();
        $promos = Promotion::query()
            ->where('is_active', true)
            ->where(function($q) use ($now){
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function($q) use ($now){
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('priority')
            ->get();

        return $promos->filter(function(Promotion $p) use ($subtotal, $items, $salesChannel, $customerGroupId){
            if ($p->min_order_amount && $subtotal < (float)$p->min_order_amount) return false;
            if ($p->min_items && $items->sum('qty') < (int)$p->min_items) return false;
            if ($p->applicable_sales_channels && $salesChannel && !in_array($salesChannel, (array)$p->applicable_sales_channels, true)) return false;
            if ($p->applicable_customer_group_ids && $customerGroupId && !in_array($customerGroupId, (array)$p->applicable_customer_group_ids, true)) return false;

            // Nếu scoped theo product, cần có ít nhất 1 item match
            if ($p->scope === 'product') {
                $match = $items->first(function($it) use ($p){
                    $pidOk = empty($p->applicable_product_ids) || in_array($it['product_id'] ?? 0, (array)$p->applicable_product_ids, true);
                    $cats = (array)($it['category_ids'] ?? []);
                    $catOk = empty($p->applicable_category_ids) || count(array_intersect($cats, (array)$p->applicable_category_ids)) > 0;
                    return $pidOk && $catOk;
                });
                if (!$match) return false;
            }

            // TODO: usage_limit, per customer (có thể bổ sung sau bằng đếm PromotionUsage)
            return true;
        });
    }

    private function estimateDiscount(Promotion $p, Collection $items, float $baseAmount): float
    {
        if ($p->type === 'free_shipping') {
            return 0.0;
        }

        $eligibleAmount = $baseAmount;
        if ($p->scope === 'product') {
            $eligibleAmount = (float)$items->sum(function($it) use ($p){
                $pidOk = empty($p->applicable_product_ids) || in_array($it['product_id'] ?? 0, (array)$p->applicable_product_ids, true);
                $cats = (array)($it['category_ids'] ?? []);
                $catOk = empty($p->applicable_category_ids) || count(array_intersect($cats, (array)$p->applicable_category_ids)) > 0;
                if ($pidOk && $catOk) {
                    return (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 0);
                }
                return 0.0;
            });
        }

        $discount = 0.0;
        if ($p->type === 'percent') {
            $discount = $eligibleAmount * (float)$p->discount_value / 100.0;
            if (!empty($p->max_discount_amount)) {
                $discount = min($discount, (float)$p->max_discount_amount);
            }
        } elseif ($p->type === 'fixed_amount') {
            $discount = (float)$p->discount_value;
            $discount = min($discount, $eligibleAmount);
        }
        return max(0.0, (float)$discount);
    }
}


