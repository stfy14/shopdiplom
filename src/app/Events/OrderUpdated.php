<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OrderUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return[
            // Уведомляем страницу самого заказа
            new PrivateChannel('order.' . $this->order->id),
            // Уведомляем клиента для его списка заказов
            new PrivateChannel('user.' . $this->order->user_id),
            // Уведомляем админов для общего списка
            new PrivateChannel('admin-notifications')
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderUpdated';
    }
}