<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    public $fillable = ['name', 'slug', 'slug_fa', 'icon', 'sort_order', 'main_category_id'];

    public function main_category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id')->withTrashed();
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'main_category_id');
    }

    /**
     * Get the attributes assigned to this category.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Model casts.
     */
    public function casts(): array
    {
        return [
            'main_category_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Canonical public URL: /category/{id}/{slug_fa}.
     */
    public function getUrlAttribute(): string
    {
        return route('category.view', [
            'id' => $this->id,
            'slug' => $this->slug_fa ?: $this->slug,
        ]);
    }
}
