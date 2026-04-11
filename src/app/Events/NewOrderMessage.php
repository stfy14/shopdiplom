<?php

namespace App\Events;

use App\Models\OrderMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // ИЗМЕНЕНО
use Illuminate\Foundation\Events\Dispatchable;

// ИЗМЕНЕНО: implements ShouldBroadcastNow
class NewOrderMessage implements ShouldBroadcastNow 
{
    use Dispatchable;

    public function __construct(public OrderMessage $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('order.' . $this->message->order_id);
    }

    public function broadcastAs(): string
    {
        return 'NewOrderMessage';
    }

    public function broadcastWith(): array
    {
        return $this->message->toArray();
    }
}