<?php

namespace Database\Seeders;

use App\Models\Shop\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching categories from setaregan.co...');

        try {
            // Fetch the homepage to get categories
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
            ])->timeout(30)->get('https://setaregan.co/');

            if (!$response->successful()) {
                $this->command->error('Failed to fetch categories. Status: ' . $response->status());
                return;
            }

            $html = $response->body();
            $categories = $this->extractCategories($html);

            if (empty($categories)) {
                $this->command->warn('No categories found. You may need to manually add categories.');
                return;
            }

            $this->command->info('Found ' . count($categories) . ' main categories.');

            // Clear existing categories (optional - comment out if you want to keep existing)
            // Category::query()->delete();

            $sortOrder = 1;
            foreach ($categories as $categoryData) {
                $mainCategory = $this->createCategory($categoryData, null, $sortOrder++);

                // Create subcategories if they exist
                if (isset($categoryData['children']) && is_array($categoryData['children'])) {
                    $childSortOrder = 1;
                    foreach ($categoryData['children'] as $childData) {
                        $this->createCategory($childData, $mainCategory->id, $childSortOrder++);
                    }
                }
            }

            $this->command->info('Categories seeded successfully!');
        } catch (\Exception $e) {
            $this->command->error('Error fetching categories: ' . $e->getMessage());
        }
    }

    /**
     * Extract categories from HTML
     */
    private function extractCategories(string $html): array
    {
        $categories = [];
        $seen = [];

        // Pattern 1: Look for navigation menus with category links
        // Common patterns: nav, menu, sidebar, categories section
        $patterns = [
            // Navigation links with category in URL
            '/<a[^>]*href=["\']([^"\']*(?:category|cat|دسته)[^"\']*)["\'][^>]*>(.+?)<\/a>/is',
            // Links in nav/menu containers
            '/<(?:nav|ul|div)[^>]*(?:class|id)=["\'][^"\']*(?:nav|menu|category|categories)[^"\']*["\'][^>]*>(.+?)<\/(?:nav|ul|div)>/is',
            // Category items in lists
            '/<li[^>]*>\s*<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.+?)<\/a>\s*<\/li>/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $url = isset($match[1]) ? trim($match[1]) : '';
                    $name = isset($match[2]) ? trim(strip_tags($match[2])) : (isset($match[1]) ? trim(strip_tags($match[1])) : '');
                    
                    // Clean name
                    $name = preg_replace('/\s+/', ' ', $name);
                    $name = trim($name);
                    
                    if (!empty($name) && strlen($name) < 100 && strlen($name) > 2) {
                        // Skip common non-category links
                        if (preg_match('/^(خانه|صفحه اصلی|تماس|درباره|ورود|ثبت نام|سبد خرید|جستجو)$/u', $name)) {
                            continue;
                        }
                        
                        $slug = !empty($url) ? $this->extractSlugFromUrl($url) : Str::slug($name);
                        
                        if (empty($slug)) {
                            $slug = Str::slug($name);
                        }
                        
                        // Use name as key to avoid duplicates
                        $key = mb_strtolower($name);
                        if (!isset($seen[$key]) && !empty($slug)) {
                            $seen[$key] = true;
                            $categories[] = [
                                'name' => $name,
                                'slug' => $slug,
                                'url' => $url,
                            ];
                        }
                    }
                }
            }
        }

        // Pattern 2: Look for structured data (JSON-LD, microdata)
        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.+?)<\/script>/s', $html, $scriptMatches)) {
            foreach ($scriptMatches[1] as $scriptContent) {
                $jsonData = json_decode($scriptContent, true);
                if ($jsonData && is_array($jsonData)) {
                    $this->extractCategoriesFromJson($jsonData, $categories, $seen);
                }
            }
        }

        // Pattern 3: Look for category sections in HTML
        if (preg_match_all('/<div[^>]*(?:class|id)=["\'][^"\']*(?:category|categories|product-category)[^"\']*["\'][^>]*>(.+?)<\/div>/is', $html, $divMatches)) {
            foreach ($divMatches[1] as $divContent) {
                if (preg_match_all('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(.+?)<\/a>/is', $divContent, $linkMatches, PREG_SET_ORDER)) {
                    foreach ($linkMatches as $linkMatch) {
                        $url = trim($linkMatch[1]);
                        $name = trim(strip_tags($linkMatch[2]));
                        
                        if (!empty($name) && strlen($name) < 100) {
                            $key = mb_strtolower($name);
                            if (!isset($seen[$key])) {
                                $seen[$key] = true;
                                $slug = $this->extractSlugFromUrl($url) ?: Str::slug($name);
                                if (!empty($slug)) {
                                    $categories[] = [
                                        'name' => $name,
                                        'slug' => $slug,
                                        'url' => $url,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // If no categories found, use default categories
        if (empty($categories)) {
            $this->command->warn('Could not extract categories from HTML. Using default categories.');
            return $this->getDefaultCategories();
        }

        // Limit to reasonable number and remove duplicates
        $uniqueCategories = [];
        foreach ($categories as $cat) {
            $key = $cat['slug'];
            if (!isset($uniqueCategories[$key])) {
                $uniqueCategories[$key] = $cat;
            }
        }

        return array_values($uniqueCategories);
    }

    /**
     * Extract categories from JSON-LD data
     */
    private function extractCategoriesFromJson(array $data, array &$categories, array &$seen): void
    {
        // Look for BreadcrumbList which often contains category information
        if (isset($data['@type']) && $data['@type'] === 'BreadcrumbList' && isset($data['itemListElement'])) {
            foreach ($data['itemListElement'] as $item) {
                if (isset($item['name']) && isset($item['item'])) {
                    $name = trim($item['name']);
                    $url = is_string($item['item']) ? $item['item'] : ($item['item']['@id'] ?? '');
                    
                    if (!empty($name) && strlen($name) < 100) {
                        $key = mb_strtolower($name);
                        if (!isset($seen[$key])) {
                            $seen[$key] = true;
                            $slug = $this->extractSlugFromUrl($url) ?: Str::slug($name);
                            if (!empty($slug)) {
                                $categories[] = [
                                    'name' => $name,
                                    'slug' => $slug,
                                    'url' => $url,
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Recursively search in nested arrays
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->extractCategoriesFromJson($value, $categories, $seen);
            }
        }
    }

    /**
     * Extract slug from URL
     */
    private function extractSlugFromUrl(string $url): ?string
    {
        // Remove protocol and domain
        $path = parse_url($url, PHP_URL_PATH);
        
        if (!$path) {
            return null;
        }

        // Extract last part of path
        $parts = explode('/', trim($path, '/'));
        $slug = end($parts);

        // Clean slug
        $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);
        
        return !empty($slug) ? $slug : null;
    }

    /**
     * Get default categories if scraping fails
     * These are common categories for Iranian tech e-commerce sites
     */
    private function getDefaultCategories(): array
    {
        return [
            [
                'name' => 'لپ تاپ',
                'slug' => 'laptop',
                'children' => [
                    ['name' => 'لپ تاپ گیمینگ', 'slug' => 'gaming-laptop'],
                    ['name' => 'لپ تاپ اداری', 'slug' => 'office-laptop'],
                    ['name' => 'لپ تاپ دانشجویی', 'slug' => 'student-laptop'],
                    ['name' => 'لپ تاپ حرفه‌ای', 'slug' => 'professional-laptop'],
                ],
            ],
            [
                'name' => 'کامپیوتر',
                'slug' => 'desktop',
                'children' => [
                    ['name' => 'کامپیوتر آماده', 'slug' => 'prebuilt-desktop'],
                    ['name' => 'کامپیوتر اسمبل شده', 'slug' => 'assembled-desktop'],
                    ['name' => 'کامپیوتر گیمینگ', 'slug' => 'gaming-desktop'],
                ],
            ],
            [
                'name' => 'موبایل و تبلت',
                'slug' => 'mobile-tablet',
                'children' => [
                    ['name' => 'موبایل', 'slug' => 'mobile'],
                    ['name' => 'تبلت', 'slug' => 'tablet'],
                    ['name' => 'ساعت هوشمند', 'slug' => 'smartwatch'],
                ],
            ],
            [
                'name' => 'قطعات کامپیوتر',
                'slug' => 'computer-parts',
                'children' => [
                    ['name' => 'پردازنده', 'slug' => 'cpu'],
                    ['name' => 'مادربرد', 'slug' => 'motherboard'],
                    ['name' => 'کارت گرافیک', 'slug' => 'gpu'],
                    ['name' => 'رم', 'slug' => 'ram'],
                    ['name' => 'هارد دیسک', 'slug' => 'hdd'],
                    ['name' => 'اس اس دی', 'slug' => 'ssd'],
                    ['name' => 'پاور', 'slug' => 'power-supply'],
                    ['name' => 'کیس', 'slug' => 'case'],
                    ['name' => 'خنک‌کننده', 'slug' => 'cooler'],
                ],
            ],
            [
                'name' => 'لوازم جانبی',
                'slug' => 'accessories',
                'children' => [
                    ['name' => 'کیبورد', 'slug' => 'keyboard'],
                    ['name' => 'ماوس', 'slug' => 'mouse'],
                    ['name' => 'هدفون', 'slug' => 'headphone'],
                    ['name' => 'اسپیکر', 'slug' => 'speaker'],
                    ['name' => 'وب کم', 'slug' => 'webcam'],
                    ['name' => 'میکروفون', 'slug' => 'microphone'],
                ],
            ],
            [
                'name' => 'مانیتور',
                'slug' => 'monitor',
                'children' => [
                    ['name' => 'مانیتور گیمینگ', 'slug' => 'gaming-monitor'],
                    ['name' => 'مانیتور اداری', 'slug' => 'office-monitor'],
                    ['name' => 'مانیتور حرفه‌ای', 'slug' => 'professional-monitor'],
                ],
            ],
        ];
    }

    /**
     * Create a category record
     */
    private function createCategory(array $data, ?int $mainCategoryId, int $sortOrder): Category
    {
        $name = $data['name'] ?? '';
        $slug = $data['slug'] ?? Str::slug($name);
        $slugFa = str_replace(' ', '-', $name); // Simple Persian slug

        // Ensure unique slugs
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $originalSlugFa = $slugFa;
        $counterFa = 1;
        while (Category::where('slug_fa', $slugFa)->exists()) {
            $slugFa = $originalSlugFa . '-' . $counterFa;
            $counterFa++;
        }

        return Category::updateOrCreate(
            [
                'slug' => $slug,
            ],
            [
                'name' => $name,
                'slug_fa' => $slugFa,
                'main_category_id' => $mainCategoryId ?? 0,
                'sort_order' => $sortOrder,
                'icon' => $data['icon'] ?? null,
            ]
        );
    }
}
