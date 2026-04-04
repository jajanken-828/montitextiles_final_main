<?php

namespace App\Http\Controllers\eco;

use App\Http\Controllers\Controller;
use App\Models\inv\Product;
use Inertia\Inertia;

class EcoStoreController extends Controller
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

        return Inertia::render('Dashboard/ECO/Store', ['products' => $products]);
    }
}