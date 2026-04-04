<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\eco\Inquiry;
use App\Models\eco\ConversationMessage;
use App\Models\ClientQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            ->where('custom_notes', 'LIKE', '%inquiry_id:' . $inquiry->id . '%')
            ->with('items')
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
        $controller = new \App\Http\Controllers\client\QuotationController();
        return $controller->accept($quotation);
    }

    public function rejectQuotation(Request $request, ClientQuotation $quotation)
    {
        try {
            // Authorization check
            if ($quotation->client_id !== Auth::guard('client')->id()) {
                return back()->withErrors(['error' => 'Unauthorized access to this quotation.']);
            }
            
            // Status check
            if ($quotation->status !== 'sent') {
                return back()->withErrors(['error' => 'This quotation has already been processed.']);
            }

            // Validation
            $request->validate([
                'reason' => 'required|string',
                'request_new' => 'boolean'
            ]);

            // Update quotation
            $quotation->update([
                'status' => 'rejected',
                'client_rejected_at' => now()
            ]);

            // Extract inquiry ID from custom_notes
            $inquiryId = null;
            if (preg_match('/inquiry_id:(\d+)/', $quotation->custom_notes ?? '', $matches)) {
                $inquiryId = $matches[1];
            }

            $inquiry = null;
            if ($inquiryId) {
                $inquiry = Inquiry::where('id', $inquiryId)
                    ->where('client_id', $quotation->client_id)
                    ->first();
            }

            // Fallback to latest inquiry if not found
            if (!$inquiry) {
                $inquiry = Inquiry::where('client_id', $quotation->client_id)->latest()->first();
            }

            if ($inquiry) {
                $message = "Quotation {$quotation->quotation_number} rejected. Reason: {$request->reason}.";
                if ($request->request_new) {
                    $message .= " The client requests a new quotation.";
                }
                ConversationMessage::create([
                    'inquiry_id' => $inquiry->id,
                    'sender_type' => 'client',
                    'message' => $message,
                    'is_system_event' => true,
                ]);
            } else {
                Log::warning('No inquiry found for quotation rejection', ['quotation_id' => $quotation->id]);
            }

            $successMessage = $request->request_new ? 'Request for new quotation sent.' : 'Quotation rejected.';
            return back()->with('success', $successMessage);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Quotation rejection failed: ' . $e->getMessage(), [
                'quotation_id' => $quotation->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to reject quotation: ' . $e->getMessage()]);
        }
    }

    private function authorizeClient(Inquiry $inquiry)
    {
        if ($inquiry->client_id !== Auth::guard('client')->id()) {
            abort(403);
        }
    }
}