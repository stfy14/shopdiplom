<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Events\NewOrderMessage;
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
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $items = Cart::with('product')
            ->where('user_id', auth()->id())
            ->get();

        $total = $items->sum(fn($i) => $i->product->price * $i->quantity);

        $order = Order::create([
            'user_id'     => auth()->id(),
            'city'        => $request->city,
            'street'      => $request->street,
            'house'       => $request->house,
            'comment'     => $request->comment,
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

        broadcast(new NewOrderPlaced($order));

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

        $order->update([
            'status' => 'cancelled_by_user',
            'has_unseen_activity' => true // Добавляем флаг
        ]);

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

        $order->update(['has_unseen_activity' => true]);

        broadcast(new NewOrderMessage($message));

        return back();
    }

    public function updateContacts(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id() || !in_array($order->status, ['new', 'processing'])) {
            abort(403);
        }

        $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update($request->only(['city', 'street', 'house', 'comment', 'phone']));

        // Взводим флаг, если юзер сам поменял данные
        if ($order->user_id === auth()->id()) {
            $order->update(['has_unseen_activity' => true]);
        }

        return back();
    }
}