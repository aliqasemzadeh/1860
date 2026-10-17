<?php

namespace App\Livewire\Panel\Content\Banner;

use App\Livewire\Forms\Panel\Content\BannerForm;
use App\Services\Content\BannerImageService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public BannerForm $form;

    #[Validate('required|image|max:8192')]
    public $desktop_file = null;

    #[Validate('nullable|image|max:5120')]
    public $mobile_file = null;

    public function removeDesktopFile(): void
    {
        if ($this->desktop_file) {
            $this->desktop_file->delete();
        }

        $this->desktop_file = null;
        Flux::toast(variant: 'success', text: __('general.file_removed'));
    }

    public function removeMobileFile(): void
    {
        if ($this->mobile_file) {
            $this->mobile_file->delete();
        }

        $this->mobile_file = null;
        Flux::toast(variant: 'success', text: __('general.file_removed'));
    }

    public function create(BannerImageService $imageService): void
    {
        $this->authorize('content_banner_create');

        $this->validate([
            'form.title' => ['required', 'string', 'max:255'],
            'form.link' => ['nullable', 'string', 'max:500'],
            'form.is_active' => ['boolean'],
            'form.sort_order' => ['nullable', 'integer', 'min:0'],
            'desktop_file' => ['required', 'image', 'max:8192'],
            'mobile_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $paths = $imageService->storeResponsiveSet(
            $this->desktop_file,
            $this->form->title,
            $this->mobile_file
        );

        $this->form->desktop_image = $paths['desktop_image'];
        $this->form->tablet_image = $paths['tablet_image'];
        $this->form->mobile_image = $paths['mobile_image'];

        $this->form->store();

        Flux::modal('panel.content.banner.create.modal')->close();
        $this->dispatch('panel.content.banner.index.render');
        Flux::toast(variant: 'success', text: __('general.banner_created'));

        $this->form->reset();
        $this->form->is_active = true;
        $this->reset(['desktop_file', 'mobile_file']);
    }

    public function render(): View
    {
        return view('livewire.panel.content.banner.create');
    }
}
