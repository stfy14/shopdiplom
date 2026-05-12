<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $rootCategories = Category::with(['allChildren'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        // Плоский список с глубиной для рендера дерева во фронтенде
        $flatCategories = $this->flattenTree($rootCategories);

        // Все категории для выбора родителя
        $allCategories = Category::orderBy('sort_order')->get(['id', 'name', 'parent_id', 'code']);

        return Inertia::render('Admin/Categories', [
            'categories'    => $flatCategories,
            'allCategories' => $allCategories,
        ]);
    }

    // Рекурсивно сворачивает дерево в плоский список с depth
    private function flattenTree($categories, int $depth = 0): array
    {
        $result = [];
        foreach ($categories as $cat) {
            // $cat может быть Eloquent-моделью (первый уровень) или массивом (рекурсия)
            $item = is_array($cat) ? $cat : $cat->toArray();
            $item['depth'] = $depth;
            $children = $item['all_children'] ?? [];
            unset($item['all_children'], $item['children']);
            $result[] = $item;
            if (!empty($children)) {
                $result = array_merge($result, $this->flattenTree($children, $depth + 1));
            }
        }
        return $result;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:categories,code',
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'gost'        => 'nullable|string|max:100',
            'din'         => 'nullable|string|max:100',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'parent_id'   => $request->parent_id ?: null,
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'gost'        => $request->gost,
            'din'         => $request->din,
            'image'       => $imagePath,
        ]);

        return back();
    }

    // @deprecated — используй store() с parent_id = null
    public function storeParent(Request $request)
    {
        $request->merge(['parent_id' => null]);
        return $this->store($request);
    }

    // @deprecated — используй store() с parent_id
    public function storeChild(Request $request)
    {
        return $this->store($request);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:categories,code,' . $category->id,
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'gost'        => 'nullable|string|max:100',
            'din'         => 'nullable|string|max:100',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'gost'        => $request->gost,
            'din'         => $request->din,
            'image'       => $imagePath,
        ]);

        return back();
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back();
    }
}