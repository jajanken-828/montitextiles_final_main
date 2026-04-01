<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\ClientQuotation;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the client dashboard with stats and calendar events.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $client = Auth::guard('client')->user();

        // Stats
        $totalOrders = PurchaseOrder::where('client_id', $client->id)->count();
        $totalQuotationsSent = ClientQuotation::where('client_id', $client->id)
            ->where('status', 'sent')
            ->count();
        $pendingSettlements = PurchaseOrder::where('client_id', $client->id)
            ->whereIn('status', ['approved', 'pending_client_approval'])
            ->where('total_amount', '>', 0)
            ->count();

        // Calendar events
        $events = [];

        // Quotations sent by client
        $quotations = ClientQuotation::where('client_id', $client->id)
            ->whereIn('status', ['sent', 'under_review'])
            ->get();
        foreach ($quotations as $q) {
            $events[] = [
                'date' => $q->issue_date,
                'title' => 'Quotation Sent: ' . $q->quotation_number,
                'type' => 'quotation_sent',
                'id' => $q->id,
                'details' => [
                    'quotation_number' => $q->quotation_number,
                    'grand_total' => $q->grand_total,
                    'valid_until' => $q->valid_until,
                ],
            ];
        }

        // Quotations received from ECO (awaiting client acceptance)
        $receivedQuotes = ClientQuotation::where('client_id', $client->id)
            ->where('status', 'under_review')
            ->get();
        foreach ($receivedQuotes as $q) {
            $events[] = [
                'date' => $q->updated_at->toDateString(),
                'title' => 'Quotation Ready: ' . $q->quotation_number,
                'type' => 'quotation_ready',
                'id' => $q->id,
                'details' => [
                    'quotation_number' => $q->quotation_number,
                    'grand_total' => $q->grand_total,
                    'valid_until' => $q->valid_until,
                ],
            ];
        }

        // Orders awaiting final client approval (after tiering)
        $pendingOrders = PurchaseOrder::where('client_id', $client->id)
            ->where('status', 'pending_client_approval')
            ->get();
        foreach ($pendingOrders as $order) {
            $events[] = [
                'date' => $order->updated_at->toDateString(),
                'title' => 'Order #' . $order->po_number . ' Ready for Approval',
                'type' => 'order_ready',
                'id' => $order->id,
                'details' => [
                    'po_number' => $order->po_number,
                    'total_amount' => $order->total_amount,
                    'tier_level' => $order->tier_level,
                ],
            ];
        }

        // Payment deadlines for approved orders
        $approvedOrders = PurchaseOrder::where('client_id', $client->id)
            ->where('status', 'approved')
            ->get();
        foreach ($approvedOrders as $order) {
            $due = Carbon::parse($order->created_at)->addDays(30);
            $events[] = [
                'date' => $due->toDateString(),
                'title' => 'Order #' . $order->po_number . ' Payment Due',
                'type' => 'payment_due',
                'id' => $order->id,
                'details' => [
                    'po_number' => $order->po_number,
                    'total_amount' => $order->total_amount,
                ],
            ];
        }

        return Inertia::render('CLIENT/dashboard', [
            'stats' => [
                'total_orders' => $totalOrders,
                'total_quotations_sent' => $totalQuotationsSent,
                'pending_settlements' => $pendingSettlements,
            ],
            'calendarEvents' => $events,
        ]);
    }
}