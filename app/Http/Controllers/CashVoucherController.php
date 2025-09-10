<?php

namespace App\Http\Controllers;

use App\Models\CashVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashVoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = CashVoucher::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // account filters removed
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

        return view('cashbook.index', compact('vouchers'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'receipt');
        return view('cashbook.create', compact('type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reason' => 'nullable|string',
            'payer_name' => 'nullable|string',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $validated['voucher_code'] = $this->generateVoucherCode($validated['type']);
        $validated['status'] = 'pending';

        // no transfer logic

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
        $voucher->load(['attachments']);
        return view('cashbook.show', compact('voucher'));
    }

    public function edit(CashVoucher $voucher)
    {
        return view('cashbook.edit', compact('voucher'));
    }

    public function update(Request $request, CashVoucher $voucher)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reason' => 'nullable|string',
            'payer_name' => 'nullable|string',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $voucher->update($validated);

        return redirect()->route('cashbook.index')->with('success', 'Đã cập nhật phiếu thành công.');
    }

    public function destroy(CashVoucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('cashbook.index')->with('success', 'Đã xóa phiếu thành công.');
    }

    public function approve(Request $request, CashVoucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt phiếu ở trạng thái Chờ duyệt.');
        }

        $voucher->status = 'approved';
        $voucher->approved_by = Auth::id();
        $voucher->approved_at = now();
        $voucher->save();
        return back()->with('success', 'Đã duyệt phiếu thành công.');
    }

    public function cancel(Request $request, CashVoucher $voucher)
    {
        if ($voucher->status === 'cancelled') {
            return back()->with('error', 'Phiếu đã ở trạng thái Hủy.');
        }

        $voucher->status = 'cancelled';
        $voucher->save();
        return back()->with('success', 'Đã hủy phiếu thành công.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = CashVoucher::query();

        if ($request->filled('type')) { $query->where('type', $request->type); }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('from_date')) { $query->whereDate('transaction_date', '>=', $request->from_date); }
        if ($request->filled('to_date')) { $query->whereDate('transaction_date', '<=', $request->to_date); }
        // account filters removed
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('payer_name', 'like', "%{$search}%");
            });
        }

        $filename = 'cashbook_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Mã phiếu', 'Loại', 'Số tiền', 'Ngày giao dịch', 'Trạng thái', 'Tên người gửi', 'Mô tả']);
            $query->orderBy('transaction_date', 'desc')->chunk(500, function($rows) use ($handle) {
                foreach ($rows as $v) {
                    fputcsv($handle, [
                        $v->voucher_code,
                        $v->type,
                        (float)$v->amount,
                        optional($v->transaction_date)->format('Y-m-d'),
                        $v->status,
                        $v->payer_name,
                        $v->description,
                    ]);
                }
            });
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function generateVoucherCode($type)
    {
        $prefix = match($type) {
            'receipt' => 'PT',
            'payment' => 'PC',
            default => 'PH'
        };

        $date = now()->format('Ymd');
        $count = CashVoucher::whereDate('created_at', today())->count() + 1;
        
        return $prefix . $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
