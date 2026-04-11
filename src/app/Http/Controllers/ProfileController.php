<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->withCount(['messages as unread_messages_count' => function ($query) {
                $query->where('sender_role', 'admin')->where('is_read', false);
            }])
            ->latest()
            ->get();

        return Inertia::render('Shop/Profile', [
            'orders' => $orders,
            'user'   => auth()->user(),
        ]);
    }
}