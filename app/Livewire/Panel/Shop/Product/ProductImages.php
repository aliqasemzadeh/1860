<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Services\Shop\ProductImageSeoService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductImages extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public ?int $productId = null;

    #[Validate(['images.*' => 'image|max:10240'])] // 10MB Max per image
    public array $images = [];

    #[On('panel.shop.product.images.assign-data')]
    public function assignData($id): void
    {
        $this->product = Product::with('images')->findOrFail($id);
        $this->productId = $this->product->id;
        $this->images = [];
        Flux::modal('panel.shop.product.images.modal')->show();
    }

    #[On('panel.shop.product.images.refresh')]
    public function refresh(): void
    {
        if ($this->product) {
            $this->product->refresh();
            $this->product->load('images');
        }
    }

    public function openWizard(): void
    {
        if ($this->product) {
            $this->dispatch('panel.shop.product.images.wizard.assign-data', id: $this->product->id);
        }
    }

    public function removeImage(int $index): void
    {
        if (isset($this->images[$index])) {
            $image = $this->images[$index];
            $image->delete();
            unset($this->images[$index]);
            $this->images = array_values($this->images);
        }
    }

    public function removeProductImage(int $imageId): void
    {
        if (! $this->product) {
            return;
        }

        $image = ProductImage::where('product_id', $this->product->id)
            ->where('id', $imageId)
            ->first();

        if ($image) {
            // Delete the file from storage
            if ($image->file_path && Storage::disk('public')->exists($image->file_path)) {
                Storage::disk('public')->delete($image->file_path);
            }

            $image->delete();
            $this->product->refresh();
            Flux::toast(variant: 'success', text: __('general.image_removed'));
        }
    }

    public function removeBackground(int $imageId): void
    {
        if (! $this->product) {
            return;
        }

        $image = ProductImage::where('product_id', $this->product->id)
            ->where('id', $imageId)
            ->first();

        if (! $image || ! $image->file_path) {
            Flux::toast(variant: 'danger', text: __('general.image_not_found'));
            return;
        }

        $originalPath = Storage::disk('public')->path($image->file_path);

        if (! file_exists($originalPath)) {
            Flux::toast(variant: 'danger', text: __('general.file_not_found'));
            return;
        }

        try {
            // Create output path with PNG extension
            $pathInfo = pathinfo($originalPath);
            $outputPath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.png';

            // Run removebg.js script
            $scriptPath = base_path('removebg.js');
            $result = Process::run([
                'node',
                $scriptPath,
                $originalPath,
                $outputPath,
            ]);

            if (! $result->successful()) {
                throw new \Exception($result->errorOutput() ?: $result->output());
            }

            // Verify output file was created
            if (! file_exists($outputPath)) {
                throw new \Exception(__('general.output_file_not_created'));
            }

            // Delete old file if it's not PNG
            $oldExtension = strtolower($pathInfo['extension'] ?? '');
            if ($oldExtension !== 'png' && file_exists($originalPath)) {
                unlink($originalPath);
            }

            // Update database with new file path and name
            $pathInfoDb = pathinfo($image->file_path);
            $newFilePath = $pathInfoDb['dirname'] . '/' . $pathInfoDb['filename'] . '.png';
            
            $pathInfoName = pathinfo($image->file_name);
            $newFileName = $pathInfoName['filename'] . '.png';

            $image->update([
                'file_path' => $newFilePath,
                'file_name' => $newFileName,
            ]);

            $this->product->refresh();
            Flux::toast(variant: 'success', text: __('general.background_removed'));
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('general.error_processing_image') . ': ' . $e->getMessage());
        }
    }

    public function save(): void
    {
        if (! $this->product) {
            return;
        }

        $this->validate([
            'images.*' => 'image|max:10240', // 10MB Max per image
        ], [], [
            'images.*' => __('general.image'),
        ]);

        if (empty($this->images)) {
            Flux::toast(variant: 'warning', text: __('general.no_images_selected'));
            return;
        }

        foreach ($this->images as $image) {
            $paths = app(ProductImageSeoService::class)->storeAsWebp(
                $image,
                'product-images',
                $this->product,
            );

            ProductImage::create([
                'product_id' => $this->product->id,
                'file_path' => $paths['file_path'],
                'file_name' => $paths['file_name'],
            ]);
        }

        $this->product->refresh();
        $this->images = [];
        Flux::toast(variant: 'success', text: __('general.images_added'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-images');
    }
}
