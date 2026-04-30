<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cartItems = [];
        $cartCount = 0;
        $user = $request->user();

        if ($user) {
            $cart = \App\Models\Cart::with('product')->where('user_id', $user->id)->get();
            $cartItems = $cart;
            $cartCount = $cart->sum('quantity');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'cartCount' => $cartCount,
            'cartItems' => $cartItems,
            'flash' => [
                'cart_added'    => $request->session()->get('cart_added'),
                'callback_sent' => $request->session()->get('callback_sent'),
            ],

            'notifications' => $user ? $user->notifications()->take(50)->get()->map(function ($n) {
                return [
                    'id'        => $n->id,
                    'message'   => $n->data['message'],
                    'type'      => $n->data['type'] ?? 'success',
                    'href'      => $n->data['href'] ?? null,
                    'icon'      => $n->data['icon'] ?? null,
                    'read'      => $n->read_at !== null,
                    'createdAt' => $n->created_at,
                ];
            }) : [],

            'adminNotifications' => $user?->role === 'admin' ? [
                'newOrders'   => \App\Models\Order::where('status', 'new')->count(),
                'newMessages' => \App\Models\Order::where('has_unseen_activity', true)
                    ->orWhereIn('id', function ($query) {
                        $query->select('order_id')
                            ->from('order_messages')
                            ->where('sender_role', 'user')
                            ->where('is_read', false);
                    })->count(),
            ] : null,
        ];
    }
}