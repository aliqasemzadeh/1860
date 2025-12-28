<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use Flux\Flux;
use Illuminate\Contracts\View\View;
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
            Flux::toast(variant: 'success', text: __('app.image_removed'));
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
            'images.*' => __('app.image'),
        ]);

        if (empty($this->images)) {
            Flux::toast(variant: 'warning', text: __('app.no_images_selected'));
            return;
        }

        foreach ($this->images as $image) {
            // Store file publicly with original name
            $filePath = $image->storeAs('product-images', $image->getClientOriginalName(), 'public');
            $fileName = $image->getClientOriginalName();

            ProductImage::create([
                'product_id' => $this->product->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
            ]);
        }

        $this->product->refresh();
        $this->images = [];
        Flux::toast(variant: 'success', text: __('app.images_added'));
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-images');
    }
}
