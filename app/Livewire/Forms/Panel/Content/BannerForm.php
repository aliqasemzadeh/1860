<?php

namespace App\Livewire\Forms\Panel\Content;

use App\Models\Content\Banner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Form;

class BannerForm extends Form
{
    public ?int $bannerId = null;

    public string $title = '';

    public ?string $link = null;

    public ?string $desktop_image = null;

    public ?string $tablet_image = null;

    public ?string $mobile_image = null;

    public bool $is_active = true;

    public ?int $sort_order = null;

    public function setBanner(Banner $banner): void
    {
        $this->bannerId = $banner->id;
        $this->title = (string) $banner->title;
        $this->link = $banner->link;
        $this->desktop_image = $banner->desktop_image;
        $this->tablet_image = $banner->tablet_image;
        $this->mobile_image = $banner->mobile_image;
        $this->is_active = (bool) $banner->is_active;
        $this->sort_order = $banner->sort_order;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:500'],
            'desktop_image' => ['required', 'string', 'max:255'],
            'tablet_image' => ['nullable', 'string', 'max:255'],
            'mobile_image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function store(): Banner
    {
        $validated = $this->validate();

        return DB::transaction(function () use ($validated) {
            $payload = $this->payload($validated);

            if ($payload['sort_order'] === null) {
                unset($payload['sort_order']);
            }

            $banner = Banner::query()->create($payload);

            Cache::forget(Banner::CACHE_KEY_ACTIVE);

            return $banner;
        });
    }

    public function update(Banner $banner): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($banner, $validated) {
            $payload = $this->payload($validated);

            if ($payload['sort_order'] === null) {
                $payload['sort_order'] = $banner->sort_order ?? 0;
            }

            $banner->update($payload);

            Cache::forget(Banner::CACHE_KEY_ACTIVE);
        });
    }

    protected function payload(array $validated): array
    {
        $link = isset($validated['link']) ? trim((string) $validated['link']) : null;

        return [
            'title' => trim((string) $validated['title']),
            'link' => $link !== '' ? $link : null,
            'desktop_image' => $validated['desktop_image'],
            'tablet_image' => $validated['tablet_image'] ?? null,
            'mobile_image' => $validated['mobile_image'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => isset($validated['sort_order']) && $validated['sort_order'] !== ''
                ? (int) $validated['sort_order']
                : null,
        ];
    }
}
