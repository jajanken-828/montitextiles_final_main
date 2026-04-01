<?php

namespace App\Http\Controllers\scm;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Scm\MaterialRequest;
use App\Models\Scm\PurchaseInvoice;
use App\Models\Supplier; // Add this import
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ScmDashboardController extends Controller
{
    // New Dashboard with Charts
    public function managerDashboard()
    {
        // Basic stats
        $pendingMaterialRequests = MaterialRequest::where('status', 'pending')->count();
        $pendingInvoices = PurchaseInvoice::where('status', 'unpaid')->count();
        $readyOrders = PurchaseOrder::whereHas('queue', function ($q) {
            $q->where('stage', 'inv_checked')->where('inv_check_sufficient', true);
        })->count();

        // Material requests trend (last 6 months)
        $months = [];
        $materialRequestsTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M');
            $materialRequestsTrend[] = MaterialRequest::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Supplier performance (top 5 by purchase orders) - using join to avoid missing relationship
        $supplierPerformance = PurchaseInvoice::leftJoin('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->select(
                'purchase_invoices.supplier_id',
                DB::raw('count(*) as po_count'),
                DB::raw('sum(purchase_invoices.amount) as total_amount'),
                'suppliers.business_name as supplier_name'
            )
            ->groupBy('purchase_invoices.supplier_id', 'suppliers.business_name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'supplier_name' => $item->supplier_name ?? 'Unknown',
                'po_count' => $item->po_count,
                'total_amount' => (float) $item->total_amount,
            ]);

        // Urgency distribution for material requests
        $urgencyCounts = [
            'High' => MaterialRequest::where('status', 'pending')->where('urgency', 'High')->count(),
            'Medium' => MaterialRequest::where('status', 'pending')->where('urgency', 'Medium')->count(),
            'Low' => MaterialRequest::where('status', 'pending')->where('urgency', 'Low')->count(),
        ];

        return Inertia::render('Dashboard/SCM/Manager/Index', [
            'stats' => [
                'pendingMaterialRequests' => $pendingMaterialRequests,
                'pendingInvoices' => $pendingInvoices,
                'readyOrders' => $readyOrders,
            ],
            'materialRequestsTrend' => [
                'months' => $months,
                'values' => $materialRequestsTrend,
            ],
            'supplierPerformance' => $supplierPerformance,
            'urgencyCounts' => $urgencyCounts,
        ]);
    }

    // Operations page (old dashboard content)
    public function operations()
    {
        $materialRequests = MaterialRequest::where('status', 'pending')
            ->with('material')
            ->orderByRaw("FIELD(urgency, 'High', 'Medium', 'Low')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'req_number' => $req->req_number,
                'material_name' => $req->material?->name ?? $req->material_name,
                'required_qty' => $req->required_qty,
                'unit' => $req->material?->unit ?? $req->unit,
                'urgency' => $req->urgency,
            ]);

        $invoices = PurchaseInvoice::where('status', 'unpaid')
            ->orderBy('due_date')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'po_number' => $inv->po_number,
                'supplier_name' => $inv->supplier_name,
                'amount' => $inv->amount,
                'due_date' => $inv->due_date,
            ]);

        $readyOrders = PurchaseOrder::with(['client', 'items'])
            ->whereHas('queue', function ($q) {
                $q->where('stage', 'inv_checked')
                    ->where('inv_check_sufficient', true);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'po_number' => $order->po_number,
                'client_name' => $order->client->company_name,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
            ]);

        $insufficientOrders = PurchaseOrder::with(['client', 'items'])
            ->whereHas('queue', function ($q) {
                $q->where('stage', 'inv_checked')
                    ->where('inv_check_sufficient', false);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'po_number' => $order->po_number,
                'client_name' => $order->client->company_name,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
            ]);

        $stats = [
            'pendingMaterialRequests' => $materialRequests->count(),
            'pendingPayments' => $invoices->count(),
            'readyOrdersCount' => $readyOrders->count(),
        ];

        return Inertia::render('Dashboard/SCM/Manager/Operations', [
            'stats' => $stats,
            'materialRequests' => $materialRequests,
            'invoices' => $invoices,
            'readyOrders' => $readyOrders,
            'insufficientOrders' => $insufficientOrders,
        ]);
    }

    // Staff dashboard remains unchanged
    public function staffDashboard()
    {
        return Inertia::render('Dashboard/SCM/Employee/Index', [
            'user' => auth()->user(),
            'stats' => [],
        ]);
    }
}
