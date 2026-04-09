<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $items = Cart::with('product')
            ->where('user_id', auth()->id())
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('home');
        }

        return Inertia::render('Shop/Checkout', [
            'items' => $items,
            'total' => $items->sum(fn($i) => $i->product->price * $i->quantity),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'phone'   => ['required', 'string', 'regex:/^\+?[\d\s\(\)\-]{10,18}$/'],
        ]);

        $items = Cart::with('product')
            ->where('user_id', auth()->id())
            ->get();

        $total = $items->sum(fn($i) => $i->product->price * $i->quantity);

        $order = Order::create([
            'user_id'     => auth()->id(),
            'address'     => $request->address,
            'phone'       => $request->phone,
            'total_price' => $total,
            'status'      => 'new',
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $item->product_id,
                'quantity'          => $item->quantity,
                'price_at_purchase' => $item->product->price,
            ]);
        }

        Cart::where('user_id', auth()->id())->delete();

        return redirect()->route('order.show', $order->uuid);
    }

    public function show(string $uuid)
    {
        $order = Order::with(['items.product', 'messages'])
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Shop/Order', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->status !== 'new') {
            abort(403);
        }

        $order->update(['status' => 'cancelled_by_user']);

        return back();
    }

    public function getMessages(Order $order)
    {
        $messages = $order->messages()->latest()->get();
        return response()->json($messages);
    }

    public function sendMessage(Order $order, Request $request)
    {
        $request->validate(['message' => 'required|string']);

        OrderMessage::create([
            'order_id'    => $order->id,
            'sender_role' => 'user',
            'message'     => $request->message,
        ]);

        return back();
    }

    public function updateContacts(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        if (!in_array($order->status, ['new', 'processing'])) {
            abort(403, 'Нельзя изменить контакты на этом этапе');
        }

        $request->validate([
            'address' => 'required|string|min:10',
            'phone'   => ['required', 'string', 'regex:/^\+?[\d\s\(\)\-]{10,18}$/'],
        ]);

        $order->update($request->only('address', 'phone'));
        return back()->with('success', 'Контакты обновлены');
    }
}