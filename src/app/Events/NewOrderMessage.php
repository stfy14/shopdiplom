<?php

namespace App\Events;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderMessage implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public OrderMessage $message) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('order.' . $this->message->order_id)];

        $order = Order::find($this->message->order_id);
        if ($order) {
            if ($this->message->sender_role === 'admin') {
                // Админ пишет — уведомляем пользователя на его личном канале
                $channels[] = new PrivateChannel('user.' . $order->user_id);
            }
            // Пользователь пишет — уведомляем админа через admin-notifications
            // (это уже делает OrderUpdated с типом new_message, здесь не дублируем)
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'NewOrderMessage';
    }

    public function broadcastWith(): array
    {
        $order = Order::find($this->message->order_id);
        $data = $this->message->toArray();
        $data['order_uuid']   = $order?->uuid;
        $data['order_number'] = $this->message->order_id;
        return $data;
    }
}