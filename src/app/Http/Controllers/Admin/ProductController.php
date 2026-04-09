<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCharacteristic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active');
        $isDeleted = $tab === 'deleted';

        $products = Product::with('category')
            ->where('is_deleted', $isDeleted)
            ->latest()
            ->get();

        return Inertia::render('Admin/Products', [
            'products' => $products,
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        $categories = Category::with('characteristics')->get();

        return Inertia::render('Admin/ProductForm', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'discount'    => 'integer|min:0|max:100',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'quantity'    => $request->quantity,
            'discount'    => $request->discount ?? 0,
            'category_id' => $request->category_id,
            'image'       => $imagePath,
        ]);

        if ($request->filled('characteristics')) {
            foreach ($request->characteristics as $charId => $value) {
                if (!empty(trim($value))) {
                    ProductCharacteristic::create([
                        'product_id'        => $product->id,
                        'characteristic_id' => $charId,
                        'value'             => trim($value),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products');
    }

    public function edit(Product $product)
    {
        $product->load('characteristics');
        $categories = Category::with('characteristics')->get();

        return Inertia::render('Admin/ProductForm', [
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title'       => 'required|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'discount'    => 'integer|min:0|max:100',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price,
            'quantity'    => $request->quantity,
            'discount'    => $request->discount ?? 0,
            'category_id' => $request->category_id,
            'image'       => $imagePath,
        ]);

        ProductCharacteristic::where('product_id', $product->id)->delete();

        if ($request->filled('characteristics')) {
            foreach ($request->characteristics as $charId => $value) {
                if (!empty(trim($value))) {
                    ProductCharacteristic::create([
                        'product_id'        => $product->id,
                        'characteristic_id' => $charId,
                        'value'             => trim($value),
                    ]);
                }
            }
        }

        return redirect()->route('admin.products');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_deleted' => true]);
        return back();
    }

    public function restore(Product $product)
    {
        $product->update(['is_deleted' => false]);
        return back();
    }
}