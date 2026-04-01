<?php

namespace App\Http\Controllers\ceo;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\PurchaseOrder;
use App\Models\User;
use Inertia\Inertia;

class CeoDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalOrders' => PurchaseOrder::count(),
            'totalRevenue' => PurchaseOrder::where('status', 'approved')->sum('total_amount'),
            'activeEmployees' => User::where('is_active', true)->count(),
            'pendingLeads' => CrmLead::where('status', 'Inquiry')->count(),
        ];

        return Inertia::render('Dashboard/CEO/Index', [
            'stats' => $stats,
        ]);
    }
}
