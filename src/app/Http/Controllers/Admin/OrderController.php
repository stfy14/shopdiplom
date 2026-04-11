<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Events\NewOrderMessage;
use App\Events\OrderUpdated;
use App\Notifications\AppNotification;
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

        return inertia('Admin/Orders', ['orders' => $query->get(), 'tab' => $tab]);
    }

    public function show(Order $order)
    {
        $needsBroadcast = false;

        if ($order->has_unseen_activity) {
            $order->update(['has_unseen_activity' => false]);
            $needsBroadcast = true;
        }
        if ($order->unreadMessages()->exists()) {
            $order->unreadMessages()->update(['is_read' => true]);
            $needsBroadcast = true;
        }

        if ($needsBroadcast) broadcast(new OrderUpdated($order, 'read'));

        $order->load(['user', 'items.product', 'messages']);
        return Inertia::render('Admin/OrderView',['order' => $order]);
    }

    public function updateStatus(Order $order, Request $request)
    {
        $request->validate(['status' => 'required|in:new,processing,shipped,cancelled,cancelled_by_user']);
        $order->update([
            'status'                        => $request->status,
            'user_has_unseen_status_change' => true,
        ]);

        $label =[
            'new' => 'Новый', 'processing' => 'В обработке', 'shipped' => 'Отправлен', 
            'cancelled' => 'Отменён', 'cancelled_by_user' => 'Отменён клиентом'
        ][$request->status] ?? $request->status;

        $order->user->notify(new AppNotification("Заказ #{$order->id}: статус изменён на «{$label}»", 'success', "/orders/{$order->uuid}", 'status'));

        broadcast(new OrderUpdated($order, 'status_change'));
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

        $order->user->notify(new \App\Notifications\AppNotification("Новый ответ от поддержки по заказу #{$order->id}", 'success', "/orders/{$order->uuid}", 'message'));

        broadcast(new NewOrderMessage($message));
        
        // ДОБАВЛЕНА ЭТА СТРОКА:
        broadcast(new OrderUpdated($order, 'new_message'));

        return response()->json($message);
    }

    public function updateContacts(Order $order, Request $request)
    {
        $request->validate([
            'city'    => 'required|string|max:100',
            'street'  => 'required|string|max:255',
            'house'   => 'required|string|max:50',
            'comment' => 'nullable|string|max:1000',
            'phone'   =>['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        $order->update(array_merge(
            $request->only(['city', 'street', 'house', 'comment', 'phone']),['user_has_unseen_contact_update' => true]
        ));

        $order->user->notify(new AppNotification("Менеджер обновил контакты заказа #{$order->id}", 'success', "/orders/{$order->uuid}", 'edit'));

        broadcast(new OrderUpdated($order, 'contacts_updated_by_admin'));
        return response()->json(['success' => true]);
    }
}