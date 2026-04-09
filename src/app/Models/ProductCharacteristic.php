<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCharacteristic extends Model
{
    protected $fillable = ['product_id', 'characteristic_id', 'value'];

    public function characteristic()
    {
        return $this->belongsTo(Characteristic::class);
    }
}