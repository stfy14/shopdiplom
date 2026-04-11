<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\NewOrderPlaced;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) {
            return redirect('/');
        }

        $total = $items->sum(function ($item) {
            return $item->product->price_with_discount * $item->quantity;
        });

        return Inertia::render('Shop/Checkout', [
            'items' => $items,
            'total' => $total,
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

        $items = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) return back();

        $total = $items->sum(function ($item) {
            return $item->product->price_with_discount * $item->quantity;
        });

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
                'price_at_purchase' => $item->product->price_with_discount,
            ]);
            $item->product->decrement('quantity', $item->quantity);
        }

        Cart::where('user_id', auth()->id())->delete();

        broadcast(new NewOrderPlaced($order));

        return redirect()->route('profile');
    }

    public function show($uuid)
    {
        $order = Order::with(['items.product', 'messages'])->where('uuid', $uuid)->firstOrFail();
        if ($order->user_id !== auth()->id()) abort(403);

        // Clear user-facing notification flags when user opens the order
        $updates = [];
        if ($order->user_has_unseen_status_change) $updates['user_has_unseen_status_change'] = false;
        if ($order->user_has_unseen_contact_update) $updates['user_has_unseen_contact_update'] = false;
        if (!empty($updates)) $order->update($updates);

        return Inertia::render('Shop/Order', ['order' => $order]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        if ($order->status !== 'new') return back();

        $order->update(['status' => 'cancelled_by_user']);

        foreach ($order->items as $item) {
            $item->product->increment('quantity', $item->quantity);
        }

        broadcast(new \App\Events\OrderUpdated($order, 'cancelled'));

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

        $message = $order->messages()->create([
            'sender_role' => 'user',
            'message'     => $request->message,
        ]);

        // NOTE: has_unseen_activity is NOT set here — messages are already tracked
        // via unread_messages_count (is_read flag). has_unseen_activity is reserved
        // exclusively for contact updates so the admin edit icon stays unambiguous.

        broadcast(new \App\Events\NewOrderMessage($message));
        broadcast(new \App\Events\OrderUpdated($order, 'new_message'));

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
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update(array_merge(
            $request->only(['city', 'street', 'house', 'comment', 'phone']),
            ['has_unseen_activity' => true] // exclusively means "user updated contacts"
        ));

        broadcast(new \App\Events\OrderUpdated($order, 'contacts_updated'));

        return response()->json(['success' => true]);
    }

    public function readMessages(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        $order->messages()->where('sender_role', 'admin')->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }
}