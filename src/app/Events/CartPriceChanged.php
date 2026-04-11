<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CartPriceChanged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public Product $product,
        public float $oldPrice,
        public float $newPrice,
        public int $oldDiscount,
        public int $newDiscount,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'CartPriceChanged';
    }

    public function broadcastWith(): array
    {
        $oldFinal = $this->oldDiscount > 0
            ? round($this->oldPrice * (1 - $this->oldDiscount / 100))
            : $this->oldPrice;

        $newFinal = $this->newDiscount > 0
            ? round($this->newPrice * (1 - $this->newDiscount / 100))
            : $this->newPrice;

        return [
            'product_id'    => $this->product->id,
            'product_title' => $this->product->title,
            'old_price'     => $oldFinal,
            'new_price'     => $newFinal,
        ];
    }
}