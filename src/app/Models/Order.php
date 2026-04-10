<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'phone', 'total_price', 'status',
        'city', 'street', 'house', 'comment'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->uuid = Str::uuid();
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function messages()
    {
        return $this->hasMany(OrderMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unreadMessages()
    {
        return $this->hasMany(OrderMessage::class)
            ->where('sender_role', 'user')
            ->where('is_read', false);
    }
}