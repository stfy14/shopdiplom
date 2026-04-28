<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    // /catalog — главная страница каталога с родительскими категориями
    public function index()
    {
        $categories = Category::with(['children' => function ($q) {
                $q->withCount(['products' => fn($p) => $p->where('is_deleted', false)])
                  ->orderBy('sort_order');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Catalog/Index', [
            'categories' => $categories,
        ]);
    }

    // /catalog/{slug} — страница категории:
    //   - если родительская: показываем подкатегории
    //   - если дочерняя: показываем товары с фильтрами
    public function show(Request $request, string $slug)
    {
        $category = Category::with(['parent', 'children.characteristics', 'characteristics'])
            ->where('code', $slug)
            ->firstOrFail();

        // Родительская категория — показываем детей
        if ($category->isParent()) {
            $children = $category->children()
                ->withCount(['products' => fn($q) => $q->where('is_deleted', false)])
                ->orderBy('sort_order')
                ->get();

            return Inertia::render('Catalog/Parent', [
                'category' => $category,
                'children' => $children,
            ]);
        }

        // Дочерняя категория — показываем товары
        $q = Product::with('category')
            ->where('category_id', $category->id)
            ->where('is_deleted', false);

        // Поиск
        if ($search = $request->get('q')) {
            $clean = mb_strtolower(str_replace('ё', 'е', $search));
            $q->whereRaw("REPLACE(LOWER(title), 'ё', 'е') LIKE ?", ["%{$clean}%"]);
        }

        // Сортировка
        $sort = $request->get('sort', 'default');
        match ($sort) {
            'price_asc'  => $q->orderByRaw('price * (1 - discount / 100.0) ASC'),
            'price_desc' => $q->orderByRaw('price * (1 - discount / 100.0) DESC'),
            'new'        => $q->latest(),
            default      => $q->latest(),
        };

        $products = $q->get();

        // Breadcrumbs: Каталог > Родитель > Текущая
        $breadcrumbs = [
            ['title' => 'Каталог', 'href' => '/catalog'],
            ['title' => $category->parent->name, 'href' => '/catalog/' . $category->parent->code],
            ['title' => $category->name, 'href' => null],
        ];

        return Inertia::render('Catalog/Show', [
            'category'    => $category,
            'products'    => $products,
            'breadcrumbs' => $breadcrumbs,
            'filters'     => $request->only(['q', 'sort']),
        ]);
    }
}