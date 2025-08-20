<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->with('group');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group_id')) {
            $query->where('customer_group_id', $request->group_id);
        }
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', (bool)$request->is_active);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $customers = $query->paginate($perPage)->appends($request->query());

        $groups = CustomerGroup::orderBy('priority', 'desc')->orderBy('name')->get();
        $types = Customer::query()->distinct()->pluck('customer_type')->filter()->values();

        return view('customers.index', compact('customers', 'groups', 'types'));
    }

    public function create()
    {
        $groups = CustomerGroup::orderBy('priority', 'desc')->orderBy('name')->get();
        return view('customers.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:100',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'source' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Đã tạo khách hàng.');
    }

    public function show(Customer $customer)
    {
        $customer->load('group');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $groups = CustomerGroup::orderBy('priority', 'desc')->orderBy('name')->get();
        return view('customers.edit', compact('customer', 'groups'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birthday' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:100',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'source' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Đã xóa khách hàng.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->route('customers.index')->with('error', 'Chưa chọn khách hàng nào.');
        }
        Customer::whereIn('id', $ids)->delete();
        return redirect()->route('customers.index')->with('success', 'Đã xóa ' . count($ids) . ' khách hàng.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:4096'
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $handle = fopen($path, 'r');
            if ($handle === false) {
                throw new \RuntimeException('Không thể mở file CSV');
            }

            $headers = [];
            if (($row = fgetcsv($handle)) !== false) {
                if (isset($row[0])) { $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]); }
                $headers = array_map(fn($h) => strtolower(trim($h)), $row);
            }

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === 1 && trim($row[0]) === '') { continue; }
                $data = [];
                foreach ($headers as $i => $key) { $data[$key] = $row[$i] ?? null; }

                if (empty($data['name'])) { continue; }

                $payload = [
                    'name' => $data['name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'birthday' => $data['birthday'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'ward' => $data['ward'] ?? null,
                    'customer_type' => $data['customer_type'] ?? null,
                    'source' => $data['source'] ?? null,
                    'tax_number' => $data['tax_number'] ?? null,
                    'company' => $data['company'] ?? null,
                    'note' => $data['note'] ?? null,
                    'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
                ];

                // map group by name if provided
                if (!empty($data['group'])) {
                    $group = CustomerGroup::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($data['group'])],
                        ['name' => $data['group']]
                    );
                    $payload['customer_group_id'] = $group->id;
                }

                // Identify by phone or email if available, else create new each time
                $customer = null;
                if (!empty($payload['phone'])) {
                    $customer = Customer::where('phone', $payload['phone'])->first();
                }
                if (!$customer && !empty($payload['email'])) {
                    $customer = Customer::where('email', $payload['email'])->first();
                }

                if ($customer) {
                    $customer->update($payload);
                } else {
                    Customer::create($payload);
                }
            }

            fclose($handle);
            return redirect()->route('customers.index')->with('success', 'Import khách hàng thành công!');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Lỗi khi import: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $filename = 'customers.csv';
        $columns = [
            'name','phone','email','gender','birthday','address','city','district','ward','customer_type','group','source','tax_number','company','note','is_active','total_spent','total_orders','created_at'
        ];

        $callback = function() use ($columns) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);
            Customer::with('group')->orderBy('id')->chunk(500, function($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $data = [
                        'name' => $row->name,
                        'phone' => $row->phone,
                        'email' => $row->email,
                        'gender' => $row->gender,
                        'birthday' => $row->birthday ? $row->birthday->format('Y-m-d') : null,
                        'address' => $row->address,
                        'city' => $row->city,
                        'district' => $row->district,
                        'ward' => $row->ward,
                        'customer_type' => $row->customer_type,
                        'group' => $row->group?->name,
                        'source' => $row->source,
                        'tax_number' => $row->tax_number,
                        'company' => $row->company,
                        'note' => $row->note,
                        'is_active' => (int) ($row->is_active ?? 0),
                        'total_spent' => (float) $row->total_spent,
                        'total_orders' => (int) $row->total_orders,
                        'created_at' => $row->created_at?->format('Y-m-d H:i:s'),
                    ];
                    $ordered = [];
                    foreach ($columns as $col) { $ordered[] = $data[$col] ?? ''; }
                    fputcsv($handle, $ordered);
                }
            });
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}


