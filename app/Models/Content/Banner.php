<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Banner extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;

    public const CACHE_KEY_ACTIVE = 'home.banners.active';

    protected $fillable = [
        'title',
        'link',
        'desktop_image',
        'tablet_image',
        'mobile_image',
        'is_active',
        'sort_order',
    ];

    public array $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $flush = static function (): void {
            Cache::forget(self::CACHE_KEY_ACTIVE);
        };

        static::saved($flush);
        static::deleted($flush);
        static::restored($flush);
        static::forceDeleted($flush);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getDesktopImageUrlAttribute(): ?string
    {
        if (! $this->desktop_image) {
            return null;
        }

        return Storage::disk('public')->url($this->desktop_image);
    }

    public function getTabletImageUrlAttribute(): ?string
    {
        $path = $this->tablet_image ?: $this->desktop_image;

        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function getMobileImageUrlAttribute(): ?string
    {
        $path = $this->mobile_image ?: ($this->tablet_image ?: $this->desktop_image);

        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
