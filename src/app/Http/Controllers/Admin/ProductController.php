<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Events\CartPriceChanged;
use App\Events\ProductUpdated;
use App\Models\ProductCharacteristic;
use App\Notifications\AppNotification;
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

        return Inertia::render('Admin/Products',[
            'products' => $products,
            'tab'      => $tab,
        ]);
    }

    public function create()
    {
        $categories = Category::with('characteristics')->get();
        return Inertia::render('Admin/ProductForm',['categories' => $categories]);
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

        broadcast(new ProductUpdated());

        return redirect()->route('admin.products');
    }

    public function edit(Product $product)
    {
        $product->load('characteristics');
        $categories = Category::with('characteristics')->get();

        return Inertia::render('Admin/ProductForm',[
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

        $oldPrice    = (float) $product->price;
        $oldDiscount = (int)   $product->discount;
        $newPrice    = (float) $request->price;
        $newDiscount = (int)   ($request->discount ?? 0);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $newPrice,
            'quantity'    => $request->quantity,
            'discount'    => $newDiscount,
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

        // Логика расчета изменений в цене
        $oldFinal = $oldDiscount > 0 ? round($oldPrice * (1 - $oldDiscount / 100)) : $oldPrice;
        $newFinal = $newDiscount > 0 ? round($newPrice * (1 - $newDiscount / 100)) : $newPrice;

        if ($oldFinal !== $newFinal) {
            $reason = '';
            $fmtOld = number_format($oldPrice, 0, '', ' ');
            $fmtNew = number_format($newPrice, 0, '', ' ');

            if ($oldPrice != $newPrice && $oldDiscount != $newDiscount) {
                $reason = "Изменилась базовая цена (с $fmtOld на $fmtNew) и скидка (с {$oldDiscount}% на {$newDiscount}%)";
            } elseif ($oldPrice != $newPrice) {
                $reason = "Изменилась базовая цена с $fmtOld на $fmtNew";
            } elseif ($oldDiscount != $newDiscount) {
                $reason = "Изменилась скидка с {$oldDiscount}% на {$newDiscount}%";
            }

            Cart::where('product_id', $product->id)->update([
                'old_price' => $oldFinal,
                'price_change_reason' => $reason
            ]);

            $cartItems = Cart::where('product_id', $product->id)->get();
            $priceDown = $newFinal < $oldFinal;

            foreach ($cartItems as $cartItem) {
                $user = \App\Models\User::find($cartItem->user_id);
                if ($user) {
                    $user->notify(new AppNotification(
                        "«{$product->title}»: $fmtOld → $fmtNew ₽",
                        $priceDown ? 'success' : 'error',
                        '/cart',
                        $priceDown ? 'price_down' : 'price_up'
                    ));
                }

                broadcast(new CartPriceChanged(
                    $cartItem->user_id,
                    $product,
                    $oldPrice,
                    $newPrice,
                    $oldDiscount,
                    $newDiscount,
                ));
            }
        }

        broadcast(new ProductUpdated());

        return redirect()->route('admin.products');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_deleted' => true]);
        broadcast(new ProductUpdated());
        return back();
    }

    public function restore(Product $product)
    {
        $product->update(['is_deleted' => false]);
        broadcast(new ProductUpdated());
        return back();
    }
}