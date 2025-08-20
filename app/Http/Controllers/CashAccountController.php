<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\Request;

class CashAccountController extends Controller
{
    public function index()
    {
        try {
            $accounts = CashAccount::orderBy('type')->orderBy('name')->paginate(20);
        } catch (\Exception $e) {
            $accounts = collect()->paginate(20);
        }
        
        return view('cashbook.accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('cashbook.accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,other',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['balance'] = $validated['balance'] ?? 0;

        CashAccount::create($validated);

        return redirect()->route('cashbook.accounts.index')->with('success', 'Đã tạo tài khoản thành công.');
    }

    public function show(CashAccount $account)
    {
        return view('cashbook.accounts.show', compact('account'));
    }

    public function edit(CashAccount $account)
    {
        return view('cashbook.accounts.edit', compact('account'));
    }

    public function update(Request $request, CashAccount $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,other',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['balance'] = $validated['balance'] ?? 0;

        $account->update($validated);

        return redirect()->route('cashbook.accounts.index')->with('success', 'Đã cập nhật tài khoản thành công.');
    }

    public function destroy(CashAccount $account)
    {
        $account->delete();
        return redirect()->route('cashbook.accounts.index')->with('success', 'Đã xóa tài khoản thành công.');
    }
}
