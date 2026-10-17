<?php

namespace App\Livewire\Panel\Content\Banner;

use App\Models\Content\Banner;
use App\Services\Content\BannerImageService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected const SORTABLE = ['sort_order', 'title', 'is_active', 'created_at'];

    public string $search = '';

    public string $statusFilter = '';

    public string $trashedFilter = 'without';

    public string $sortBy = 'sort_order';

    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->authorize('content_banner_index');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTrashedFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function banners(): LengthAwarePaginator
    {
        $sortBy = in_array($this->sortBy, self::SORTABLE, true) ? $this->sortBy : 'sort_order';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Banner::query()
            ->when($this->trashedFilter === 'with', fn ($query) => $query->withTrashed())
            ->when($this->trashedFilter === 'only', fn ($query) => $query->onlyTrashed())
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search)
                        ->orWhere('link', 'like', $search);
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy($sortBy, $sortDirection)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function moveUp(int $id): void
    {
        $this->authorize('content_banner_edit');

        $banner = Banner::query()->find($id);

        if ($banner === null) {
            return;
        }

        $banner->moveOrderUp();
        Cache::forget(Banner::CACHE_KEY_ACTIVE);

        unset($this->banners);
        Flux::toast(variant: 'success', text: __('general.banner_order_updated'));
    }

    public function moveDown(int $id): void
    {
        $this->authorize('content_banner_edit');

        $banner = Banner::query()->find($id);

        if ($banner === null) {
            return;
        }

        $banner->moveOrderDown();
        Cache::forget(Banner::CACHE_KEY_ACTIVE);

        unset($this->banners);
        Flux::toast(variant: 'success', text: __('general.banner_order_updated'));
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('content_banner_edit');

        $banner = Banner::query()->find($id);

        if ($banner === null) {
            return;
        }

        $banner->update(['is_active' => ! $banner->is_active]);

        unset($this->banners);
        Flux::toast(variant: 'success', text: __('general.banner_status_updated'));
    }

    public function delete(int $id): void
    {
        $this->authorize('content_banner_delete');

        $banner = Banner::query()->find($id);

        if ($banner === null) {
            return;
        }

        $banner->delete();

        $this->dispatch('panel.content.banner.index.render');
        Flux::toast(variant: 'success', text: __('general.banner_deleted'));
    }

    public function restore(int $id): void
    {
        $this->authorize('content_banner_edit');

        $banner = Banner::onlyTrashed()->find($id);

        if ($banner === null) {
            return;
        }

        $banner->restore();

        $this->dispatch('panel.content.banner.index.render');
        Flux::toast(variant: 'success', text: __('general.banner_restored'));
    }

    public function forceDelete(int $id): void
    {
        $this->authorize('content_banner_delete');

        $banner = Banner::withTrashed()->find($id);

        if ($banner === null) {
            return;
        }

        $paths = [
            $banner->desktop_image,
            $banner->tablet_image,
            $banner->mobile_image,
        ];

        $banner->forceDelete();

        app(BannerImageService::class)->deleteFiles($paths);

        $this->dispatch('panel.content.banner.index.render');
        Flux::toast(variant: 'success', text: __('general.banner_force_deleted'));
    }

    #[Layout('layouts.panels.content')]
    #[On('panel.content.banner.index.render')]
    public function render()
    {
        $this->authorize('content_banner_index');

        return view('livewire.panel.content.banner.index');
    }
}
