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

    public function broadcastOn(): PrivateChannel // ИЗМЕНЕНО
    {
        return new PrivateChannel('admin-notifications'); // ИЗМЕНЕНО
    }

    public function broadcastAs(): string
    {
        return 'NewOrderPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'newOrdersCount'   => Order::where('status', 'new')->count(),
            'newMessagesCount' => Order::where('has_unseen_activity', true)->count(),
        ];
    }
}