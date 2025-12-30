<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
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
            'site_type' => 'required|string|in:logitech,logikey,gigabyte,xvision,matin,green,fater,generic',
            'url' => 'required|url',
        ], [], [
            'site_type' => __('app.site_type'),
            'url' => __('app.url'),
        ]);

        $this->isLoading = true;

        try {
            $imageUrls = BaseImageFetcher::fetchBySiteType($this->site_type, $this->url);

            if (empty($imageUrls)) {
                Flux::toast(variant: 'warning', text: __('app.no_images_found'));
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

            Flux::toast(variant: 'success', text: __('app.images_fetched', ['count' => count($imageUrls)]));
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('app.error_fetching_images') . ': ' . $e->getMessage());
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
            Flux::toast(variant: 'warning', text: __('app.no_images_selected'));
            return;
        }

        $this->isLoading = true;

        try {
            // Ensure directory exists
            Storage::disk('public')->makeDirectory('product-images');

            $successCount = 0;
            $failCount = 0;

            foreach ($this->images as $imageData) {
                try {
                    // Download the image
                    $imageResponse = Http::timeout(30)->get($imageData['url']);

                    if (! $imageResponse->successful()) {
                        $failCount++;
                        continue;
                    }

                    // Get file extension from URL or content type
                    $extension = $this->getFileExtension($imageData['url'], $imageResponse->header('Content-Type', ''));
                    $fileName = Str::slug(pathinfo($imageData['name'], PATHINFO_FILENAME)) . '.' . $extension;
                    $filePath = 'product-images/' . $fileName;

                    // Ensure unique filename
                    $counter = 1;
                    while (Storage::disk('public')->exists($filePath)) {
                        $fileName = Str::slug(pathinfo($imageData['name'], PATHINFO_FILENAME)) . '-' . $counter . '.' . $extension;
                        $filePath = 'product-images/' . $fileName;
                        $counter++;
                    }

                    // Store the file
                    $saved = Storage::disk('public')->put($filePath, $imageResponse->body());

                    if ($saved) {
                        // Create ProductImage record
                        ProductImage::create([
                            'product_id' => $this->product->id,
                            'file_path' => $filePath,
                            'file_name' => $fileName,
                        ]);
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $failCount++;
                    // Continue with next image
                }
            }

            if ($successCount > 0) {
                $this->product->refresh();
                Flux::toast(variant: 'success', text: __('app.images_uploaded', ['count' => $successCount]));
                Flux::modal('panel.shop.product.images.wizard.modal')->hide();
                $this->dispatch('panel.shop.product.images.refresh');
            }

            if ($failCount > 0) {
                Flux::toast(variant: 'warning', text: __('app.some_images_failed', ['count' => $failCount]));
            }
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('app.error_uploading_images') . ': ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getFileExtension(string $url, string $contentType): string
    {
        // Try to get extension from URL
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return strtolower($extension);
            }
        }

        // Try to get extension from content type
        if ($contentType) {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];

            foreach ($mimeToExt as $mime => $ext) {
                if (str_contains($contentType, $mime)) {
                    return $ext;
                }
            }
        }

        // Default to jpg
        return 'jpg';
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-image-wizard');
    }
}
