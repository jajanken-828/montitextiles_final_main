<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\eco\Inquiry;
use App\Models\eco\ConversationMessage;
use App\Models\ClientQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientConversationController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::where('client_id', Auth::guard('client')->id())
            ->with('product')
            ->latest()
            ->get();
        return Inertia::render('Client/ConversationList', ['inquiries' => $inquiries]);
    }

    public function show(Inquiry $inquiry)
    {
        $this->authorizeClient($inquiry);
        $inquiry->load(['messages', 'product']);
        $quotations = ClientQuotation::where('client_id', $inquiry->client_id)
            ->where('custom_notes', 'LIKE', "%inquiry_id:{$inquiry->id}%")
            ->get();
        return Inertia::render('Client/ConversationShow', [
            'inquiry' => $inquiry,
            'quotations' => $quotations,
        ]);
    }

    public function sendMessage(Request $request, Inquiry $inquiry)
    {
        $this->authorizeClient($inquiry);
        $request->validate(['message' => 'required|string']);
        ConversationMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'client',
            'message' => $request->message,
        ]);
        $inquiry->update(['last_message_at' => now()]);
        return back()->with('success', 'Message sent.');
    }

    public function acceptQuotation(ClientQuotation $quotation)
    {
        if ($quotation->client_id !== Auth::guard('client')->id() || $quotation->status !== 'sent') {
            abort(403);
        }
        // Convert to purchase order (as in existing QuotationController)
        // Re-use logic or call existing method
        $controller = new \App\Http\Controllers\client\QuotationController();
        return $controller->accept($quotation);
    }

    public function rejectQuotation(Request $request, ClientQuotation $quotation)
    {
        if ($quotation->client_id !== Auth::guard('client')->id() || $quotation->status !== 'sent') {
            abort(403);
        }
        $request->validate(['reason' => 'required|string', 'request_new' => 'boolean']);
        $quotation->update(['status' => 'rejected', 'client_rejected_at' => now()]);
        // Add a system message to the inquiry
        $inquiry = Inquiry::where('client_id', $quotation->client_id)
            ->where('custom_notes', 'LIKE', "%inquiry_id%")->first();
        if ($inquiry) {
            ConversationMessage::create([
                'inquiry_id' => $inquiry->id,
                'sender_type' => 'client',
                'message' => "Quotation {$quotation->quotation_number} rejected. Reason: {$request->reason}." . ($request->request_new ? ' Requesting a new quotation.' : ''),
                'is_system_event' => true,
            ]);
        }
        return back()->with('success', $request->request_new ? 'We will issue a new quotation.' : 'Quotation rejected.');
    }

    private function authorizeClient(Inquiry $inquiry)
    {
        if ($inquiry->client_id !== Auth::guard('client')->id()) {
            abort(403);
        }
    }
}