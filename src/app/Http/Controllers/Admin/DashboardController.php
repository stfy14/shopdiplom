<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $newOrders = Order::where('status', 'new')->count();
        $activeOrders = Order::whereIn('status', ['new', 'processing', 'shipped'])->count();
        $totalRevenue = Order::whereNotIn('status', ['cancelled', 'cancelled_by_user'])->sum('total_price');
        
        $totalProducts = Product::where('is_deleted', false)->count();
        $outOfStock = Product::where('is_deleted', false)->where('quantity', 0)->count();
        
        $totalUsers = User::count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' =>[
                'totalOrders' => $totalOrders,
                'newOrders' => $newOrders,
                'activeOrders' => $activeOrders,
                'totalRevenue' => $totalRevenue,
                'totalProducts' => $totalProducts,
                'outOfStock' => $outOfStock,
                'totalUsers' => $totalUsers,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }
}