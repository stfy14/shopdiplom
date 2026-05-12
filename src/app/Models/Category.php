<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'code', 'parent_id', 'image', 'description', 'sort_order', 'gost', 'din'];

    // Родительская категория
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Дочерние категории (первый уровень)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    // Рекурсивные дочерние категории любой глубины
    public function allChildren()
    {
        return $this->children()->with(['allChildren.allChildren.allChildren']);
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

    // Является ли категория листовой (нет дочерних)
    public function isLeaf(): bool
    {
        return !$this->children()->exists();
    }

    // Является ли категория корневой
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    // @deprecated - используй isRoot()
    public function isParent(): bool
    {
        return $this->isRoot();
    }

    // Получить цепочку предков (от корня к текущей)
    public function getAncestors(): array
    {
        $ancestors = [];
        $current = $this->parent;
        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }
        return $ancestors;
    }

    // URL картинки
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? '/storage/' . $this->image : null;
    }
}