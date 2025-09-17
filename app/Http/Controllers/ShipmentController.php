<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\Order;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::query();

        if ($request->filled('search')) {
            $s = $request->get('search');
            $query->where(function($q) use ($s){
                $q->where('order_code','like',"%{$s}%")
                  ->orWhere('tracking_code','like',"%{$s}%")
                  ->orWhere('recipient_name','like',"%{$s}%")
                  ->orWhere('recipient_phone','like',"%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array)$request->get('status'));
        }
        if ($request->filled('branch')) { $query->where('branch', $request->branch); }
        if ($request->filled('region')) { $query->where('region', $request->region); }

        // Date range filter (from-to) theo created_at
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $allowedSorts = ['created_at','status','carrier','shipping_fee','cod_amount'];
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'created_at'; }
        $sortOrder = strtolower($request->get('sort_order','desc')) === 'asc' ? 'asc' : 'desc';

        $shipments = $query->orderBy($sortBy, $sortOrder)
            ->paginate($request->get('per_page', 20))
            ->appends($request->query());

        return view('shipping.shipments.index', compact('shipments','sortBy','sortOrder'));
    }

    public function create()
    {
        return view('shipping.shipments.create');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('events');
        return view('shipping.shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        return view('shipping.shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'province' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'cod_amount' => 'nullable|numeric|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
        ]);

        $shipment->update($validated);

        return redirect()->route('shipments.show', $shipment)->with('success', 'Cập nhật vận đơn thành công.');
    }

    public function destroy(Shipment $shipment)
    {
        // Không xóa nếu đã kết thúc? Tùy chính sách, tạm thời cho phép xóa
        $shipment->delete();
        return redirect()->route('shipments.index')->with('success', 'Đã xóa vận đơn.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Danh sách đơn hàng: mỗi item có order_code và cod_amount
            'orders' => 'required|array|min:1',
            'orders.*.order_code' => 'required|string|max:50|exists:orders,order_number',
            'orders.*.cod_amount' => 'nullable|numeric|min:0',

            'carrier' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:500',
            'province' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending_pickup,picked_up,in_transit,retry,returning,returned,delivered,failed,cancelled',
            'shipping_fee' => 'nullable|numeric|min:0',
            'weight_grams' => 'nullable|integer|min:0',
            'picked_up_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
        ]);

        $ordersInput = collect($validated['orders'])
            ->filter(fn($o) => !empty($o['order_code']))
            ->values();

        if ($ordersInput->isEmpty()) {
            return back()->withInput()->with('error', 'Vui lòng nhập ít nhất một mã đơn hàng hợp lệ.');
        }

        if (empty($validated['status'])) {
            $validated['status'] = 'pending_pickup';
        }

        // Lấy thông tin từ đơn đầu tiên để sinh mã và fallback người nhận
        $firstOrderCode = $ordersInput[0]['order_code'];
        $firstOrder = Order::where('order_number', $firstOrderCode)->first();

        $recipientName = $validated['recipient_name'] ?? null;
        $recipientPhone = $validated['recipient_phone'] ?? null;
        $addressLine = $validated['address_line'] ?? null;
        $province = $validated['province'] ?? null;
        $ward = $validated['ward'] ?? null;

        if ($firstOrder) {
            $recipientName = $recipientName ?: $firstOrder->customer_name;
            $recipientPhone = $recipientPhone ?: $firstOrder->phone;
            $addressLine = $addressLine ?: $firstOrder->delivery_address;
            $ward = $ward ?: $firstOrder->ward;
            $province = $province ?: $firstOrder->city;
        }

        // Tổng COD = tổng của từng đơn
        $totalCod = $ordersInput->sum(function ($o) {
            return (float)($o['cod_amount'] ?? 0);
        });

        // Tự sinh tracking_code (dựa trên mã đơn đầu tiên nếu có)
        $trackingCode = $this->generateTrackingCode($firstOrderCode);

        $shipment = Shipment::create([
            'order_code' => $firstOrderCode, // giữ reference đơn đầu tiên để tương thích cũ
            'tracking_code' => $trackingCode,
            'carrier' => $validated['carrier'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'region' => $validated['region'] ?? null,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'address_line' => $addressLine,
            'province' => $province,
            'ward' => $ward,
            'status' => $validated['status'],
            'cod_amount' => $totalCod,
            'shipping_fee' => $validated['shipping_fee'] ?? 0,
            'weight_grams' => $validated['weight_grams'] ?? 0,
        ]);

        // Gắn các đơn hàng vào pivot với COD từng đơn
        $attachData = [];
        foreach ($ordersInput as $item) {
            $order = Order::where('order_number', $item['order_code'])->first();
            if ($order) {
                $attachData[$order->id] = ['cod_amount' => (float)($item['cod_amount'] ?? 0)];
            }
        }
        if (!empty($attachData)) {
            $shipment->ordersMany()->attach($attachData);
        }

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $shipment->status,
            'event_at' => now(),
            'note' => 'Tạo vận đơn',
        ]);

        return redirect()->route('shipments.index')
            ->with('success', 'Tạo vận đơn thành công.');
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $request->validate([
            'status' => 'required|in:pending_pickup,picked_up,in_transit,retry,returning,returned,delivered,failed,cancelled',
            'note' => 'nullable|string|max:255',
        ]);

        $status = $request->get('status');
        $ts = now();

        // Ràng buộc chuyển trạng thái:
        // - 'delivered', 'returned', 'cancelled' là kết thúc, không cho cập nhật tiếp
        // - 'failed' chỉ cho phép chuyển sang 'returned' (hoàn hàng thành công)
        if (in_array($shipment->status, ['delivered','returned','cancelled'])) {
            return redirect()->back()->with('error', 'Vận đơn đã kết thúc, không thể cập nhật.');
        }
        if ($shipment->status === 'failed' && $status !== 'returned') {
            return redirect()->back()->with('error', 'Vận đơn thất bại chỉ có thể chuyển sang trạng thái hoàn hàng thành công.');
        }

        // Luồng theo đặc tả:
        // pending_pickup (Đang lấy hàng) -> cancelled | picked_up
        // picked_up (Đã lấy hàng) -> cancelled | in_transit
        // in_transit (Đang giao hàng) -> delivered | returning | failed
        // returning (Hoàn hàng) -> returned
        // failed (Thất bại) -> returned
        $allowedTransitions = [
            'pending_pickup' => ['picked_up', 'cancelled'],
            'picked_up' => ['in_transit', 'cancelled'],
            'in_transit' => ['delivered', 'returning', 'failed'],
            'returning' => ['returned'],
            'failed' => ['returned'],
            // Không cho chuyển từ các trạng thái kết thúc
            'delivered' => [],
            'returned' => [],
            'cancelled' => [],
        ];

        if (isset($allowedTransitions[$shipment->status]) && !in_array($status, $allowedTransitions[$shipment->status])) {
            return redirect()->back()->with('error', 'Chuyển trạng thái không hợp lệ từ trạng thái hiện tại.');
        }

        $update = ['status' => $status];
        switch ($status) {
            case 'picked_up':
                $update['picked_up_at'] = $ts;
                break;
            case 'delivered':
                $update['delivered_at'] = $ts;
                break;
            case 'failed':
                $update['failed_at'] = $ts;
                break;
            case 'returning':
                $update['returning_at'] = $ts;
                break;
            case 'returned':
                $update['returned_at'] = $ts;
                break;
        }

        $shipment->update($update);
        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'event_at' => $ts,
            'note' => $request->get('note'),
        ]);

        // Đồng bộ trạng thái Đơn hàng theo trạng thái Vận đơn
        // Mapping nghiệp vụ:
        // - pending_pickup => processing (đang chuẩn bị)
        // - picked_up|in_transit|retry|returning => shipping (đang giao)
        // - delivered => delivered (đã nhận)
        // - failed => failed (giao thất bại)
        // - returned => returned (hoàn hàng thành công)
        if (!empty($shipment->order_code)) {
            $order = Order::where('order_number', $shipment->order_code)->first();
            if ($order) {
                $mapped = match($status) {
                    'pending_pickup' => Order::STATUS_PROCESSING,
                    'picked_up', 'in_transit', 'retry', 'returning' => Order::STATUS_SHIPPING,
                    'delivered' => Order::STATUS_DELIVERED,
                    'failed' => Order::STATUS_FAILED,
                    'returned' => Order::STATUS_RETURNED,
                    'cancelled' => $order->status, // không thay đổi đơn khi hủy vận đơn
                    default => $order->status,
                };

                // Không ghi đè trạng thái đã kết thúc theo hướng lùi
                $terminal = [Order::STATUS_DELIVERED, Order::STATUS_RETURNED];
                if (!in_array($order->status, $terminal)) {
                    // Nếu đang failed và mapped là returned (hoàn hàng sau thất bại) thì cho phép
                    if ($order->status === Order::STATUS_FAILED && $mapped !== Order::STATUS_RETURNED) {
                        // giữ nguyên failed nếu không chuyển sang returned
                    } else {
                        if ($mapped !== $order->status) {
                            $order->update(['status' => $mapped]);
                        }
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái vận đơn thành công.');
    }

    private function generateTrackingCode(?string $orderCode): string
    {
        $prefix = $orderCode ? substr(preg_replace('/[^A-Z0-9]/','', strtoupper($orderCode)), -6) : 'PS';
        $rand = strtoupper(bin2hex(random_bytes(3)));
        return $prefix . '-' . date('ymd') . '-' . $rand;
    }
}


