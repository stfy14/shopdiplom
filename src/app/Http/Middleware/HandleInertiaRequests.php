<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cartItems = [];
        $cartCount = 0;

        if ($request->user()) {
            $cart = \App\Models\Cart::with('product')
                ->where('user_id', $request->user()->id)
                ->get();
            $cartItems = $cart;
            $cartCount = $cart->sum('quantity');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'cartCount' => $cartCount,
            'cartItems' => $cartItems,
            'flash' => [
                'cart_added' => $request->session()->get('cart_added'),
            ],
            'adminNotifications' => $request->user()?->role === 'admin' ? [
            'newOrders'    => \App\Models\Order::where('status', 'new')->count(),
            'newMessages'  => \App\Models\Order::where('has_unseen_activity', true)
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
