<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Events\NewOrderPlaced;
use App\Events\NewOrderMessage;
use App\Events\OrderUpdated;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) return redirect()->route('cart.index');

        $total = $items->sum(fn($i) => ($i->product->price_with_discount ?? 0) * $i->quantity);
        return Inertia::render('Shop/Checkout',['items' => $items, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   =>['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) return back();

        $total = $items->sum(fn($i) => ($i->product->price_with_discount ?? 0) * $i->quantity);

        $order = Order::create([
            'user_id'     => auth()->id(),
            'phone'       => $request->phone,
            'city'        => $request->city,
            'street'      => $request->street,
            'house'       => $request->house,
            'comment'     => $request->comment,
            'total_price' => $total,
            'status'      => 'new',
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $item->product_id,
                'quantity'          => $item->quantity,
                'price_at_purchase' => $item->product->price_with_discount ?? 0,
            ]);
            $item->product->decrement('quantity', $item->quantity);
        }

        Cart::where('user_id', auth()->id())->delete();

        // СОЗДАЕМ УВЕДОМЛЕНИЕ ДЛЯ АДМИНОВ
        $fmt = number_format($order->total_price, 0, '', ' ');
        \App\Models\User::where('role', 'admin')->get()->each(function($admin) use($order, $fmt) {
            $admin->notify(new AppNotification("Новый заказ #{$order->id} на {$fmt} ₽", 'success', "/admin/orders/{$order->id}", 'order'));
        });

        broadcast(new NewOrderPlaced($order));
        return redirect()->route('order.show', $order->uuid);
    }

    public function show($uuid)
    {
        $order = Order::with(['items.product', 'messages'])->where('uuid', $uuid)->firstOrFail();
        if ($order->user_id !== auth()->id()) abort(403);
        
        return Inertia::render('Shop/Order', ['order' => $order]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id() || !in_array($order->status, ['new', 'processing'])) abort(403);
        
        $order->update(['status' => 'cancelled_by_user']);
        foreach ($order->items as $item) {
            $item->product->increment('quantity', $item->quantity);
        }

        // УВЕДОМЛЕНИЕ АДМИНУ ОБ ОТМЕНЕ
        \App\Models\User::where('role', 'admin')->get()->each(function($admin) use($order) {
            $admin->notify(new AppNotification("Заказ #{$order->id} отменён клиентом", 'error', "/admin/orders/{$order->id}", 'cancel'));
        });

        broadcast(new OrderUpdated($order, 'cancelled'));
        return back();
    }

    public function getMessages(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
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

        // УВЕДОМЛЕНИЕ АДМИНУ О НОВОМ СООБЩЕНИИ
        \App\Models\User::where('role', 'admin')->get()->each(function($admin) use($order) {
            $admin->notify(new AppNotification("Новое сообщение от клиента в заказе #{$order->id}", 'success', "/admin/orders/{$order->id}", 'message'));
        });

        broadcast(new NewOrderMessage($message));
        return response()->json($message);
    }

    public function updateContacts(Order $order, Request $request)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   =>['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update(array_merge(
            $request->only(['city', 'street', 'house', 'comment', 'phone']),
            ['has_unseen_activity' => true]
        ));

        // УВЕДОМЛЕНИЕ АДМИНУ
        \App\Models\User::where('role', 'admin')->get()->each(function($admin) use($order) {
            $admin->notify(new AppNotification("Клиент обновил контакты в заказе #{$order->id}", 'success', "/admin/orders/{$order->id}", 'edit'));
        });

        broadcast(new OrderUpdated($order, 'contacts_updated'));
        return response()->json(['success' => true]);
    }

    public function readMessages(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        $needsBroadcast = false;

        if ($order->user_has_unseen_status_change || $order->user_has_unseen_contact_update) {
            $order->update([
                'user_has_unseen_status_change' => false,
                'user_has_unseen_contact_update' => false
            ]);
            $needsBroadcast = true;
        }

        $unread = $order->messages()->where('sender_role', 'admin')->where('is_read', false);
        if ($unread->exists()) {
            $unread->update(['is_read' => true]);
            $needsBroadcast = true;
        }

        if ($needsBroadcast) broadcast(new OrderUpdated($order, 'read'));
        return response()->json(['success' => true]);
    }
}