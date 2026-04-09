<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 'price_with_discount', 'quantity',
        'discount', 'image', 'category_id', 'is_deleted'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function characteristics()
    {
        return $this->hasMany(ProductCharacteristic::class);
    }

    public function getPriceWithDiscountAttribute()
    {
        if ($this->discount > 0) {
            return round($this->price * (1 - $this->discount / 100));
        }
        return $this->price;
    }
}