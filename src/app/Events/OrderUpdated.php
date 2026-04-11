<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OrderUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    // Типы: status_change | new_message | contacts_updated | contacts_updated_by_admin | cancelled
    public function __construct(
        public Order $order,
        public string $type = 'status_change'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('admin-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => $this->order->toArray(),
            'type'  => $this->type,
        ];
    }
}