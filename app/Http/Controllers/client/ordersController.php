<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\OrderQueue;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrdersController extends Controller
{
    /**
     * Display the client's purchase orders.
     *
     * @return \Inertia\Response
     */
    public function orders()
    {
        $client = Auth::guard('client')->user();

        $orders = PurchaseOrder::with(['items.product'])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Attach queue stage if needed
                $queue = OrderQueue::where('purchase_order_id', $order->id)->first();
                $order->queue_stage = $queue ? $queue->stage : null;

                return $order;
            });

        $stats = [
            'pending_orders' => $orders->where('status', 'credit_review')->count()
                + $orders->where('status', 'tier_assignment')->count()
                + $orders->where('status', 'pending_client_approval')->count(),
            'completed_orders' => $orders->where('status', 'approved')->count(),
            'recent_orders' => $orders->take(5),
        ];

        return Inertia::render('CLIENT/orders', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    /**
     * Accept a finalized purchase order (after tiering).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptPurchaseOrder(PurchaseOrder $order)
    {
        if ($order->client_id !== Auth::guard('client')->id()) {
            abort(403);
        }

        if ($order->status !== 'pending_client_approval') {
            return back()->with('error', 'This order is not awaiting your approval.');
        }

        DB::beginTransaction();
        try {
            $order->update(['status' => 'approved']);

            // Update queue stage
            OrderQueue::updateOrCreate(
                ['purchase_order_id' => $order->id],
                ['stage' => 'eco_approved']
            );

            DB::commit();

            return redirect()->back()->with('success', 'Order approved. It will now be processed by Supply Chain.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to approve order.');
        }
    }
}
