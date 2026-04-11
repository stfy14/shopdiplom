<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Events\OrderUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $cat = $request->get('cat');

        $query = \App\Models\Product::with('category')->where('is_deleted', false);

        if ($cat) {
            $query->whereHas('category', function($query) use ($cat) {
                $query->where('code', $cat);
            });
        }

        if ($q) {
            // Приводим запрос к нижнему регистру и меняем ё на е
            $qClean = mb_strtolower(str_replace('ё', 'е', $q));
            
            $query->whereRaw("REPLACE(LOWER(title), 'ё', 'е') LIKE ?", ['%' . $qClean . '%'])
                ->orWhereRaw("REPLACE(LOWER(description), 'ё', 'е') LIKE ?", ['%' . $qClean . '%']);
        }

        $products = $query->latest()->get();

        return Inertia::render('Shop/Index',[
            'products' => $products,
            'categories' => \App\Models\Category::all(),
            'filters' => $request->only(['q', 'cat']),
            // СТРОКУ С cartItems ОТСЮДА УДАЛИЛИ
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