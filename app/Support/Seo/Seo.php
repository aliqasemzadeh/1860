<?php

namespace App\Support\Seo;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Seo
{
    /**
     * @param  list<array<string, mixed>>  $schemas
     * @param  array<string, string>  $meta
     */
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $keywords = null,
        public readonly ?string $canonical = null,
        public readonly ?string $image = null,
        public readonly string $type = 'website',
        public readonly bool $noindex = false,
        public readonly ?string $prev = null,
        public readonly ?string $next = null,
        public readonly array $schemas = [],
        public readonly array $meta = [],
    ) {}

    public static function shouldNoindex(?Request $request = null): bool
    {
        $request ??= request();
        $routeName = $request->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        foreach (config('seo.noindex_routes', []) as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public static function siteName(): string
    {
        return app(GeneralSettings::class)->title ?: config('app.name');
    }

    public static function documentTitle(?string $pageTitle): string
    {
        $siteName = self::siteName();
        $pageTitle = filled($pageTitle) ? trim($pageTitle) : '';

        if ($pageTitle === '' || $pageTitle === $siteName) {
            return $siteName;
        }

        return $pageTitle.' | '.$siteName;
    }

    public static function site(?string $title = null, bool $noindex = false): self
    {
        $general = app(GeneralSettings::class);
        $siteName = self::siteName();

        return new self(
            title: $title ?: $siteName,
            description: filled($general->description)
                ? $general->description
                : self::clean(__('general.seo_home_description', ['name' => $siteName])),
            keywords: filled($general->keywords)
                ? $general->keywords
                : __('general.seo_home_keywords'),
            canonical: route('home'),
            image: self::defaultImage($general),
            noindex: $noindex,
            schemas: $noindex ? [] : [SeoSchema::website(), SeoSchema::organization()],
        );
    }

    public static function home(int $page = 1, int $lastPage = 1): self
    {
        $general = app(GeneralSettings::class);
        $siteTitle = self::siteName();

        $title = $page > 1
            ? __('general.seo_page', ['page' => $page])
            : __('general.seo_home_title', ['name' => $siteTitle]);

        $description = filled($general->description)
            ? $general->description
            : __('general.seo_home_description', ['name' => $siteTitle]);

        $canonical = $page > 1
            ? route('home', ['page' => $page])
            : route('home');

        return new self(
            title: $title,
            description: self::clean($description),
            keywords: filled($general->keywords)
                ? $general->keywords
                : __('general.seo_home_keywords'),
            canonical: $canonical,
            image: self::defaultImage($general),
            prev: $page > 1
                ? ($page === 2 ? route('home') : route('home', ['page' => $page - 1]))
                : null,
            next: $page < $lastPage ? route('home', ['page' => $page + 1]) : null,
            schemas: [SeoSchema::website(), SeoSchema::organization()],
        );
    }

    public static function contact(): self
    {
        $general = app(GeneralSettings::class);

        return new self(
            title: __('general.seo_contact_title'),
            description: self::clean(__('general.contact_description')),
            keywords: filled($general->keywords)
                ? $general->keywords
                : __('general.seo_home_keywords'),
            canonical: route('contact.index'),
            image: self::defaultImage($general),
            schemas: [
                SeoSchema::contactPage(),
                SeoSchema::breadcrumbs([
                    ['name' => __('general.home'), 'url' => route('home')],
                    ['name' => __('general.contact_us'), 'url' => route('contact.index')],
                ]),
            ],
        );
    }

    /**
     * @param  iterable<int, array{file_path?: string}|object>  $images
     */
    public static function product(Product $product, ?ProductPrice $price, iterable $images): self
    {
        $title = implode(' - ', array_filter([
            $product->name,
            $product->en_name,
            $product->category?->name,
            $product->brand?->name,
        ]));

        $description = self::clean(
            strip_tags((string) ($product->description ?: implode(' ', array_filter([$product->name, $product->en_name])))),
            (int) config('seo.description_limit', 160)
        );

        $keywords = $product->tags->pluck('name')->implode(', ');
        if (empty($keywords)) {
            $keywords = implode(', ', array_filter([
                $product->name,
                $product->en_name,
                $product->category?->name,
                $product->brand?->name,
            ]));
        }

        $imageUrl = null;
        foreach ($images as $image) {
            $path = is_array($image) ? ($image['file_path'] ?? null) : ($image->file_path ?? null);
            if (filled($path)) {
                $imageUrl = url(Storage::url($path));
                break;
            }
        }

        if ($imageUrl === null && filled($product->file_path)) {
            $imageUrl = url(Storage::url($product->file_path));
        }

        $meta = [
            'product_id' => (string) $product->id,
            'product_name' => $product->name,
        ];

        if ($price) {
            // Iranian engines expect Toman. JSON-LD uses IRR (see SeoSchema::product).
            $hasDiscount = $price->sale_price && $price->sale_price < $price->price;
            $currentPrice = $hasDiscount ? $price->sale_price : $price->price;

            if ((float) $price->quantity > 0) {
                $meta['product_price'] = (string) $currentPrice;
                if ($hasDiscount) {
                    $meta['product_old_price'] = (string) $price->price;
                }
                $meta['availability'] = 'instock';
                $guarantee = $price->warranty->name ?? $price->warranty->title ?? null;
                if (filled($guarantee)) {
                    $meta['guarantee'] = (string) $guarantee;
                }
            } else {
                $meta['availability'] = 'outofstock';
            }
        }

        $crumbs = [
            ['name' => __('general.home'), 'url' => route('home')],
        ];
        if ($product->category) {
            $crumbs[] = ['name' => $product->category->name, 'url' => $product->category->url];
        }
        $crumbs[] = ['name' => $product->name, 'url' => $product->url];

        return new self(
            title: $title,
            description: $description,
            keywords: $keywords,
            canonical: $product->url,
            image: $imageUrl,
            type: 'product',
            schemas: [
                SeoSchema::breadcrumbs($crumbs),
            ],
            meta: $meta,
        );
    }

    /**
     * @param  iterable<int, Product>  $products
     */
    public static function category(
        Category $category,
        iterable $products,
        int $page = 1,
        int $lastPage = 1,
        bool $filtered = false,
    ): self {
        $general = app(GeneralSettings::class);

        $title = $category->name;
        if ($page > 1 && ! $filtered) {
            $title .= ' | '.__('general.seo_page', ['page' => $page]);
        }

        $description = self::clean(
            __('general.category_seo_description', ['name' => $category->name]),
            (int) config('seo.description_limit', 160)
        );

        $baseUrl = $category->url;
        $canonical = $filtered
            ? $baseUrl
            : ($page > 1 ? $baseUrl.'?page='.$page : $baseUrl);

        $prev = null;
        $next = null;
        if (! $filtered) {
            if ($page > 1) {
                $prev = $page === 2 ? $baseUrl : $baseUrl.'?page='.($page - 1);
            }
            if ($page < $lastPage) {
                $next = $baseUrl.'?page='.($page + 1);
            }
        }

        $pageProducts = collect($products)->take(20);

        return new self(
            title: $title,
            description: $description,
            canonical: $canonical,
            image: self::defaultImage($general),
            noindex: $filtered,
            prev: $prev,
            next: $next,
            schemas: [
                SeoSchema::collectionPage($category, $pageProducts, $description),
                SeoSchema::breadcrumbs([
                    ['name' => __('general.home'), 'url' => route('home')],
                    ['name' => $category->name, 'url' => $baseUrl],
                ]),
            ],
        );
    }

    public static function clean(?string $text, ?int $limit = null): string
    {
        $cleaned = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');

        if ($limit !== null) {
            return Str::limit($cleaned, $limit, '...');
        }

        return $cleaned;
    }

    protected static function defaultImage(GeneralSettings $general): ?string
    {
        $logo = $general->logoUrl();

        if (filled($logo)) {
            return url($logo);
        }

        $fallback = asset('images/logo.png');

        return $fallback ? url($fallback) : null;
    }
}
