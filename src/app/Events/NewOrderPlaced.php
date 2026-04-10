<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderPlaced implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(public Order $order) {}

    public function broadcastOn(): Channel
    {
        return new Channel('admin-notifications');
    }

    public function broadcastWith(): array
    {
        return [
            'newOrdersCount'   => Order::where('status', 'new')->count(),
            'newMessagesCount' => Order::where('has_unseen_activity', true)->count(),
        ];
    }
}