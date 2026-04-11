<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Доступ к чату заказа
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    // Админов пускаем всегда
    if ($user->role === 'admin') {
        return true;
    }
    // Обычного юзера пускаем, только если это его заказ
    $order = \App\Models\Order::find($orderId);
    return $order && (int) $order->user_id === (int) $user->id;
});

// Доступ к уведомлениям
Broadcast::channel('admin-notifications', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});