<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerGroup;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'latestShipment'])
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
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15))->appends($request->query());

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with(['variants' => function($q){
            $q->orderBy('volume_ml');
        }])->orderBy('name')->get();
        $groups = CustomerGroup::orderBy('name')->where('is_active', true)->get(['id','name','discount_rate','min_order_amount','max_discount_amount']);
        
        return view('orders.create', compact('products','groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'type' => 'nullable|in:sale,return,draft',
            'status' => 'required|in:draft,confirmed,processing,shipping,delivered,failed,returned',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_method' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'ward' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required_without:items.*.product_variant_id|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Map type từ status trước khi xử lý tồn kho
        $mappedType = match($request->status) {
            'draft' => 'draft',
            'failed', 'returned' => 'return',
            default => 'sale',
        };

        // Validate stock availability cho đơn thuộc nhóm sale
        if ($mappedType === 'sale') {
            $this->validateStockAvailability($request->items);
        }

        // Generate order number
        $orderNumber = 'DH' . date('Ymd') . Str::random(4);

        // Calculate totals
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        // Áp dụng chiết khấu nhóm khách hàng nếu chọn
        $groupDiscount = 0;
        $appliedGroupId = null;
        if ($request->filled('customer_group_id')) {
            $group = CustomerGroup::find($request->customer_group_id);
            if ($group && $group->is_active) {
                $appliedGroupId = $group->id;
                // Điều kiện: min_order_amount
                if (!$group->min_order_amount || $totalAmount >= $group->min_order_amount) {
                    $rate = (float)($group->discount_rate ?? 0);
                    if ($rate > 0) {
                        $groupDiscount = round($totalAmount * ($rate / 100), 2);
                        if ($group->max_discount_amount) {
                            $groupDiscount = min($groupDiscount, (float)$group->max_discount_amount);
                        }
                    }
                }
            }
        }

        // Cho phép nhập thêm giảm giá thủ công, nhưng không vượt quá tổng - groupDiscount
        $manualDiscount = (float)($request->discount_amount ?? 0);
        $discountAmount = $groupDiscount + $manualDiscount;
        $finalAmount = $totalAmount - $discountAmount;

        // Use database transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            // Find or create customer
            $customer = Customer::where('name', $request->customer_name)
                ->where('phone', $request->phone)
                ->first();
            
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'phone' => $request->phone,
                    'address' => $request->delivery_address,
                    'customer_type' => 'walkin',
                    'source' => 'offline',
                    'is_active' => true,
                ]);
            } else {
                // Update customer information if provided
                $updateData = [];
                if ($request->phone && $customer->phone !== $request->phone) {
                    $updateData['phone'] = $request->phone;
                }
                if ($request->delivery_address && $customer->address !== $request->delivery_address) {
                    $updateData['address'] = $request->delivery_address;
                }
                if (!empty($updateData)) {
                    $customer->update($updateData);
                }
            }

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'customer_name' => $request->customer_name,
                'status' => $request->status,
                // Map type theo status: nhóm đơn đi (confirmed, processing, shipping, delivered) => sale; đơn nháp => draft; thất bại/hoàn trả => return
                'type' => $mappedType,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'customer_group_id' => $appliedGroupId,
                'notes' => $request->notes,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'payment_method' => $request->payment_method,
                'delivery_address' => $request->delivery_address,
                'ward' => $request->ward,
                'city' => $request->city,
                'phone' => $request->phone,
            ]);

            // Create order items and update inventory
            foreach ($request->items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'custom_notes' => $item['custom_notes'] ?? null,
                ]);

                // Update inventory based on order type
                $this->updateInventoryForOrder($order, $orderItem);
            }

            // Update customer statistics
            $customer->increment('total_orders');
            $customer->increment('total_spent', $finalAmount);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create order', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage());
        }


        return redirect()->route('orders.index')
            ->with('success', 'Đơn hàng đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'items.variant', 'latestShipment']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $products = Product::with(['variants' => function($q){
            $q->orderBy('volume_ml');
        }])->orderBy('name')->get();
        $order->load(['customer', 'items.product']);
        
        return view('orders.edit', compact('order', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'type' => 'nullable|in:sale,return,draft',
            'status' => 'required|in:draft,confirmed,processing,shipping,delivered,failed,returned',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_method' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'ward' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required_without:items.*.product_variant_id|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Map type từ status trước khi xử lý tồn kho
        $mappedType = match($request->status) {
            'draft' => 'draft',
            'failed', 'returned' => 'return',
            default => 'sale',
        };

        // Validate stock availability cho đơn thuộc nhóm sale
        if ($mappedType === 'sale') {
            $this->validateStockAvailability($request->items);
        }

        // Calculate totals
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $discountAmount = $request->discount_amount ?? 0;
        $finalAmount = $totalAmount - $discountAmount;

        // Find or create customer
        $customer = Customer::where('name', $request->customer_name)
            ->where('phone', $request->phone)
            ->first();
        
        if (!$customer) {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'phone' => $request->phone,
                'address' => $request->delivery_address,
                'customer_type' => 'walkin',
                'source' => 'offline',
                'is_active' => true,
            ]);
        } else {
            // Update customer information if provided
            $updateData = [];
            if ($request->phone && $customer->phone !== $request->phone) {
                $updateData['phone'] = $request->phone;
            }
            if ($request->delivery_address && $customer->address !== $request->delivery_address) {
                $updateData['address'] = $request->delivery_address;
            }
            if (!empty($updateData)) {
                $customer->update($updateData);
            }
        }

        // Update order
        $order->update([
            'customer_id' => $customer->id,
            'customer_name' => $request->customer_name,
            'status' => $request->status,
            'type' => match($request->status) {
                'draft' => 'draft',
                'failed', 'returned' => 'return',
                default => 'sale',
            },
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'notes' => $request->notes,
            'order_date' => $request->order_date,
            'delivery_date' => $request->delivery_date,
            'payment_method' => $request->payment_method,
            'delivery_address' => $request->delivery_address,
            'ward' => $request->ward,
            'city' => $request->city,
            'phone' => $request->phone,
        ]);

        // Delete existing items and create new ones
        $order->items()->delete();
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['product_variant_id'] ?? null,
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
        DB::beginTransaction();
        
        try {
            // Restore inventory for sale orders
            if ($order->type === 'sale') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    $beforeStock = $product->stock;
                    $afterStock = $beforeStock + $item->quantity;
                    
                    $product->update(['stock' => $afterStock]);
                    
                    // Create inventory movement record
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'return',
                        'quantity_change' => $item->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'performed_by' => null,
                        'note' => "Hủy đơn hàng {$order->order_number}",
                        'transaction_date' => now(),
                        'reference_id' => $order->order_number,
                        'order_id' => $order->id,
                    ]);
                }
            }
            
            // For return orders, subtract from stock when deleting
            if ($order->type === 'return') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    $beforeStock = $product->stock;
                    $afterStock = $beforeStock - $item->quantity;
                    
                    if ($afterStock < 0) {
                        throw new \Exception("Không thể hủy đơn trả hàng. Tồn kho sẽ âm.");
                    }
                    
                    $product->update(['stock' => $afterStock]);
                    
                    // Create inventory movement record
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'export',
                        'quantity_change' => -$item->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'performed_by' => null,
                        'note' => "Hủy đơn trả hàng {$order->order_number}",
                        'transaction_date' => now(),
                        'reference_id' => $order->order_number,
                        'order_id' => $order->id,
                    ]);
                }
            }
            
            // Update customer statistics before deleting
            if ($order->customer_id) {
                $customer = $order->customer;
                $customer->decrement('total_orders');
                $customer->decrement('total_spent', $order->final_amount);
            }
            
            $order->items()->delete();
            $order->delete();
            
            DB::commit();
            
            return redirect()->route('orders.index')
                ->with('success', 'Đơn hàng đã được xóa thành công!');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Không thể xóa đơn hàng: ' . $e->getMessage());
        }
    }

    /**
     * Show sales orders
     */
    public function sales(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'latestShipment'])
            ->sales()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15))->appends($request->query());
        return view('orders.sales', compact('orders'));
    }

    /**
     * Show return orders
     */
    public function returns(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'latestShipment'])
            ->returns()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15))->appends($request->query());
        return view('orders.returns', compact('orders'));
    }

    /**
     * Show draft orders
     */
    public function drafts(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'latestShipment'])
            ->drafts()
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15))->appends($request->query());
        return view('orders.drafts', compact('orders'));
    }

    /**
     * Validate stock availability for sale orders
     */
    private function validateStockAvailability(array $items)
    {
        foreach ($items as $item) {
            if (!empty($item['product_variant_id'])) {
                $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                if (!$variant) {
                    throw new \Exception("Chiết không tồn tại.");
                }
                if (!$variant->is_active) {
                    throw new \Exception("Chiết '{$variant->sku}' đã ngừng bán.");
                }
                if ($variant->stock < $item['quantity']) {
                    throw new \Exception("Chiết '{$variant->sku}' chỉ còn {$variant->stock}, không đủ để bán {$item['quantity']}.");
                }
                continue;
            }

            $product = Product::find($item['product_id'] ?? null);
            
            if (!$product) {
                throw new \Exception("Sản phẩm không tồn tại.");
            }

            if (!$product->is_active) {
                throw new \Exception("Sản phẩm '{$product->name}' đã ngừng bán.");
            }

            if ($product->stock < $item['quantity']) {
                throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$product->stock} sản phẩm, không đủ để bán {$item['quantity']} sản phẩm.");
            }

            // Check expiry date if exists
            if ($product->expiry_date && $product->expiry_date < now()) {
                throw new \Exception("Sản phẩm '{$product->name}' đã hết hạn sử dụng.");
            }
        }
    }

    /**
     * Update inventory based on order type
     */
    private function updateInventoryForOrder(Order $order, $orderItem)
    {
        // Nếu là chiết thì trừ kho từ variant, ngược lại trừ từ product
        $product = $orderItem->variant ? $orderItem->variant : $orderItem->product;
        $quantity = $orderItem->quantity;
        
        // Determine inventory change based on order type
        $quantityChange = 0;
        $movementType = '';
        
        switch ($order->type) {
            case 'sale':
                $quantityChange = -$quantity; // Subtract from stock
                $movementType = 'export';
                break;
            case 'return':
                $quantityChange = $quantity; // Add to stock
                $movementType = 'return';
                break;
            case 'draft':
                // Don't update inventory for drafts
                return;
        }

        // Update product stock
        $beforeStock = $product->stock;
        $afterStock = $beforeStock + $quantityChange;
        
        $product->update(['stock' => $afterStock]);

        // Create inventory movement record
        InventoryMovement::create([
            // Luôn ghi nhận theo product cha để không vi phạm FK khi item là variant
            'product_id' => $orderItem->product_id,
            'type' => $movementType,
            'quantity_change' => $quantityChange,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'performed_by' => null, // TODO: Add user authentication
            'note' => "Đơn hàng {$order->order_number} - {$order->type_text}",
            'transaction_date' => now(),
            'reference_id' => $order->order_number,
            'order_id' => $order->id,
        ]);
    }
}
