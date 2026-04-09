<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CharacteristicController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with('characteristics')->get();
        $currentCategoryId = $request->get('category_id', $categories->first()?->id);

        return Inertia::render('Admin/Characteristics', [
            'categories'        => $categories,
            'currentCategoryId' => (int) $currentCategoryId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        Characteristic::create($request->only('name', 'category_id'));

        return back();
    }

    public function destroy(Characteristic $characteristic)
    {
        $characteristic->delete();
        return back();
    }
}