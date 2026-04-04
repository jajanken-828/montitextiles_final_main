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
        // Orders ready to be pushed (approved and no queue entry)
        $salesOrders = PurchaseOrder::with('client')
            ->where('status', 'approved')
            ->whereDoesntHave('orderQueue')
            ->get();

        // Orders that have already been pushed (have an orderQueue)
        $pushedOrders = PurchaseOrder::with('client', 'orderQueue')
            ->whereHas('orderQueue')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'po_number' => $order->po_number,
                    'client' => $order->client,
                    'total_amount' => $order->total_amount,
                    'pushed_to' => $order->orderQueue->stage ?? 'SCM / Order Mgmt',
                    'pushed_at' => $order->orderQueue->created_at,
                ];
            });

        // Debug: Log counts to help diagnose
        \Log::info('Pending push orders: ' . $salesOrders->count());
        \Log::info('Already pushed orders: ' . $pushedOrders->count());

        return Inertia::render('Dashboard/ECO/Push', [
            'salesOrders' => $salesOrders,
            'pushedOrders' => $pushedOrders,
        ]);
    }

    public function pushToSCM(PurchaseOrder $order)
    {
        OrderQueue::updateOrCreate(
            ['purchase_order_id' => $order->id],
            ['stage' => 'eco_approved', 'notes' => 'Pushed from ECO']
        );
        return back()->with('success', "Order {$order->po_number} pushed to SCM.");
    }

    public function pushToOrderManagement(PurchaseOrder $order)
    {
        OrderQueue::updateOrCreate(
            ['purchase_order_id' => $order->id],
            ['stage' => 'eco_approved', 'notes' => 'Forwarded to Order Management']
        );
        return back()->with('success', "Order {$order->po_number} forwarded to Order Management.");
    }
}