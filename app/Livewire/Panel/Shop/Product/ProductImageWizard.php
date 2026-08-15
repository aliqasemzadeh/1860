<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Services\Shop\ProductImageSeoService;
use App\Support\GetProductImages\BaseImageFetcher;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductImageWizard extends Component
{
    public ?Product $product = null;

    public ?int $productId = null;

    public string $site_type = '';

    public string $url = '';

    public array $images = []; // [['url' => '', 'name' => '', 'id' => unique_id]]

    public bool $isLoading = false;

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

    #[On('panel.shop.product.images.wizard.assign-data')]
    public function assignData($id): void
    {
        $this->product = Product::findOrFail($id);
        $this->productId = $this->product->id;
        $this->site_type = '';
        $this->url = '';
        $this->images = [];
        $this->isLoading = false;
        Flux::modal('panel.shop.product.images.wizard.modal')->show();
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
                        'optimize' => false, // بهینه‌سازی پیش‌فرض غیرفعال
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

    public function updateImageName(string $imageId, string $name): void
    {
        foreach ($this->images as $index => $image) {
            if ($image['id'] === $imageId) {
                $this->images[$index]['name'] = $name;
                break;
            }
        }
    }

    public function removeImage(string $imageId): void
    {
        $this->images = array_values(array_filter($this->images, function ($image) use ($imageId) {
            return $image['id'] !== $imageId;
        }));
    }

    public function save(): void
    {
        if (! $this->product) {
            return;
        }

        if (empty($this->images)) {
            Flux::toast(variant: 'warning', text: __('general.no_images_selected'));
            return;
        }

        $this->isLoading = true;

        try {
            // Ensure directory exists
            Storage::disk('public')->makeDirectory('product-images');

            $successCount = 0;
            $failCount = 0;
            $seo = app(ProductImageSeoService::class);

            foreach ($this->images as $imageData) {
                try {
                    $imageResponse = Http::timeout(60)->get($imageData['url']);

                    if (! $imageResponse->successful()) {
                        $failCount++;

                        continue;
                    }

                    $paths = $seo->storeAsWebp($imageResponse->body(), 'product-images', $this->product);

                    $productImage = ProductImage::create([
                        'product_id' => $this->product->id,
                        'file_path' => $paths['file_path'],
                        'file_name' => $paths['file_name'],
                    ]);

                    if (! empty($imageData['optimize']) && $imageData['optimize']) {
                        \App\Jobs\Shop\ProductImageOptimizeJob::dispatch($productImage->id);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                    \Log::error('Failed to upload image: '.$e->getMessage(), [
                        'url' => $imageData['url'] ?? null,
                        'product_id' => $this->product->id,
                    ]);
                }
            }

            if ($successCount > 0) {
                $this->product->refresh();
                Flux::toast(variant: 'success', text: __('general.images_uploaded', ['count' => $successCount]));
                Flux::modal('panel.shop.product.images.wizard.modal')->close();
                $this->dispatch('panel.shop.product.images.refresh');
            }

            if ($failCount > 0) {
                Flux::toast(variant: 'warning', text: __('general.some_images_failed', ['count' => $failCount]));
            }
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('general.error_uploading_images') . ': ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-image-wizard');
    }
}
