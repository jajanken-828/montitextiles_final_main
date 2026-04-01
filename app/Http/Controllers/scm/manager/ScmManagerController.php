<?php

namespace App\Http\Controllers\scm\manager;

use App\Http\Controllers\Controller;
use App\Http\Controllers\inv\manager\ProductionPlanningController;
use App\Models\PurchaseOrder;
use App\Models\Scm\MaterialRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ScmManagerController extends Controller
{
    /**
     * Forward a material request to the Procurement module (PRO).
     */
    public function forwardMaterialRequest(Request $request, $id)
    {
        try {
            $materialRequest = MaterialRequest::findOrFail($id);

            if ($materialRequest->status !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Material request already processed.']);
            }

            $materialRequest->update([
                'status' => 'forwarded',
                'forwarded_at' => now(),
                'forwarded_by' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Material request forwarded to Procurement.');
        } catch (\Exception $e) {
            Log::error('Forward material request failed: '.$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to forward request.']);
        }
    }

    /**
     * Approve an order for manufacturing after INV has confirmed sufficient materials.
     * Moves the order from inv_checked (with sufficient flag true) to man_production.
     */
    public function approveManufacturing(PurchaseOrder $order)
    {
        $queue = $order->queue;

        if (! $queue) {
            return redirect()->back()->withErrors(['error' => 'Order queue not found.']);
        }

        if ($queue->stage !== 'inv_checked') {
            return redirect()->back()->withErrors(['error' => 'Order is not in the correct stage for manufacturing approval.']);
        }

        if (! $queue->inv_check_sufficient) {
            return redirect()->back()->withErrors(['error' => 'Order materials are insufficient. Please handle material requests first.']);
        }

        $queue->update(['stage' => 'man_production', 'man_started_at' => now()]);

        return redirect()->back()->with('success', "Order {$order->po_number} sent to Manufacturing.");
    }

    /**
     * Display the assignment page where SCM managers can reassign staff.
     */
    public function assignment()
    {
        $staff = User::where('role', 'SCM')
            ->where('position', 'staff')
            ->select('id', 'name', 'email', 'role')
            ->get();

        return Inertia::render('Dashboard/SCM/Manager/Assignment', [
            'staff' => $staff,
        ]);
    }

    /**
     * Update the role of a staff member.
     */
    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:INV,MAN,PRO,SCM',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $validated['role']]);

        return redirect()->back()->with('message', "{$user->name} has been assigned to {$validated['role']}.");
    }

    public function recheckOrder(PurchaseOrder $order)
    {
        $queue = $order->queue;
        if (! $queue || $queue->stage !== 'inv_checked') {
            return redirect()->back()->withErrors(['error' => 'Order is not in the inv_checked stage.']);
        }

        $sufficient = ProductionPlanningController::reevaluateOrder($order);

        if ($sufficient) {
            return redirect()->back()->with('success', "Order {$order->po_number} now has sufficient materials.");
        } else {
            return redirect()->back()->with('info', "Order {$order->po_number} still lacks materials.");
        }
    }
}
