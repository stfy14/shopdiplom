<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    // /catalog — главная страница каталога с корневыми категориями
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

    // /catalog/{slug} — страница категории любой глубины:
    //   - если есть дочерние → показываем их
    //   - если листовая → показываем товары с фильтрами
    public function show(Request $request, string $slug)
    {
        $category = Category::with(['parent.parent.parent', 'characteristics'])
            ->where('code', $slug)
            ->firstOrFail();

        $breadcrumbs = $this->buildBreadcrumbs($category);

        // Если есть дочерние категории — показываем их (любая глубина)
        if ($category->children()->exists()) {
            $children = $category->children()
                ->withCount(['products' => fn($q) => $q->where('is_deleted', false)])
                ->with(['children' => fn($q) => $q
                    ->withCount(['products' => fn($p) => $p->where('is_deleted', false)])
                    ->orderBy('sort_order')
                ])
                ->orderBy('sort_order')
                ->get();

            return Inertia::render('Catalog/Parent', [
                'category'    => $category,
                'children'    => $children,
                'breadcrumbs' => $breadcrumbs,
            ]);
        }

        // Листовая категория — показываем товары
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

        return Inertia::render('Catalog/Show', [
            'category'    => $category,
            'products'    => $products,
            'breadcrumbs' => $breadcrumbs,
            'filters'     => $request->only(['q', 'sort']),
        ]);
    }

    // Построить хлебные крошки от корня до текущей категории
    private function buildBreadcrumbs(Category $category, bool $currentAsLink = false): array
    {
        $crumbs = [['title' => 'Каталог', 'href' => '/catalog']];

        foreach ($category->getAncestors() as $ancestor) {
            $crumbs[] = ['title' => $ancestor->name, 'href' => '/catalog/' . $ancestor->code];
        }

        $crumbs[] = [
            'title' => $category->name,
            'href'  => $currentAsLink ? '/catalog/' . $category->code : null,
        ];

        return $crumbs;
    }
}