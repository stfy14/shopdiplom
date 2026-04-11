<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Events\NewOrderMessage;
use App\Events\NewOrderPlaced;
use App\Events\OrderUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) return redirect()->route('home');

        return Inertia::render('Shop/Checkout', [
            'items' => $items,
            'total' => $items->sum(fn($i) => $i->product->price * $i->quantity),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) return redirect()->route('home');

        $order = null;
        DB::transaction(function () use ($items, $data, &$order) {
            $total = $items->sum(fn($i) => $i->product->price_with_discount * $i->quantity);

            $order = Order::create(array_merge($data, [
                'user_id'     => auth()->id(),
                'total_price' => $total,
                'status'      => 'new',
            ]));

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item->product_id,
                    'quantity'          => $item->quantity,
                    'price_at_purchase' => $item->product->price_with_discount,
                ]);
            }
            Cart::where('user_id', auth()->id())->delete();
        });

        broadcast(new NewOrderPlaced($order));
        return redirect()->route('order.show', $order->uuid);
    }

    public function show(string $uuid)
    {
        $order = Order::with(['items.product', 'messages'])
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Shop/Order', ['order' => $order]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->status !== 'new') abort(403);

        $order->update([
            'status'               => 'cancelled_by_user',
            'has_unseen_activity'  => true,
        ]);
        broadcast(new OrderUpdated($order, 'cancelled'));
        return response()->json(['success' => true]);
    }

    public function getMessages(Order $order)
    {
        if ($order->user_id !== auth()->id() && auth()->user()->role !== 'admin') abort(403);
        return response()->json($order->messages()->oldest()->get());
    }

    public function sendMessage(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        $request->validate(['message' => 'required|string']);

        $message = OrderMessage::create([
            'order_id'    => $order->id,
            'sender_role' => 'user',
            'message'     => $request->message,
        ]);

        $order->update(['has_unseen_activity' => true]);

        // NewOrderMessage вещает только на order.{id} (т.к. отправитель - user)
        // Админ получит уведомление через OrderUpdated ниже
        broadcast(new NewOrderMessage($message));
        broadcast(new OrderUpdated($order, 'new_message'));

        return response()->json($message);
    }

    public function updateContacts(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id() || !in_array($order->status, ['new', 'processing'])) abort(403);

        $validated = $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update($validated);
        $order->update(['has_unseen_activity' => true]);
        // Тип contacts_updated — админ получит специфичный тост
        broadcast(new OrderUpdated($order, 'contacts_updated'));

        return response()->json(['success' => true]);
    }
}