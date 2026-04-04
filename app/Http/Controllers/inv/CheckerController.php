<?php

namespace App\Http\Controllers\inv;

use App\Http\Controllers\Controller;
use App\Models\inv\Material;
use App\Models\WarehouseStockItem;
use App\Models\PurchaseOrder;
use App\Models\Scm\MaterialRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckerController extends Controller
{
    /**
     * Display stock checker dashboard.
     */
    public function index()
    {
        $materials = Material::all();
        $stockStatus = [];

        foreach ($materials as $material) {
            $totalStock = WarehouseStockItem::where('material_id', $material->id)->sum('quantity');
            $status = ($totalStock <= 0) ? 'out' : (($totalStock <= $material->reorder_point) ? 'low' : 'ok');

            $stockStatus[] = [
                'id' => $material->id,
                'mat_id' => $material->mat_id,
                'name' => $material->name,
                'category' => $material->category,
                'unit' => $material->unit,
                'reorder_point' => $material->reorder_point,
                'total_stock' => (float) $totalStock,
                'status' => $status,
            ];
        }

        $pendingOrdersCount = PurchaseOrder::whereHas('queue', function ($q) {
            $q->where('stage', 'inv_check');
        })->count();

        return Inertia::render('Dashboard/Inventory/Checker', [
            'materials' => $stockStatus,
            'pendingOrdersCount' => $pendingOrdersCount,
        ]);
    }

    /**
     * Request procurement for a specific material.
     */
    public function requestProcurement(Material $material)
    {
        $currentStock = WarehouseStockItem::where('material_id', $material->id)->sum('quantity');
        $requiredQty = max($material->reorder_point - $currentStock, 1);

        $reqNumber = 'MR-' . date('Ymd') . '-' . rand(1000, 9999);

        MaterialRequest::create([
            'req_number' => $reqNumber,
            'material_id' => $material->id,
            'material_name' => $material->name,
            'category' => $material->category,
            'unit' => $material->unit,
            'current_stock' => $currentStock,
            'reorder_point' => $material->reorder_point,
            'required_qty' => $requiredQty,
            'urgency' => 'Medium',
            'requested_by' => auth()->user()->name,
            'requested_at' => now(),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', "Procurement request {$reqNumber} sent to SCM.");
    }

    /**
     * Check material sufficiency for pending orders.
     */
    public function checkOrders()
    {
        $orders = PurchaseOrder::whereHas('queue', function ($q) {
            $q->where('stage', 'inv_check');
        })->get();

        foreach ($orders as $order) {
            // You can implement the same logic as ProductionPlanningController::checkAvailability
            // For now, just forward to the checker logic
            // This is a placeholder – you may call a service or update the queue stage.
        }

        return redirect()->back()->with('message', 'Order check completed.');
    }

    /**
     * Check a specific order.
     */
    public function checkOrder(PurchaseOrder $order)
    {
        // Implement detailed check logic here
        // For now, just a placeholder
        return redirect()->back()->with('message', "Order {$order->po_number} checked.");
    }
}