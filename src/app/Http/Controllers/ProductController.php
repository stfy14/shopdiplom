<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    // Глобальный поиск по всем товарам
    public function index(Request $request)
    {
        $q   = $request->get('q');
        $cat = $request->get('cat');

        $query = Product::with('category')->where('is_deleted', false);

        if ($cat) {
            $query->whereHas('category', fn($q) => $q->where('code', $cat));
        }

        if ($q) {
            $qClean = mb_strtolower(str_replace('ё', 'е', $q));
            $query->where(function ($sq) use ($qClean) {
                $sq->whereRaw("REPLACE(LOWER(title), 'ё', 'е') LIKE ?", ["%{$qClean}%"])
                   ->orWhereRaw("REPLACE(LOWER(description), 'ё', 'е') LIKE ?", ["%{$qClean}%"]);
            });
        }

        $products = $query->latest()->get();

        // Только дочерние категории для фильтра
        $categories = Category::whereNotNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Shop/Search', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['q', 'cat']),
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category.parent', 'characteristics.characteristic']);

        $breadcrumbs = [];
        if ($product->category) {
            $breadcrumbs = [
                ['title' => 'Каталог', 'href' => '/catalog'],
            ];
            if ($product->category->parent) {
                $breadcrumbs[] = [
                    'title' => $product->category->parent->name,
                    'href'  => '/catalog/' . $product->category->parent->code,
                ];
            }
            $breadcrumbs[] = [
                'title' => $product->category->name,
                'href'  => '/catalog/' . $product->category->code,
            ];
            $breadcrumbs[] = ['title' => $product->title, 'href' => null];
        }

        return Inertia::render('Shop/Show', [
            'product'     => $product,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}