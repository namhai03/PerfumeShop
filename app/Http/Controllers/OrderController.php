<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\N8nService;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->byType($request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->byStatus($request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('orders.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:sale,return,draft',
            'status' => 'required|in:new,processing,completed',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_method' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Generate order number
        $orderNumber = 'DH' . date('Ymd') . Str::random(4);

        // Calculate totals
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $discountAmount = $request->discount_amount ?? 0;
        $finalAmount = $totalAmount - $discountAmount;

        // Create order
        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $request->customer_id,
            'status' => $request->status,
            'type' => $request->type,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'notes' => $request->notes,
            'order_date' => $request->order_date,
            'delivery_date' => $request->delivery_date,
            'payment_method' => $request->payment_method,
            'delivery_address' => $request->delivery_address,
            'phone' => $request->phone,
        ]);

        // Create order items
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'custom_notes' => $item['custom_notes'] ?? null,
            ]);
        }

        // Sau khi tạo đơn hàng thành công, gửi thông báo qua n8n
        if ($order) {
            try {
                $n8nService = new N8nService();
                $n8nService->sendNewOrderNotification([
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'order_date' => $order->order_date->format('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Log lỗi nhưng không ảnh hưởng đến việc tạo đơn hàng
                Log::error('Failed to send N8N notification for new order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('orders.index')
            ->with('success', 'Đơn hàng đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $order->load(['customer', 'items.product']);
        
        return view('orders.edit', compact('order', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:sale,return,draft',
            'status' => 'required|in:new,processing,completed',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_method' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Calculate totals
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $discountAmount = $request->discount_amount ?? 0;
        $finalAmount = $totalAmount - $discountAmount;

        // Update order
        $order->update([
            'customer_id' => $request->customer_id,
            'status' => $request->status,
            'type' => $request->type,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'notes' => $request->notes,
            'order_date' => $request->order_date,
            'delivery_date' => $request->delivery_date,
            'payment_method' => $request->payment_method,
            'delivery_address' => $request->delivery_address,
            'phone' => $request->phone,
        ]);

        // Delete existing items and create new ones
        $order->items()->delete();
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'custom_notes' => $item['custom_notes'] ?? null,
            ]);
        }

        return redirect()->route('orders.index')
            ->with('success', 'Đơn hàng đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Đơn hàng đã được xóa thành công!');
    }

    /**
     * Show sales orders
     */
    public function sales(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->sales()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);
        return view('orders.sales', compact('orders'));
    }

    /**
     * Show return orders
     */
    public function returns(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->returns()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);
        return view('orders.returns', compact('orders'));
    }

    /**
     * Show draft orders
     */
    public function drafts(Request $request)
    {
        $query = Order::with(['customer', 'items.product'])
            ->drafts()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);
        return view('orders.drafts', compact('orders'));
    }
}
