<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\inv\Product;
use App\Models\eco\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientProductsController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'Active')
            ->with('images')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'selling_price' => $p->selling_price,
                'stock_on_hand' => $p->stock_on_hand,
                'images' => $p->images->map(fn($img) => asset('storage/'.$img->path)),
            ]);
        return Inertia::render('Client/Products', ['products' => $products]);
    }

    public function inquire(Request $request, Product $product)
    {
        $request->validate(['message' => 'required|string']);
        $client = Auth::guard('client')->user();
        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'initial_message' => $request->message,
            'status' => 'open',
            'last_message_at' => now(),
        ]);
        // Also create the first message
        \App\Models\eco\ConversationMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'client',
            'message' => $request->message,
        ]);
        return redirect()->route('client.conversation.show', $inquiry->id)
            ->with('success', 'Inquiry sent. You can now continue the conversation.');
    }
}