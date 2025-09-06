<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
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

        $sortBy = $request->get('sort_by', 'created_at');
        $allowedSorts = ['created_at','status','carrier','shipping_fee','cod_amount'];
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'created_at'; }
        $sortOrder = strtolower($request->get('sort_order','desc')) === 'asc' ? 'asc' : 'desc';

        $shipments = $query->orderBy($sortBy, $sortOrder)
            ->paginate($request->get('per_page', 20))
            ->appends($request->query());

        return view('shipping.shipments.index', compact('shipments','sortBy','sortOrder'));
    }
}


