<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::query()
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->paginate(20);

        // báo cáo đơn giản
        $usageStats = PromotionUsage::selectRaw('promotion_id, COUNT(*) as usage_count, SUM(discount_amount) as total_discount')
            ->groupBy('promotion_id')
            ->pluck('usage_count', 'promotion_id');
        $discountTotals = PromotionUsage::selectRaw('promotion_id, SUM(discount_amount) as total_discount')
            ->groupBy('promotion_id')
            ->pluck('total_discount', 'promotion_id');

        return view('promotions.index', compact('promotions', 'usageStats', 'discountTotals'));
    }

    public function create()
    {
        return view('promotions.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        Promotion::create($data);
        return redirect()->route('promotions.index')->with('success', 'Đã tạo chương trình khuyến mại.');
    }

    public function show(Promotion $promotion)
    {
        $usages = $promotion->usages()->latest()->paginate(10);
        return view('promotions.show', compact('promotion', 'usages'));
    }

    public function edit(Promotion $promotion)
    {
        return view('promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validatePayload($request, $promotion->id);
        $promotion->update($data);
        return redirect()->route('promotions.index')->with('success', 'Đã cập nhật chương trình khuyến mại.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('promotions.index')->with('success', 'Đã xóa chương trình khuyến mại.');
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        $ruleUniqueCode = 'nullable|string|max:100|unique:promotions,code' . ($id ? ',' . $id : '');
        $validated = $request->validate([
            'code' => $ruleUniqueCode,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,fixed_amount,free_shipping',
            'scope' => 'required|in:order,product',
            'discount_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_items' => 'nullable|integer|min:1',
            'applicable_product_ids' => 'nullable|array',
            'applicable_product_ids.*' => 'integer',
            'applicable_category_ids' => 'nullable|array',
            'applicable_category_ids.*' => 'integer',
            'applicable_customer_group_ids' => 'nullable|array',
            'applicable_customer_group_ids.*' => 'integer',
            'applicable_sales_channels' => 'nullable|array',
            'applicable_sales_channels.*' => 'string',
            'is_stackable' => 'boolean',
            'priority' => 'integer',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
        ]);

        // ép mảng rỗng -> null để gọn DB
        foreach (['applicable_product_ids','applicable_category_ids','applicable_customer_group_ids','applicable_sales_channels'] as $k) {
            if (isset($validated[$k]) && empty($validated[$k])) {
                $validated[$k] = null;
            }
        }
        return $validated;
    }
}


