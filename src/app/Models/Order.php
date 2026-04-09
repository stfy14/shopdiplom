<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'address', 'phone', 'total_price', 'status'
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
}