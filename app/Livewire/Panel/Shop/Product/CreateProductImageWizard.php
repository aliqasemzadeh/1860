<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Support\GetProductImages\BaseImageFetcher;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateProductImageWizard extends Component
{
    public string $site_type = '';

    public string $url = '';

    public array $images = []; // [['url' => '', 'name' => '', 'id' => unique_id]]

    public bool $isLoading = false;

    public ?string $selectedImageUrl = null;

    public function getSiteTypes(): array
    {
        return [
            'logitech' => 'Logitech',
            'logikey' => 'Logi-key',
            'gigabyte' => 'Gigabyte',
            'xvision' => 'X-Vision',
            'matin' => 'Matin',
            'green' => 'Green',
            'fater' => 'Fater',
            'nova' => 'Nova',
            'avajang' => 'Avajang',
            'fafait' => 'Fafait',
            'generic' => 'سایر (عمومی)',
        ];
    }

    public function mount(): void
    {
        $this->resetWizard();
    }

    public function resetWizard(): void
    {
        $this->site_type = '';
        $this->url = '';
        $this->images = [];
        $this->isLoading = false;
        $this->selectedImageUrl = null;
    }

    #[On('panel.shop.product.create.image-wizard.open')]
    public function openModal(): void
    {
        $this->resetWizard();
        Flux::modal('panel.shop.product.create.image-wizard.modal')->show();
    }

    public function fetchImages(): void
    {
        $this->validate([
            'site_type' => 'required|string|in:logitech,logikey,gigabyte,xvision,matin,green,fater,avajang,generic,nova,fafait',
            'url' => 'required|url',
        ], [], [
            'site_type' => __('general.site_type'),
            'url' => __('general.url'),
        ]);

        $this->isLoading = true;

        try {
            $imageUrls = BaseImageFetcher::fetchBySiteType($this->site_type, $this->url);

            if (empty($imageUrls)) {
                Flux::toast(variant: 'warning', text: __('general.no_images_found'));
                $this->isLoading = false;
                return;
            }

            // Add new images to the list
            foreach ($imageUrls as $imageUrl) {
                // Check if this URL already exists
                $exists = false;
                foreach ($this->images as $existingImage) {
                    if ($existingImage['url'] === $imageUrl) {
                        $exists = true;
                        break;
                    }
                }

                if (! $exists) {
                    $baseName = basename(parse_url($imageUrl, PHP_URL_PATH));
                    if (empty($baseName) || ! preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $baseName)) {
                        $baseName = 'image-' . (count($this->images) + 1);
                    }

                    $this->images[] = [
                        'id' => Str::random(10),
                        'url' => $imageUrl,
                        'name' => $baseName,
                    ];
                }
            }

            Flux::toast(variant: 'success', text: __('general.images_fetched', ['count' => count($imageUrls)]));
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('general.error_fetching_images') . ': ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function selectImage(string $imageId): void
    {
        foreach ($this->images as $image) {
            if ($image['id'] === $imageId) {
                $this->selectedImageUrl = $image['url'];
                break;
            }
        }
    }

    public function confirmSelection(): void
    {
        if (! $this->selectedImageUrl) {
            Flux::toast(variant: 'warning', text: __('general.no_image_selected'));
            return;
        }

        // Dispatch event to Create component with selected image URL
        $this->dispatch('panel.shop.product.create.image-selected', url: $this->selectedImageUrl);

        Flux::modal('panel.shop.product.create.image-wizard.modal')->close();
        Flux::toast(variant: 'success', text: __('general.image_selected'));
        $this->resetWizard();
    }

    public function removeImage(string $imageId): void
    {
        $this->images = array_values(array_filter($this->images, function ($image) use ($imageId) {
            return $image['id'] !== $imageId;
        }));

        // If removed image was selected, clear selection
        if ($this->selectedImageUrl) {
            $wasSelected = false;
            foreach ($this->images as $image) {
                if ($image['url'] === $this->selectedImageUrl) {
                    $wasSelected = true;
                    break;
                }
            }
            if (! $wasSelected) {
                $this->selectedImageUrl = null;
            }
        }
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.create-product-image-wizard');
    }
}

