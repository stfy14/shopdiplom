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
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Shop/Cart', [
            'items' => $items,
        ]);
    }

    public function add(Product $product)
    {
        if ($product->is_deleted || $product->quantity <= 0) {
            return back()->withErrors(['cart' => 'Товар недоступен или нет в наличии.']);
        }

        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        $currentInCart = $cartItem ? $cartItem->quantity : 0;

        if ($currentInCart >= $product->quantity) {
            return back()->withErrors(['cart' => 'Вы уже добавили максимально доступное количество товара.']);
        }

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id'    => auth()->id(),
                'product_id' => $product->id,
                'quantity'   => 1,
            ]);
        }

        return back()->with('cart_added', $product->title);
    }

    public function update(Product $product, Request $request)
    {
        $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $product->quantity,
            ],
        ], [
            'quantity.max' => 'На складе доступно только ' . $product->quantity . ' шт.',
        ]);

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

    public function clearNotification(Product $product)
    {
        Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->update([
                'old_price'           => null,
                'price_change_reason' => null,
            ]);

        return back();
    }
}