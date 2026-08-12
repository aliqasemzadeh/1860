<?php

namespace App\Livewire\Panel\Shop\Product;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Models\Shop\Unit;
use App\Support\FaterProductFetcher;
use App\Support\GigabyteProductFetcher;
use App\Support\SetareganProductFetcher;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductWizard extends Component
{
    public int $step = 1;

    // Step 1: Site selection and basic info
    public string $site_type = '';
    public string $url = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;
    public string $category_search = '';
    public string $brand_search = '';

    // Step 2: Fetched product info
    public ?array $fetched_data = null;
    public bool $is_fetching = false;
    public ?string $fetch_error = null;

    // Step 2: Editable fields
    public string $name = '';
    public ?string $description = null;
    public string $slug = '';
    public string $slug_fa = '';
    public float $weight = 0;
    public float $x_dimension = 0;
    public float $y_dimension = 0;
    public float $z_dimension = 0;
    public ?int $unit_id = null;
    public string $unit_search = '';
    public array $image_urls = [];

    public function updatedName(string $value): void
    {
        $this->slug = Str::slug($value);
        $this->slug_fa = slug_fa($value);
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'site_type' => ['required', 'string', 'in:fater,gigabyte,setaregan'],
                'url' => ['required', 'url'],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'brand_id' => ['required', 'integer', 'exists:brands,id'],
            ], [], [
                'site_type' => __('app.site_type'),
                'url' => __('app.product_wizard_url'),
                'category_id' => __('app.category'),
                'brand_id' => __('app.brand'),
            ]);

            // Validate URL matches site type
            if ($this->site_type === 'fater' && !str_contains($this->url, 'faterco.ir')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }
            if ($this->site_type === 'gigabyte' && !str_contains($this->url, 'gigabyte.com')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }
            if ($this->site_type === 'setaregan' && !str_contains($this->url, 'setaregan.co')) {
                $this->addError('url', __('app.url_does_not_match_site_type'));
                return;
            }

            $this->fetchProductInfo();
        }
    }

    public function fetchProductInfo(): void
    {
        $this->is_fetching = true;
        $this->fetch_error = null;
        $this->fetched_data = null;

        try {
            $logger = Log::channel('single');

            if ($this->site_type === 'fater') {
                $data = FaterProductFetcher::fetchProductInfo($this->url, $logger);
            } elseif ($this->site_type === 'gigabyte') {
                $data = GigabyteProductFetcher::fetchProductInfo($this->url, $logger);
            } elseif ($this->site_type === 'setaregan') {
                $data = SetareganProductFetcher::fetchProductInfo($this->url, $logger);
            } else {
                $this->fetch_error = __('app.unsupported_site');
                $this->is_fetching = false;
                return;
            }

            if (!$data) {
                $this->fetch_error = __('app.product_info_fetch_failed');
                $this->is_fetching = false;
                return;
            }

            $this->fetched_data = $data;

            // Populate editable fields
            $this->name = $data['name'] ?? '';
            $this->description = $data['description'] ?? null;
            $this->slug = $data['slug'] ?? Str::slug($this->name);
            $this->slug_fa = $data['slug_fa'] ?? slug_fa($this->name);
            $this->weight = $data['weight'] ?? 0;
            $this->x_dimension = $data['x_dimension'] ?? 0;
            $this->y_dimension = $data['y_dimension'] ?? 0;
            $this->z_dimension = $data['z_dimension'] ?? 0;
            $this->image_urls = $data['images'] ?? [];

            $this->step = 2;
            $this->is_fetching = false;
            Flux::toast(variant: 'success', text: __('app.product_info_fetched'));
        } catch (\Exception $e) {
            $this->fetch_error = __('app.product_info_fetch_failed') . ': ' . $e->getMessage();
            $this->is_fetching = false;
            Log::error('Product wizard fetch error: ' . $e->getMessage());
        }
    }

    public function back(): void
    {
        if ($this->step > 1) {
            $this->step--;
            if ($this->step === 1) {
                $this->reset(['fetched_data', 'name', 'description', 'slug', 'slug_fa', 'weight', 'x_dimension', 'y_dimension', 'z_dimension', 'image_urls', 'fetch_error']);
            }
        }
    }

    public function createProduct(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'slug_fa' => ['required', 'string', 'max:255', 'unique:products,slug_fa'],
            'weight' => ['required', 'numeric', 'min:0'],
            'x_dimension' => ['required', 'numeric', 'min:0'],
            'y_dimension' => ['required', 'numeric', 'min:0'],
            'z_dimension' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ], [], [
            'name' => __('app.name'),
            'slug' => __('app.slug'),
            'slug_fa' => __('app.slug_fa'),
            'weight' => __('app.weight'),
            'x_dimension' => __('app.x_dimension'),
            'y_dimension' => __('app.y_dimension'),
            'z_dimension' => __('app.z_dimension'),
            'category_id' => __('app.category'),
            'brand_id' => __('app.brand'),
            'unit_id' => __('app.unit'),
        ]);

        // Download and save first image as processed product file
        $filePath = null;
        $fileName = null;
        if (!empty($this->image_urls)) {
            try {
                $firstImageUrl = $this->image_urls[0];
                Log::info("Downloading first image from: {$firstImageUrl}");
                $imageResponse = Http::timeout(30)->get($firstImageUrl);

                if ($imageResponse->successful()) {
                    // Ensure directory exists
                    Storage::disk('public')->makeDirectory('products');

                    // Always store processed main image as PNG and square
                    $fileName = Str::slug($this->name) . '.png';
                    $filePath = 'products/' . $fileName;

                    $processedImage = $this->processImageToSquare($imageResponse->body());
                    if ($processedImage !== null) {
                        $saved = Storage::disk('public')->put($filePath, $processedImage);
                        if ($saved) {
                            Log::info("Successfully saved processed main image: {$filePath}");
                        } else {
                            Log::error("Failed to save processed main image: {$filePath}");
                        }
                    } else {
                        // Fallback: store original response if processing fails
                        Log::warning("Image processing failed, storing original image");
                        $saved = Storage::disk('public')->put($filePath, $imageResponse->body());
                        if ($saved) {
                            Log::info("Successfully saved original main image: {$filePath}");
                        } else {
                            Log::error("Failed to save original main image: {$filePath}");
                        }
                    }
                } else {
                    Log::warning("Failed to download first image. Status: {$imageResponse->status()}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to download product image: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Create product
        $product = Product::create([
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'slug_fa' => $this->slug_fa,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'weight' => $this->weight,
            'x_dimension' => $this->x_dimension,
            'y_dimension' => $this->y_dimension,
            'z_dimension' => $this->z_dimension,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'unit_id' => $this->unit_id,
        ]);

        // Download and save additional processed images
        if (!empty($this->image_urls) && count($this->image_urls) > 1) {
            // Ensure directory exists
            Storage::disk('public')->makeDirectory('products/images');

            $additionalImages = array_slice($this->image_urls, 1);
            Log::info("Processing " . count($additionalImages) . " additional images");

            foreach ($additionalImages as $index => $imageUrl) {
                try {
                    Log::info("Downloading additional image #{$index} from: {$imageUrl}");
                    $imageResponse = Http::timeout(30)->get($imageUrl);

                    if ($imageResponse->successful()) {
                        // Store additional images as processed square PNGs
                        $imageFileName = Str::slug($this->name) . '-' . uniqid() . '.png';
                        $imageFilePath = 'products/images/' . $imageFileName;

                        $processedImage = $this->processImageToSquare($imageResponse->body());
                        $saved = false;

                        if ($processedImage !== null) {
                            $saved = Storage::disk('public')->put($imageFilePath, $processedImage);
                            if ($saved) {
                                Log::info("Successfully saved processed additional image: {$imageFilePath}");
                            } else {
                                Log::error("Failed to save processed additional image: {$imageFilePath}");
                            }
                        } else {
                            // Fallback: store original response if processing fails
                            Log::warning("Image processing failed for additional image, storing original");
                            $saved = Storage::disk('public')->put($imageFilePath, $imageResponse->body());
                            if ($saved) {
                                Log::info("Successfully saved original additional image: {$imageFilePath}");
                            } else {
                                Log::error("Failed to save original additional image: {$imageFilePath}");
                            }
                        }

                        if ($saved) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'file_path' => $imageFilePath,
                                'file_name' => $imageFileName,
                            ]);
                            Log::info("Created ProductImage record for: {$imageFilePath}");
                        } else {
                            Log::error("Skipping ProductImage creation because file was not saved: {$imageFilePath}");
                        }
                    } else {
                        Log::warning("Failed to download additional image #{$index}. Status: {$imageResponse->status()}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to download additional product image #{$index}: " . $e->getMessage(), [
                        'url' => $imageUrl,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } else {
            Log::info("No additional images to process (total images: " . count($this->image_urls) . ")");
        }

        Flux::modal('panel.shop.product.product-wizard.modal')->close();
        $this->dispatch('panel.shop.product.index.render');
        Flux::toast(variant: 'success', text: __('app.product_created_with_price_fetcher'));

        // Reset all fields
        $this->reset();
        $this->step = 1;
    }

    #[On('panel.shop.product.category.refresh')]
    public function refreshCategory($id): void
    {
        $this->category_id = $id['id'];
        $this->category_search = '';
    }

    #[On('panel.shop.product.brand.refresh')]
    public function refreshBrand($id): void
    {
        $this->brand_id = $id['id'];
        $this->brand_search = '';
    }

    #[On('panel.shop.product.unit.refresh')]
    public function refreshUnit($id): void
    {
        $this->unit_id = $id['id'];
        $this->unit_search = '';
    }

    /**
     * Process raw image data to a square PNG with white background converted to transparent.
     */
    protected function processImageToSquare(string $imageData): ?string
    {
        try {
            $imageManager = new ImageManager(new Driver());
            $image = $imageManager->read($imageData);

            $originalWidth = $image->width();
            $originalHeight = $image->height();
            Log::debug("Processing image: {$originalWidth}x{$originalHeight}");

            // Convert white background to transparent using fill
            // Similar to: $img->fill($transparent_black, 0, 0);
            $transparentBlack = [0, 0, 0, 0];

            try {
                // Fill white areas with transparent starting from top-left corner
                // This will replace white/near-white pixels with transparent
                $image->fill($transparentBlack, 0, 0);
                Log::debug("Filled white background with transparent");
            } catch (\Throwable $e) {
                // If fill doesn't work as expected, try alternative method
                Log::debug('Fill method failed, trying alternative: ' . $e->getMessage());

                // Alternative: Create transparent canvas and place image
                // This ensures transparency is preserved
                try {
                    $width = $image->width();
                    $height = $image->height();
                    $canvas = $imageManager->create($width, $height);
                    $canvas->place($image, 'top-left', 0, 0);
                    $image = $canvas;
                } catch (\Throwable $e2) {
                    Log::debug('Alternative method also failed: ' . $e2->getMessage());
                }
            }

            // Try to trim transparent/white borders
            try {
                $image = $image->trim('top-left', null, 40);
                $trimmedWidth = $image->width();
                $trimmedHeight = $image->height();
                Log::debug("After trim: {$trimmedWidth}x{$trimmedHeight}");
            } catch (\Throwable $e) {
                Log::debug('Image trim not supported or failed: ' . $e->getMessage());
            }

            // Fit to a square while keeping aspect ratio
            $size = 1000;
            $width = $image->width();
            $height = $image->height();
            $minSide = max(1, min($width, $height));

            Log::debug("Cropping to square: {$minSide}x{$minSide} from {$width}x{$height}");

            // Calculate position to center the image on canvas
            $cropX = intval(($width - $minSide) / 2);
            $cropY = intval(($height - $minSide) / 2);
            $croppedImage = $image->crop($minSide, $minSide, $cropX, $cropY);

            // Scale down if needed
            $finalSize = $minSide;
            if ($minSide > $size) {
                $croppedImage = $croppedImage->scaleDown($size, $size);
                $finalSize = $croppedImage->width();
            }

            // Create transparent square canvas and place image centered
            $canvas = $imageManager->create($finalSize, $finalSize);

            // Place the cropped image on transparent canvas (centered)
            $offsetX = intval(($finalSize - $croppedImage->width()) / 2);
            $offsetY = intval(($finalSize - $croppedImage->height()) / 2);
            $canvas->place($croppedImage, 'top-left', $offsetX, $offsetY);

            // Encode to PNG with transparency
            $pngData = (string) $canvas->toPng();
            Log::debug("Image processed successfully, PNG size: " . strlen($pngData) . " bytes");
            return $pngData;
        } catch (\Throwable $e) {
            Log::error('Failed to process product image: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    #[Computed]
    public function categories()
    {
        $query = Category::query()
            ->where('main_category_id', '!=', 0)
            ->when($this->category_search, fn ($query) => $query->where('name', 'like', '%'.$this->category_search.'%'))
            ->with('main_category')
            ->orderBy('name')
            ->limit(20);

        $categories = $query->get(['id', 'name', 'main_category_id']);

        if ($this->category_id && !$categories->contains('id', $this->category_id)) {
            $selectedCategory = Category::with('main_category')
                ->where('id', $this->category_id)
                ->where('main_category_id', '!=', 0)
                ->first(['id', 'name', 'main_category_id']);

            if ($selectedCategory) {
                $categories->prepend($selectedCategory);
            }
        }

        return $categories;
    }

    #[Computed]
    public function brands()
    {
        $query = Brand::query()
            ->when($this->brand_search, fn ($query) => $query->where('name', 'like', '%'.$this->brand_search.'%'))
            ->orderBy('name')
            ->limit(20);

        $brands = $query->get(['id', 'name']);

        if ($this->brand_id && !$brands->contains('id', $this->brand_id)) {
            $selectedBrand = Brand::where('id', $this->brand_id)->first(['id', 'name']);

            if ($selectedBrand) {
                $brands->prepend($selectedBrand);
            }
        }

        return $brands;
    }

    #[Computed]
    public function units()
    {
        $query = Unit::query()
            ->when($this->unit_search, fn ($query) => $query->where('name', 'like', '%'.$this->unit_search.'%'))
            ->orderBy('name')
            ->limit(20);

        $units = $query->get(['id', 'name']);

        if ($this->unit_id && !$units->contains('id', $this->unit_id)) {
            $selectedUnit = Unit::where('id', $this->unit_id)->first(['id', 'name']);

            if ($selectedUnit) {
                $units->prepend($selectedUnit);
            }
        }

        return $units;
    }

    public function closeModal(): void
    {
        Flux::modal('panel.shop.product.product-wizard.modal')->close();
    }

    public function render(): View
    {
        return view('livewire.panel.shop.product.product-wizard');
    }
}
