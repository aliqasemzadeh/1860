<?php

namespace App\Livewire\Panel\Content\Banner;

use App\Livewire\Forms\Panel\Content\BannerForm;
use App\Models\Content\Banner;
use App\Services\Content\BannerImageService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public BannerForm $form;

    public ?Banner $banner = null;

    #[Validate('nullable|image|max:8192')]
    public $desktop_file = null;

    #[Validate('nullable|image|max:5120')]
    public $mobile_file = null;

    #[On('panel.content.banner.edit.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('content_banner_edit');

        $this->banner = Banner::withTrashed()->findOrFail($id);

        $this->form->setBanner($this->banner);
        $this->reset(['desktop_file', 'mobile_file']);

        Flux::modal('panel.content.banner.edit.modal')->show();
    }

    public function removeDesktopFile(): void
    {
        if ($this->desktop_file) {
            $this->desktop_file->delete();
            $this->desktop_file = null;
            Flux::toast(variant: 'success', text: __('general.file_removed'));
        }
    }

    public function removeMobileFile(): void
    {
        if ($this->mobile_file) {
            $this->mobile_file->delete();
            $this->mobile_file = null;
            Flux::toast(variant: 'success', text: __('general.file_removed'));
        }
    }

    public function edit(BannerImageService $imageService): void
    {
        $this->authorize('content_banner_edit');

        if ($this->banner === null) {
            return;
        }

        $this->validate([
            'form.title' => ['required', 'string', 'max:255'],
            'form.link' => ['nullable', 'string', 'max:500'],
            'form.is_active' => ['boolean'],
            'form.sort_order' => ['nullable', 'integer', 'min:0'],
            'desktop_file' => ['nullable', 'image', 'max:8192'],
            'mobile_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $oldDesktop = $this->banner->desktop_image;
        $oldTablet = $this->banner->tablet_image;
        $oldMobile = $this->banner->mobile_image;

        if ($this->desktop_file) {
            $paths = $imageService->storeResponsiveSet(
                $this->desktop_file,
                $this->form->title,
                $this->mobile_file
            );

            $this->form->desktop_image = $paths['desktop_image'];
            $this->form->tablet_image = $paths['tablet_image'];
            $this->form->mobile_image = $paths['mobile_image'];

            $toDelete = array_diff(
                array_filter([$oldDesktop, $oldTablet, $oldMobile]),
                [$paths['desktop_image'], $paths['tablet_image'], $paths['mobile_image']]
            );
            $imageService->deleteFiles(array_values($toDelete));
        } elseif ($this->mobile_file) {
            $this->form->mobile_image = $imageService->storeMobileOnly(
                $this->mobile_file,
                $this->form->title,
                $oldMobile
            );
        }

        $this->form->update($this->banner);

        Flux::modal('panel.content.banner.edit.modal')->close();
        $this->dispatch('panel.content.banner.index.render');
        Flux::toast(variant: 'success', text: __('general.banner_updated'));

        $this->reset(['desktop_file', 'mobile_file']);
    }

    public function render(): View
    {
        return view('livewire.panel.content.banner.edit');
    }
}
