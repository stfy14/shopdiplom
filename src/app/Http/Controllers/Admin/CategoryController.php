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
        // Загружаем всё дерево: родители → дети → характеристики
        $categories = Category::with(['children.characteristics'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Categories', [
            'categories' => $categories,
        ]);
    }

    // Создать родительскую категорию
    public function storeParent(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:categories,code',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'parent_id'   => null,
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'image'       => $imagePath,
        ]);

        return back();
    }

    // Создать дочернюю категорию
    public function storeChild(Request $request)
    {
        $request->validate([
            'parent_id'   => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:categories,code',
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'parent_id'   => $request->parent_id,
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'sort_order'  => $request->sort_order ?? 0,
            'image'       => $imagePath,
        ]);

        return back();
    }

    // Обновить категорию (любого уровня)
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:categories,code,' . $category->id,
            'description' => 'nullable|string',
            'sort_order'  => 'integer|min:0',
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