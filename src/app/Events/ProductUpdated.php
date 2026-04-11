<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ProductUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function broadcastOn(): array
    {
        return[
            new PrivateChannel('admin-notifications')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ProductUpdated';
    }
}