<?php

namespace App\Http\Controllers\eco;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CreditAccount;
use App\Models\PurchaseOrder;
use Inertia\Inertia;

class EcoCreditController extends Controller
{
    public function index()
    {
        $clients = Client::with('creditAccount')->get();
        $history = PurchaseOrder::with('client')->where('status', 'approved')->latest()->take(50)->get();
        return Inertia::render('Dashboard/ECO/Credit', [
            'clients' => $clients,
            'history' => $history,
        ]);
    }
}