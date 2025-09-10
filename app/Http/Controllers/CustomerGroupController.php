<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::orderBy('name')->paginate(20);
        return view('customers.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('customers.groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        // Normalize checkbox values
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);
        $validated['is_default'] = (bool)($validated['is_default'] ?? false);

        // Ensure only one default group
        if (!empty($validated['is_default'])) {
            CustomerGroup::where('is_default', true)->update(['is_default' => false]);
        }

        CustomerGroup::create($validated);

        return redirect()->route('customer-groups.index')->with('success', 'Đã tạo nhóm khách hàng.');
    }

    public function edit(CustomerGroup $customer_group)
    {
        return view('customers.groups.edit', ['group' => $customer_group]);
    }

    public function update(Request $request, CustomerGroup $customer_group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name,' . $customer_group->id,
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool)($validated['is_active'] ?? $customer_group->is_active);
        $validated['is_default'] = (bool)($validated['is_default'] ?? false);

        if (!empty($validated['is_default'])) {
            CustomerGroup::where('id', '!=', $customer_group->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $customer_group->update($validated);
        return redirect()->route('customer-groups.index')->with('success', 'Đã cập nhật nhóm khách hàng.');
    }

    public function show(CustomerGroup $customer_group)
    {
        $group = $customer_group->load(['customers' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }]);
        $customers = $customer_group->customers()->orderBy('created_at', 'desc')->paginate(20);
        return view('customers.groups.show', compact('group', 'customers'));
    }

    public function destroy(CustomerGroup $customer_group)
    {
        $customer_group->delete();
        return redirect()->route('customer-groups.index')->with('success', 'Đã xóa nhóm khách hàng.');
    }
}


