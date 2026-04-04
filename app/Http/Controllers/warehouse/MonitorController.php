<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseSection;
use App\Models\WarehouseShelf;
use App\Models\WarehouseStockItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonitorController extends Controller
{
    public function show(Warehouse $warehouse)
    {
        $user = auth()->user();
        if ($user->role !== 'CEO' && $warehouse->supervisor_id !== $user->id && $warehouse->manager_id !== $user->id) {
            abort(403);
        }
        $sections = $warehouse->sections()->with('shelves.stockItems.material')->get();
        $unassignedStock = WarehouseStockItem::where('warehouse_id', $warehouse->id)->whereNull('shelf_id')->get();
        return Inertia::render('Dashboard/Warehouse/Monitor', [
            'warehouse' => $warehouse,
            'sections' => $sections,
            'unassignedStock' => $unassignedStock,
        ]);
    }

    public function updateLayout(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'grid_rows' => 'required|integer|min:1',
            'grid_cols' => 'required|integer|min:1',
            'sections' => 'array',
            'sections.*.name' => 'required|string',
            'sections.*.row' => 'required|integer',
            'sections.*.col' => 'required|integer',
            'sections.*.capacity' => 'nullable|integer',
            'sections.*.shelves' => 'array',
            'sections.*.shelves.*' => 'string',
        ]);
        // Delete old sections and shelves
        $warehouse->sections()->delete();
        foreach ($data['sections'] as $sec) {
            $section = WarehouseSection::create([
                'warehouse_id' => $warehouse->id,
                'name' => $sec['name'],
                'grid_row' => $sec['row'],
                'grid_col' => $sec['col'],
                'capacity' => $sec['capacity'] ?? null,
            ]);
            foreach ($sec['shelves'] as $shelfNum) {
                WarehouseShelf::create([
                    'section_id' => $section->id,
                    'shelf_number' => $shelfNum,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Layout updated.');
    }

    public function assignToShelf(Request $request)
    {
        $data = $request->validate([
            'stock_item_id' => 'required|exists:warehouse_stock_items,id',
            'shelf_id' => 'required|exists:warehouse_shelves,id',
        ]);
        $stock = WarehouseStockItem::findOrFail($data['stock_item_id']);
        $stock->update(['shelf_id' => $data['shelf_id']]);
        return redirect()->back()->with('success', 'Material assigned to shelf.');
    }

    public function useMaterial(Request $request, WarehouseStockItem $stockItem)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01|max:'.$stockItem->quantity,
            'manufacturing_department' => 'required|string',
        ]);
        // Create a consumption record (optional)
        // Update stock item quantity or mark as used
        if ($data['quantity'] >= $stockItem->quantity) {
            $stockItem->update(['status' => 'used', 'quantity' => 0]);
        } else {
            $stockItem->decrement('quantity', $data['quantity']);
        }
        // Notify manufacturing module (event or queue)
        return redirect()->back()->with('success', 'Material sent to production.');
    }
}