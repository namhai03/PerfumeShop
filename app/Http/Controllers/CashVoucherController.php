<?php

namespace App\Http\Controllers;

use App\Models\CashVoucher;
use App\Models\CashAccount;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CashVoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = CashVoucher::query()->with(['fromAccount', 'toAccount']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $vouchers = $query->paginate($perPage)->appends($request->query());

        // Lấy tài khoản và khách hàng một cách an toàn
        try {
            $accounts = CashAccount::where('is_active', true)->get();
        } catch (\Exception $e) {
            $accounts = collect();
        }

        try {
            $customers = Customer::where('is_active', true)->get();
        } catch (\Exception $e) {
            $customers = collect();
        }

        return view('cashbook.index', compact('vouchers', 'accounts', 'customers'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'receipt');
        
        // Lấy tài khoản và khách hàng một cách an toàn
        try {
            $accounts = CashAccount::where('is_active', true)->get();
        } catch (\Exception $e) {
            $accounts = collect();
        }

        try {
            $customers = Customer::where('is_active', true)->get();
        } catch (\Exception $e) {
            $customers = collect();
        }

        return view('cashbook.create', compact('type', 'accounts', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reason' => 'nullable|string',
            'payer_group' => 'nullable|string',
            'payer_name' => 'nullable|string',
            'payer_id' => 'nullable|integer',
            'payer_type' => 'nullable|string',
            // Ràng buộc theo nghiệp vụ
            'from_account_id' => 'nullable|exists:cash_accounts,id|required_if:type,payment,transfer',
            'to_account_id' => 'nullable|exists:cash_accounts,id|required_if:type,receipt,transfer|different:from_account_id',
            'branch_id' => 'nullable|integer',
            'transaction_date' => 'required|date',
            'reference' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $validated['voucher_code'] = $this->generateVoucherCode($validated['type']);
        $validated['status'] = 'pending';

        // Kiểm tra bổ sung cho chuyển quỹ: bắt buộc khác tài khoản và có đủ 2 phía
        if ($validated['type'] === 'transfer') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                return back()->withInput()->with('error', 'Chuyển quỹ cần chọn đủ tài khoản nguồn và đích.');
            }
            if ((int)$validated['from_account_id'] === (int)$validated['to_account_id']) {
                return back()->withInput()->with('error', 'Tài khoản nguồn và đích không được trùng nhau.');
            }
        }

        $voucher = CashVoucher::create($validated);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('voucher-attachments', 'public');
                $voucher->attachments()->create([
                    'file_path' => '/storage/' . $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('cashbook.index')->with('success', 'Đã tạo phiếu thành công.');
    }

    public function show(CashVoucher $voucher)
    {
        $voucher->load(['fromAccount', 'toAccount', 'attachments']);
        return view('cashbook.show', compact('voucher'));
    }

    public function edit(CashVoucher $voucher)
    {
        // Lấy tài khoản và khách hàng một cách an toàn
        try {
            $accounts = CashAccount::where('is_active', true)->get();
        } catch (\Exception $e) {
            $accounts = collect();
        }

        try {
            $customers = Customer::where('is_active', true)->get();
        } catch (\Exception $e) {
            $customers = collect();
        }

        return view('cashbook.edit', compact('voucher', 'accounts', 'customers'));
    }

    public function update(Request $request, CashVoucher $voucher)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reason' => 'nullable|string',
            'payer_group' => 'nullable|string',
            'payer_name' => 'nullable|string',
            'payer_id' => 'nullable|integer',
            'payer_type' => 'nullable|string',
            // Cho phép cập nhật account theo nghiệp vụ hiện tại của phiếu
            'from_account_id' => 'nullable|exists:cash_accounts,id' . ($voucher->type !== 'receipt' ? '|required' : ''),
            'to_account_id' => 'nullable|exists:cash_accounts,id' . ($voucher->type !== 'payment' ? '|required' : ''),
            'branch_id' => 'nullable|integer',
            'transaction_date' => 'required|date',
            'reference' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        if ($voucher->type === 'transfer') {
            if (empty($validated['from_account_id']) || empty($validated['to_account_id'])) {
                return back()->withInput()->with('error', 'Chuyển quỹ cần chọn đủ tài khoản nguồn và đích.');
            }
            if ((int)($validated['from_account_id'] ?? 0) === (int)($validated['to_account_id'] ?? -1)) {
                return back()->withInput()->with('error', 'Tài khoản nguồn và đích không được trùng nhau.');
            }
        }

        $voucher->update($validated);

        return redirect()->route('cashbook.index')->with('success', 'Đã cập nhật phiếu thành công.');
    }

    public function destroy(CashVoucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('cashbook.index')->with('success', 'Đã xóa phiếu thành công.');
    }

    private function generateVoucherCode($type)
    {
        $prefix = match($type) {
            'receipt' => 'PT',
            'payment' => 'PC',
            'transfer' => 'CQ',
            default => 'PH'
        };

        $date = now()->format('Ymd');
        $count = CashVoucher::whereDate('created_at', today())->count() + 1;
        
        return $prefix . $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
