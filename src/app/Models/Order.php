<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    // ДОБАВЛЕНЫ 3 НОВЫХ ФЛАГА В КОНЕЦ
    protected $fillable =[
        'uuid', 'user_id', 'phone', 'total_price', 'status',
        'city', 'street', 'house', 'comment',
        'has_unseen_activity', 'user_has_unseen_status_change', 'user_has_unseen_contact_update'
    ];

    // Явное приведение к булевому типу, чтобы Vue и Inertia понимали их как true/false, а не 1/0
    protected function casts(): array
    {
        return[
            'has_unseen_activity' => 'boolean',
            'user_has_unseen_status_change' => 'boolean',
            'user_has_unseen_contact_update' => 'boolean',
        ];
    }

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