<?php

namespace App\Http\Controllers\eco;

use App\Http\Controllers\Controller;
use App\Models\OrderQueue;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EcoPushController extends Controller
{
    public function index()
    {
        $salesOrders = PurchaseOrder::where('status', 'approved')
            ->whereDoesntHave('orderQueue') // not yet pushed
            ->get();
        return Inertia::render('Dashboard/ECO/Push', ['salesOrders' => $salesOrders]);
    }

    public function pushToSCM(PurchaseOrder $order)
    {
        // Create queue entry for SCM
        OrderQueue::updateOrCreate(
            ['purchase_order_id' => $order->id],
            ['stage' => 'eco_approved', 'notes' => 'Pushed from ECO']
        );
        return back()->with('success', "Order {$order->po_number} pushed to SCM.");
    }

    public function pushToOrderManagement(PurchaseOrder $order)
    {
        // In your system, order management might be a separate module
        // Here we simply update queue stage
        OrderQueue::updateOrCreate(
            ['purchase_order_id' => $order->id],
            ['stage' => 'eco_approved', 'notes' => 'Forwarded to Order Management']
        );
        return back()->with('success', "Order {$order->po_number} forwarded to Order Management.");
    }
}