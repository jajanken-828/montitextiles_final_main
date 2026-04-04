<?php

namespace App\Http\Controllers\warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'CEO' || $user->position === 'secretary' || $user->position === 'general_manager') {
            $warehouses = Warehouse::with('supervisor')->get();
        } else {
            $warehouses = Warehouse::where('supervisor_id', $user->id)->orWhere('manager_id', $user->id)->get();
        }
        return Inertia::render('Dashboard/Warehouse/Index', ['warehouses' => $warehouses]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);
        $data['color'] = $data['color'] ?? 'blue';
        Warehouse::create($data);
        return redirect()->back()->with('success', 'Warehouse created.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => 'string|max:255',
            'location' => 'string|max:255',
            'supervisor_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);
        $warehouse->update($data);
        return redirect()->back()->with('success', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->back()->with('success', 'Warehouse deleted.');
    }
}