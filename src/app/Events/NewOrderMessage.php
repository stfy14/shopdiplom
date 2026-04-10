<?php

namespace App\Events;

use App\Models\OrderMessage;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderMessage implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(public OrderMessage $message) {}

    public function broadcastOn(): Channel
    {
        return new Channel('order.' . $this->message->order_id);
    }

    public function broadcastWith(): array
    {
        return $this->message->toArray();
    }
}