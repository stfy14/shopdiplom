<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')
            ->latest()
            ->get();

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
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

        OrderMessage::create([
            'order_id'    => $order->id,
            'sender_role' => 'admin',
            'message'     => $request->message,
        ]);

        return back();
    }
}