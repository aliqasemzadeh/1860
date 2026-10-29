<?php

namespace App\Services\Shop;

use App\Ai\Agents\CatalogProductContent;
use App\Models\Shop\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class CatalogAiService
{
    /**
     * Generate SEO-friendly catalog fields for a product draft.
     * Does not persist anything — caller should preview then save manually.
     *
     * @param  array{
     *     name?: string|null,
     *     en_name?: string|null,
     *     description?: string|null,
     *     category?: string|null,
     *     brand?: string|null,
     *     tags?: list<string>|null,
     *     attributes?: array<string, string>|null
     * }  $overrides
     * @return array{en_name: string, description: string, tags: list<string>}
     *
     * @throws Throwable
     */
    public function generateProductContent(Product $product, array $overrides = []): array
    {
        $context = $this->buildContext($product, $overrides);

        $response = (new CatalogProductContent)->prompt(
            $this->buildPrompt($context),
            provider: 'gap',
            timeout: 120,
        );

        $enName = trim((string) ($response['en_name'] ?? ''));
        $description = trim((string) ($response['description'] ?? ''));
        $tags = $this->normalizeTags($response['tags'] ?? []);

        if ($enName === '' || $description === '') {
            throw new \RuntimeException(__('general.catalog_ai_empty_response'));
        }

        return [
            'en_name' => Str::limit($enName, 255, ''),
            'description' => $description,
            'tags' => $tags,
        ];
    }

    /**
     * @param  array{
     *     name?: string|null,
     *     en_name?: string|null,
     *     description?: string|null,
     *     category?: string|null,
     *     brand?: string|null,
     *     tags?: list<string>|null,
     *     attributes?: array<string, string>|null
     * }  $overrides
     * @return array{
     *     name: string,
     *     en_name: string,
     *     description: string,
     *     category: string,
     *     brand: string,
     *     tags: list<string>,
     *     attributes: array<string, string>
     * }
     */
    protected function buildContext(Product $product, array $overrides): array
    {
        $product->loadMissing([
            'category.main_category',
            'brand',
            'tags',
            'attributeValues.attribute',
        ]);

        $category = $overrides['category']
            ?? collect([
                $product->category?->main_category?->name,
                $product->category?->name,
            ])->filter()->implode(' / ');

        $existingTags = $overrides['tags']
            ?? $product->tags->pluck('name')->all();

        return [
            'name' => trim((string) ($overrides['name'] ?? $product->name)),
            'en_name' => trim((string) ($overrides['en_name'] ?? $product->en_name)),
            'description' => trim(strip_tags((string) ($overrides['description'] ?? $product->description))),
            'category' => trim((string) $category),
            'brand' => trim((string) ($overrides['brand'] ?? $product->brand?->name)),
            'tags' => $this->normalizeTags($existingTags),
            'attributes' => $overrides['attributes'] ?? $this->attributeMap($product),
        ];
    }

    /**
     * @param  array{
     *     name: string,
     *     en_name: string,
     *     description: string,
     *     category: string,
     *     brand: string,
     *     tags: list<string>,
     *     attributes: array<string, string>
     * }  $context
     */
    protected function buildPrompt(array $context): string
    {
        $attributes = Collection::make($context['attributes'])
            ->map(fn (string $value, string $key) => "- {$key}: {$value}")
            ->implode("\n");

        $tags = implode('، ', $context['tags']);

        return <<<PROMPT
Generate improved catalog content for this product draft.

Current name (Persian): {$context['name']}
Current English name: {$context['en_name']}
Brand: {$context['brand']}
Category: {$context['category']}
Existing tags: {$tags}
Existing description (plain text): {$context['description']}

Known attributes/specs:
{$attributes}

Return structured fields only as defined by the schema.
PROMPT;
    }

    /**
     * @return array<string, string>
     */
    protected function attributeMap(Product $product): array
    {
        $map = [];

        foreach ($product->attributeValues as $attributeValue) {
            $attribute = $attributeValue->attribute;
            if (! $attribute) {
                continue;
            }

            $label = trim((string) $attribute->name);
            $value = trim((string) $attributeValue->display_value);

            if ($label === '' || $value === '' || $value === '-') {
                continue;
            }

            $map[$label] = $value;
        }

        return $map;
    }

    /**
     * @param  mixed  $tags
     * @return list<string>
     */
    protected function normalizeTags(mixed $tags): array
    {
        if (! is_array($tags) && ! $tags instanceof Collection) {
            return [];
        }

        return Collection::make($tags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->take(12)
            ->values()
            ->all();
    }
}
