<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'code', 'parent_id', 'image', 'description', 'sort_order'];

    // Родительская категория
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Дочерние категории
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    // Товары прямо в этой категории
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Характеристики этой категории
    public function characteristics()
    {
        return $this->hasMany(Characteristic::class);
    }

    // Является ли категория родительской
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    // URL картинки
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? '/storage/' . $this->image : null;
    }
}