<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('Shop/Profile', [
            'orders' => $orders,
            'user'   => auth()->user(),
        ]);
    }
}