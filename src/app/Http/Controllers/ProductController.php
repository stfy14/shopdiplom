<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where('is_deleted', false);

        if ($request->filled('q')) {
            $search = str_replace(['ё', 'Ё'], ['е', 'Е'], $request->q);
            $query->where(function($q) use ($search) {
                $q->whereRaw("REPLACE(LOWER(title), 'ё', 'е') LIKE ?", ['%' . mb_strtolower($search) . '%'])
                ->orWhereRaw("REPLACE(LOWER(description), 'ё', 'е') LIKE ?", ['%' . mb_strtolower($search) . '%']);
            });
        }

        if ($request->filled('cat')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('code', $request->cat);
            });
        }

        $products = $query->latest()->get();
        $categories = Category::all();

        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->get()->keyBy('product_id')
            : collect();

        return Inertia::render('Shop/Index', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['q', 'cat']),
            'cartItems'  => $cartItems,
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'characteristics.characteristic']);

        return Inertia::render('Shop/Show', [
            'product' => $product,
        ]);
    }
}