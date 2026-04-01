<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CrmInteraction;
use App\Models\CrmLead;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class CrmDashboardController extends Controller
{
    public function managerDashboard()
    {
        $user = auth()->user();

        $totalLeads = CrmLead::count();
        $wonLeads = CrmLead::where('status', 'Closed-Won')->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100) : 0;

        $baseProps = [
            'user' => $user,
            'userRole' => 'CRM',
            'userPosition' => 'manager',
            'businessPartners' => Client::all() ?? [],
            'leads' => CrmLead::with('assignedStaff')->latest()->get() ?? [],
            'pendingRegistrations' => Client::where('status', 'pending')->get(),
            'upcomingInterviews' => [], // todo
            'pendingApprovals' => [],
        ];

        $managerProps = array_merge($baseProps, [
            'stats' => [
                'totalPipelineValue' => (float) CrmLead::whereNotIn('status', ['Closed-Won', 'Lost'])->sum('estimated_value'),
                'activeInquiries' => CrmLead::where('status', 'Inquiry')->count(),
                'pendingApprovals' => PurchaseOrder::whereIn('status', ['credit_review', 'tier_assignment'])->count(),
                'conversionRate' => (int) $conversionRate,
            ],
            'dailyActivityCount' => CrmInteraction::whereDate('created_at', Carbon::today())->count(),
            'leaderboard' => User::where('role', 'CRM')
                ->where('position', 'staff')
                ->withCount(['leads as won_deals' => fn ($q) => $q->where('status', 'Closed-Won')])
                ->orderBy('won_deals', 'desc')
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'won_deals' => $u->won_deals,
                ]),
        ]);

        return Inertia::render('Dashboard/CRM/Index', $managerProps);
    }

    public function staffDashboard()
    {
        $user = auth()->user();

        $baseProps = [
            'user' => $user,
            'userRole' => 'CRM',
            'userPosition' => 'staff',
            'businessPartners' => [],
            'leads' => $user->leads()->latest()->get() ?? [],
            'pendingRegistrations' => [],
            'upcomingInterviews' => [],
            'pendingApprovals' => [],
        ];

        $staffProps = array_merge($baseProps, [
            'stats' => [
                'myLeads' => $user->leads()->count(),
                'myActivities' => CrmInteraction::where('user_id', $user->id)->count(),
            ],
        ]);

        return Inertia::render('Dashboard/CRM/Index', $staffProps);
    }
}
