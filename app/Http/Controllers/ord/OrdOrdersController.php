<?php

namespace App\Http\Controllers\ord;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrdOrdersController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $orders = PurchaseOrder::with('client')
            ->whereNotNull('delivery_date')
            ->whereYear('delivery_date', $year)
            ->whereMonth('delivery_date', $month)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'po_number' => $order->po_number,
                    'client_name' => $order->client->company_name,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'delivery_date' => $order->delivery_date->format('Y-m-d'),
                ];
            });

        return Inertia::render('Dashboard/ORD/Orders', [
            'orders' => $orders,
            'currentYear' => (int) $year,
            'currentMonth' => (int) $month,
        ]);
    }
}