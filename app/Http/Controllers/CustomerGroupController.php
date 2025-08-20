<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::orderBy('priority', 'desc')->orderBy('name')->paginate(20);
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
            'priority' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['priority'] = $validated['priority'] ?? 0;
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
            'priority' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $customer_group->update($validated);
        return redirect()->route('customer-groups.index')->with('success', 'Đã cập nhật nhóm khách hàng.');
    }

    public function destroy(CustomerGroup $customer_group)
    {
        $customer_group->delete();
        return redirect()->route('customer-groups.index')->with('success', 'Đã xóa nhóm khách hàng.');
    }
}


