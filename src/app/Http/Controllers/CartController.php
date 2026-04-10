<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::with('product.category')
            ->where('user_id', auth()->id())
            ->orderBy('id', 'asc') // ВАЖНО! Это не даст товарам "прыгать"
            ->get();

        return Inertia::render('Shop/Cart',[
            'items' => $items,
        ]);
    }

    public function add(Product $product)
    {
        Cart::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $product->id],
            ['quantity' => 1]
        );

        return back()->with('cart_added', $product->title);
    }

    public function update(Product $product, Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->update(['quantity' => $request->quantity]);

        return back();
    }

    public function remove(Product $product)
    {
        Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        return back();
    }
}