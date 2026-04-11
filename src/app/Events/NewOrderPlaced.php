<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderPlaced implements ShouldBroadcastNow 
{
    use Dispatchable;

    public function __construct(public Order $order) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin-notifications');
    }

    public function broadcastAs(): string
    {
        return 'NewOrderPlaced';
    }

    public function broadcastWith(): array
    {
        return[
            'order' => $this->order->toArray(), // ЭТОЙ СТРОКИ НЕ БЫЛО!
            'newOrdersCount'   => Order::where('status', 'new')->count(),
            'newMessagesCount' => Order::where('has_unseen_activity', true)->count(),
        ];
    }
}