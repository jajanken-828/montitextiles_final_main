<?php

namespace App\Http\Controllers\inv;

use App\Http\Controllers\Controller;
use App\Models\inv\Material;
use App\Models\inv\Product as InvProduct;
use App\Models\inv\Warehouse;
use App\Models\inv\WarehouseMaterial;
use Inertia\Inertia;

class InvDashboardController extends Controller
{
    public function managerDashboard()
    {
        $materials = Material::with('warehouseMaterials')->get();
        $totalSkus = $materials->count();
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;
        $totalValue = 0;

        foreach ($materials as $mat) {
            $qty = (float) $mat->warehouseMaterials->sum('quantity');
            $totalValue += $qty * (float) $mat->unit_cost;

            if ($qty <= 0) {
                $outOfStock++;
            } elseif ($qty <= $mat->reorder_point) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        $warehouses = Warehouse::withCount([
            'warehouseMaterials as sku_count' => fn ($q) => $q->where('quantity', '>', 0),
        ])
            ->withSum('warehouseMaterials as total_units', 'quantity')
            ->orderBy('id')
            ->get()
            ->map(fn ($wh) => [
                'id' => $wh->id,
                'name' => $wh->name,
                'location' => $wh->location,
                'manager' => $wh->manager,
                'color' => $wh->color ?? 'blue',
                'skus' => (int) $wh->sku_count,
                'total_units' => (float) ($wh->total_units ?? 0),
            ])->values()->toArray();

        $totalWarehouses = count($warehouses);

        $alertItems = WarehouseMaterial::with(['material', 'warehouse'])
            ->whereHas('material')
            ->get()
            ->filter(fn ($wm) => (float) $wm->quantity <= (float) $wm->material->reorder_point)
            ->sortByDesc(fn ($wm) => $wm->quantity <= 0 ? 1 : 0)
            ->map(fn ($wm) => [
                'sku' => $wm->material->mat_id,
                'name' => $wm->material->name,
                'warehouse' => $wm->warehouse->name,
                'qty' => (float) $wm->quantity,
                'reorder' => (float) $wm->material->reorder_point,
                'type' => $wm->quantity <= 0 ? 'out' : 'low',
            ])->values()->toArray();

        $palette = [
            'Raw Material' => 'bg-blue-500',
            'Chemical' => 'bg-violet-500',
            'Accessory' => 'bg-emerald-500',
            'Packaging' => 'bg-amber-500',
            'Supplies' => 'bg-cyan-500',
        ];

        $safeTotal = $totalSkus ?: 1;

        $categoryBreakdown = $materials
            ->groupBy('category')
            ->map(fn ($group, $cat) => [
                'name' => $cat,
                'count' => $group->count(),
                'pct' => (int) round(($group->count() / $safeTotal) * 100),
                'color' => $palette[$cat] ?? 'bg-slate-400',
            ])->values()->toArray();

        $recentActivity = WarehouseMaterial::with(['material', 'warehouse'])
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($wm) {
                $qty = (float) $wm->quantity;
                $reorder = (float) ($wm->material->reorder_point ?? 0);

                if ($qty <= 0) {
                    $action = 'Out of stock flagged';
                    $color = 'red';
                } elseif ($qty <= $reorder) {
                    $action = 'Low stock alert';
                    $color = 'amber';
                } else {
                    $action = 'Stock updated';
                    $color = 'emerald';
                }

                return [
                    'time' => $wm->updated_at->diffForHumans(),
                    'action' => $action,
                    'item' => $wm->material->name,
                    'qty' => number_format($qty, 0).' '.$wm->material->unit,
                    'color' => $color,
                    'warehouse' => $wm->warehouse->name,
                ];
            })->values()->toArray();

        $totalProducts = InvProduct::count();

        return Inertia::render('Dashboard/INV/Manager/index', [
            'warehouses' => $warehouses,
            'alertItems' => $alertItems,
            'recentActivity' => $recentActivity,
            'categoryBreakdown' => $categoryBreakdown,
            'kpis' => [
                'totalSkus' => $totalSkus,
                'inStock' => $inStock,
                'lowStock' => $lowStock,
                'outOfStock' => $outOfStock,
                'totalWarehouses' => $totalWarehouses,
                'totalValue' => round($totalValue, 2),
                'totalProducts' => $totalProducts,
            ],
        ]);
    }

    public function staffDashboard()
    {
        $materials = Material::with('warehouseMaterials')->get();
        $totalSkus = $materials->count();
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;
        $totalValue = 0;

        foreach ($materials as $mat) {
            $qty = (float) $mat->warehouseMaterials->sum('quantity');
            $totalValue += $qty * (float) $mat->unit_cost;

            if ($qty <= 0) {
                $outOfStock++;
            } elseif ($qty <= $mat->reorder_point) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        return Inertia::render('Dashboard/INV/Employee/index', [
            'user' => auth()->user(),
            'kpis' => [
                'totalSkus' => $totalSkus,
                'inStock' => $inStock,
                'lowStock' => $lowStock,
                'outOfStock' => $outOfStock,
                'totalValue' => round($totalValue, 2),
            ],
        ]);
    }
}
