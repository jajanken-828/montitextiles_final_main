<?php

namespace App\Http\Controllers\eco;

use App\Http\Controllers\Controller;
use App\Models\eco\Inquiry;
use App\Models\eco\ConversationMessage;
use App\Models\ClientQuotation;
use App\Models\ClientQuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EcoInquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::with(['client', 'product'])->latest()->get();
        return Inertia::render('Dashboard/ECO/Inquiry', ['inquiries' => $inquiries]);
    }

    public function show(Inquiry $inquiry)
    {
        $inquiry->load(['client', 'product', 'messages']);
        // Also load quotations that belong to this inquiry (optional: link via custom field)
        $quotations = ClientQuotation::where('client_id', $inquiry->client_id)
            ->where('custom_notes', 'LIKE', "%inquiry_id:{$inquiry->id}%")
            ->get();
        return Inertia::render('ECO/InquiryShow', [
            'inquiry' => $inquiry,
            'quotations' => $quotations,
        ]);
    }

    public function sendMessage(Request $request, Inquiry $inquiry)
    {
        $request->validate(['message' => 'required|string']);
        ConversationMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'eco',
            'message' => $request->message,
        ]);
        $inquiry->update(['last_message_at' => now()]);
        return back()->with('success', 'Message sent.');
    }

    public function setMeeting(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date',
            'location' => 'required|string',
            'type' => 'required|string',
        ]);
        ConversationMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'eco',
            'message' => "Meeting scheduled: {$data['type']} at {$data['location']} on {$data['scheduled_at']}",
            'meeting_data' => $data,
            'is_system_event' => true,
        ]);
        $inquiry->update(['last_message_at' => now()]);
        return back()->with('success', 'Meeting set and notified.');
    }

    public function issueQuotation(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'delivery_date' => 'required|date',
            'payment_mode' => 'required|string',
            'payment_terms' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $client = $inquiry->client;
        $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $grandTotal = $subtotal; // tax/shipping can be added later

        DB::beginTransaction();
        try {
            $quotation = ClientQuotation::create([
                'client_id' => $client->id,
                'quotation_number' => 'QT-'.date('Y').'-'.str_pad(ClientQuotation::count() + 1, 4, '0', STR_PAD_LEFT),
                'issue_date' => now(),
                'valid_until' => now()->addDays(30),
                'status' => 'sent',
                'billing_address' => $client->company_address,
                'shipping_address' => $client->company_address,
                'payment_terms' => $validated['payment_terms'],
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'currency' => 'PHP',
                'custom_notes' => "Inquiry #{$inquiry->id}\nDelivery: {$validated['delivery_date']}\nPayment Mode: {$validated['payment_mode']}\n".($validated['notes'] ?? ''),
            ]);

            foreach ($validated['items'] as $item) {
                $product = \App\Models\inv\Product::find($item['product_id']);
                ClientQuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_of_measure' => 'pcs',
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Add system message
            ConversationMessage::create([
                'inquiry_id' => $inquiry->id,
                'sender_type' => 'eco',
                'message' => "Quotation {$quotation->quotation_number} has been issued. Total: ₱".number_format($grandTotal, 2),
                'is_system_event' => true,
            ]);
            $inquiry->update(['status' => 'quotation_sent', 'last_message_at' => now()]);
            DB::commit();
            return back()->with('success', 'Quotation sent to client.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to issue quotation: '.$e->getMessage()]);
        }
    }

    public function creditCheck(Client $client)
    {
        // Return data from CreditController (see below)
        $credit = \App\Models\CreditAccount::where('client_id', $client->id)->first();
        $outstanding = $credit ? $credit->outstanding_balance : 0;
        $isGood = $credit ? $credit->is_good_payer : true;
        return response()->json(['outstanding' => $outstanding, 'is_good_payer' => $isGood]);
    }
}