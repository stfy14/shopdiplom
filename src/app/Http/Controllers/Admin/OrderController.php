<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Events\NewOrderMessage;
use App\Events\OrderUpdated; // <-- Убедитесь, что это добавлено
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active');
        $query = Order::with('user')->withCount('unreadMessages')->latest();

        if ($tab === 'archive') {
            $query->whereIn('status', ['cancelled', 'cancelled_by_user']);
        } else {
            $query->whereNotIn('status', ['cancelled', 'cancelled_by_user']);
        }

        $orders = $query->get();

        return inertia('Admin/Orders',[
            'orders' => $orders,
            'tab'    => $tab,
        ]);
    }

    public function show(Order $order)
    {
        $needsBroadcast = false;
        if ($order->has_unseen_activity) {
            $order->has_unseen_activity = false;
            $needsBroadcast = true;
        }
        if ($order->unreadMessages()->exists()) {
            $order->unreadMessages()->update(['is_read' => true]);
            $needsBroadcast = true;
        }

        if ($needsBroadcast) {
            $order->save();
            // ГЛАВНОЕ ИСПРАВЛЕНИЕ: Сообщаем всем, что уведомления прочитаны
            broadcast(new OrderUpdated($order));
        }

        $order->load(['user', 'items.product', 'messages']);

        return Inertia::render('Admin/OrderView', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:new,processing,shipped,cancelled,cancelled_by_user',
        ]);
        $order->update(['status' => $request->status]);
        broadcast(new OrderUpdated($order));
        return response()->json(['success' => true]);
    }

    public function getMessages(Order $order)
    {
        return response()->json($order->messages()->oldest()->get());
    }

    public function sendMessage(Order $order, Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $message = OrderMessage::create([
            'order_id'    => $order->id,
            'sender_role' => 'admin',
            'message'     => $request->message,
        ]);

        broadcast(new NewOrderMessage($message));
        
        return response()->json($message);
    }

    public function updateContacts(Order $order, Request $request)
    {
        $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update($request->only(['city', 'street', 'house', 'comment', 'phone']));
        broadcast(new OrderUpdated($order));
        return response()->json(['success' => true]);
    }
}