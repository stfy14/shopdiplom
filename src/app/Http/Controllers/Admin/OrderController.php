<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Events\NewOrderMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $tab = $request->get('tab', 'active'); // Получаем текущую вкладку

        $query = \App\Models\Order::with('user')
            ->withCount('unreadMessages')
            ->latest();

        // Фильтруем в зависимости от вкладки
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
        // Сбрасываем флаги ПЕРЕД загрузкой данных
        if ($order->has_unseen_activity) {
            $order->update(['has_unseen_activity' => false]);
        }
        $order->unreadMessages()->update(['is_read' => true]);

        // Подгружаем данные для Vue уже с "чистыми" флагами
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

        return back();
    }

    public function getMessages(Order $order)
    {
        return response()->json($order->messages()->oldest()->get());
    }

    public function sendMessage(Order $order, Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $message = OrderMessage::create([   // <-- должно быть $message =
            'order_id'    => $order->id,
            'sender_role' => 'user',
            'message'     => $request->message,
        ]);

        $order->update(['has_unseen_activity' => true]);

        broadcast(new NewOrderMessage($message));  // <-- использует $message

        return back();
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

        return back();
    }
}