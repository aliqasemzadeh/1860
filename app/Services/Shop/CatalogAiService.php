<?php

namespace App\Services\Shop;

use App\Ai\Agents\CatalogAttributeSchema;
use App\Ai\Agents\CatalogProductAttributes;
use App\Ai\Agents\CatalogProductContent;
use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use App\Models\Shop\AttributeOption;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CatalogAiService
{
    private const ALLOWED_TYPES = [
        'text',
        'textarea',
        'number',
        'boolean',
        'date',
        'select',
        'multiselect',
    ];

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
     * Suggest attribute schema for a product kind. Preview only — does not persist.
     *
     * @return list<array{
     *     name: string,
     *     attributes: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         is_required: bool,
     *         options: list<array{value: string, label: string}>
     *     }>
     * }>
     *
     * @throws Throwable
     */
    public function suggestCategoryAttributes(string $productKind, Category $category): array
    {
        $productKind = trim($productKind);

        if ($productKind === '') {
            throw new \InvalidArgumentException(__('general.catalog_ai_product_kind_required'));
        }

        $response = (new CatalogAttributeSchema)->prompt(
            $this->buildSchemaPrompt($productKind, $category),
            provider: 'gap',
            timeout: 120,
        );

        $groups = $this->normalizeSchemaGroups($response['groups'] ?? []);

        if ($groups === []) {
            throw new \RuntimeException(__('general.catalog_ai_empty_response'));
        }

        return $groups;
    }

    /**
     * Persist a previewed schema onto the category (create/reuse attributes and attach).
     *
     * @param  list<array{
     *     name: string,
     *     attributes: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         is_required: bool,
     *         options?: list<array{value: string, label: string}>
     *     }>
     * }>  $schema
     * @return list<int> attached attribute ids
     */
    public function applyCategoryAttributeSchema(Category $category, array $schema): array
    {
        $groups = $this->normalizeSchemaGroups($schema);

        if ($groups === []) {
            throw new \InvalidArgumentException(__('general.catalog_ai_empty_response'));
        }

        return DB::transaction(function () use ($category, $groups): array {
            $attachedIds = [];
            $sortOrder = (int) $category->attributes()->max('category_attribute.sort_order');

            foreach ($groups as $groupData) {
                $group = AttributeGroup::query()->firstOrCreate(
                    ['name' => $groupData['name']],
                    ['sort_order' => (int) AttributeGroup::query()->max('sort_order') + 1],
                );

                foreach ($groupData['attributes'] as $attributeData) {
                    $attribute = Attribute::query()->where('key', $attributeData['key'])->first();

                    if (! $attribute) {
                        $attribute = Attribute::query()->create([
                            'attribute_group_id' => $group->id,
                            'key' => $attributeData['key'],
                            'label' => $attributeData['label'],
                            'type' => $attributeData['type'],
                            'is_required' => $attributeData['is_required'],
                            'sort_order' => (int) Attribute::query()->max('sort_order') + 1,
                            'meta' => null,
                        ]);
                    } else {
                        $attribute->fill([
                            'attribute_group_id' => $attribute->attribute_group_id ?: $group->id,
                            'label' => $attributeData['label'] ?: $attribute->label,
                            'type' => in_array($attribute->type, self::ALLOWED_TYPES, true)
                                ? $attribute->type
                                : $attributeData['type'],
                            'is_required' => $attribute->is_required || $attributeData['is_required'],
                        ])->save();
                    }

                    if (in_array($attribute->type, ['select', 'multiselect'], true)) {
                        $this->syncAttributeOptions($attribute, $attributeData['options']);
                    }

                    $sortOrder++;
                    $category->attributes()->syncWithoutDetaching([
                        $attribute->id => [
                            'is_required' => $attributeData['is_required'],
                            'sort_order' => $sortOrder,
                        ],
                    ]);

                    $attachedIds[] = $attribute->id;
                }
            }

            return array_values(array_unique($attachedIds));
        });
    }

    /**
     * Generate attribute values for a product. Form preview only — does not persist.
     *
     * @return array<int, mixed>
     *
     * @throws Throwable
     */
    public function generateProductAttributeValues(Product $product, string $modelHint = ''): array
    {
        $product->loadMissing([
            'category.attributes.options',
            'brand',
        ]);

        $attributes = $product->category?->attributes ?? collect();

        if ($attributes->isEmpty()) {
            throw new \RuntimeException(__('general.catalog_ai_no_attributes'));
        }

        $response = (new CatalogProductAttributes)->prompt(
            $this->buildValuesPrompt($product, $attributes, $modelHint),
            provider: 'gap',
            timeout: 120,
        );

        return $this->normalizeAttributeValues($attributes, $response['values'] ?? []);
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

    protected function buildSchemaPrompt(string $productKind, Category $category): string
    {
        $category->loadMissing('main_category');

        $categoryPath = collect([
            $category->main_category?->name,
            $category->name,
        ])->filter()->implode(' / ');

        return <<<PROMPT
Design a specification schema for this product kind.

Product kind: {$productKind}
Shop category: {$categoryPath}

Return groups and attributes only as defined by the schema.
PROMPT;
    }

    /**
     * @param  Collection<int, Attribute>  $attributes
     */
    protected function buildValuesPrompt(Product $product, Collection $attributes, string $modelHint): string
    {
        $definitions = $attributes->map(function (Attribute $attribute): string {
            $options = $attribute->options
                ->map(fn (AttributeOption $option) => "{$option->value}={$option->label}")
                ->implode(', ');

            $line = "- id={$attribute->id}; key={$attribute->key}; label={$attribute->label}; type={$attribute->type}; required=".($attribute->is_required ? 'yes' : 'no');

            if ($options !== '') {
                $line .= "; options=[{$options}]";
            }

            return $line;
        })->implode("\n");

        $hint = trim($modelHint) !== '' ? trim($modelHint) : (string) ($product->en_name ?: $product->name);

        return <<<PROMPT
Complete specification values for this product.

Model hint: {$hint}
Product name: {$product->name}
English name: {$product->en_name}
Brand: {$product->brand?->name}
Description: {$this->plainDescription($product)}

Attributes to fill:
{$definitions}

Overwrite all values where possible. Return structured values only.
PROMPT;
    }

    protected function plainDescription(Product $product): string
    {
        return Str::limit(trim(strip_tags((string) $product->description)), 500, '...');
    }

    /**
     * @return list<array{
     *     name: string,
     *     attributes: list<array{
     *         key: string,
     *         label: string,
     *         type: string,
     *         is_required: bool,
     *         options: list<array{value: string, label: string}>
     *     }>
     * }>
     */
    protected function normalizeSchemaGroups(mixed $groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $normalized = [];
        $usedKeys = [];

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $name = trim((string) ($group['name'] ?? ''));
            if ($name === '') {
                $name = __('general.ungrouped_attributes');
            }

            $attributes = [];
            foreach (($group['attributes'] ?? []) as $attribute) {
                if (! is_array($attribute)) {
                    continue;
                }

                $key = Str::slug((string) ($attribute['key'] ?? ''), '_');
                if ($key === '') {
                    $key = Str::slug((string) ($attribute['label'] ?? ''), '_');
                }
                $key = Str::limit($key, 255, '');

                if ($key === '' || isset($usedKeys[$key])) {
                    continue;
                }

                $type = (string) ($attribute['type'] ?? 'text');
                if (! in_array($type, self::ALLOWED_TYPES, true)) {
                    $type = 'text';
                }

                $label = trim((string) ($attribute['label'] ?? ''));
                if ($label === '') {
                    $label = str_replace('_', ' ', $key);
                }

                $options = [];
                if (in_array($type, ['select', 'multiselect'], true)) {
                    foreach (($attribute['options'] ?? []) as $option) {
                        if (! is_array($option)) {
                            continue;
                        }

                        $value = Str::slug((string) ($option['value'] ?? ''), '_');
                        $optionLabel = trim((string) ($option['label'] ?? ''));

                        if ($value === '') {
                            $value = Str::slug($optionLabel, '_');
                        }

                        if ($value === '' || $optionLabel === '') {
                            continue;
                        }

                        $options[] = [
                            'value' => Str::limit($value, 255, ''),
                            'label' => Str::limit($optionLabel, 255, ''),
                        ];
                    }

                    $options = collect($options)
                        ->unique('value')
                        ->values()
                        ->all();

                    if ($options === []) {
                        // Fallback so select fields remain usable.
                        $type = 'text';
                    }
                }

                $usedKeys[$key] = true;
                $attributes[] = [
                    'key' => $key,
                    'label' => Str::limit($label, 255, ''),
                    'type' => $type,
                    'is_required' => (bool) ($attribute['is_required'] ?? false),
                    'options' => $options,
                ];
            }

            if ($attributes === []) {
                continue;
            }

            $normalized[] = [
                'name' => Str::limit($name, 255, ''),
                'attributes' => $attributes,
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{value: string, label: string}>  $options
     */
    protected function syncAttributeOptions(Attribute $attribute, array $options): void
    {
        $sort = (int) $attribute->options()->max('sort_order');

        foreach ($options as $option) {
            $existing = AttributeOption::query()
                ->where('attribute_id', $attribute->id)
                ->where('value', $option['value'])
                ->first();

            if ($existing) {
                if ($existing->label !== $option['label']) {
                    $existing->update(['label' => $option['label']]);
                }

                continue;
            }

            $sort++;
            AttributeOption::query()->create([
                'attribute_id' => $attribute->id,
                'value' => $option['value'],
                'label' => $option['label'],
                'sort_order' => $sort,
            ]);
        }
    }

    /**
     * @param  Collection<int, Attribute>  $attributes
     * @return array<int, mixed>
     */
    protected function normalizeAttributeValues(Collection $attributes, mixed $values): array
    {
        $byId = $attributes->keyBy('id');
        $result = [];

        foreach ($byId as $attributeId => $attribute) {
            $result[$attributeId] = match ($attribute->type) {
                'boolean' => false,
                'multiselect' => [],
                default => null,
            };
        }

        if (! is_array($values)) {
            return $result;
        }

        foreach ($values as $row) {
            if (! is_array($row)) {
                continue;
            }

            $attributeId = (int) ($row['attribute_id'] ?? 0);
            $attribute = $byId->get($attributeId);

            if (! $attribute) {
                continue;
            }

            $result[$attributeId] = $this->castAttributeValue($attribute, $row);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function castAttributeValue(Attribute $attribute, array $row): mixed
    {
        $allowed = $attribute->options->pluck('value')->all();

        return match ($attribute->type) {
            'number' => $this->asNumber($row['number_value'] ?? $row['value'] ?? null),
            'boolean' => (bool) ($row['boolean_value'] ?? filter_var($row['value'] ?? false, FILTER_VALIDATE_BOOLEAN)),
            'select' => $this->asSelectValue($row['value'] ?? null, $allowed),
            'multiselect' => $this->asMultiSelectValue($row['list_value'] ?? $row['value'] ?? [], $allowed),
            'date' => $this->asDateValue($row['value'] ?? null),
            default => filled($row['value'] ?? null) ? trim((string) $row['value']) : null,
        };
    }

    protected function asNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function asSelectValue(mixed $value, array $allowed): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || ! in_array($value, $allowed, true)) {
            return null;
        }

        return $value;
    }

    /**
     * @param  list<string>  $allowed
     * @return list<string>
     */
    protected function asMultiSelectValue(mixed $value, array $allowed): array
    {
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn (string $item) => $item !== '' && in_array($item, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    protected function asDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = str_replace('-', '/', $value);

        if (! preg_match('/^\d{3,4}\/\d{1,2}\/\d{1,2}$/', $normalized)) {
            return null;
        }

        return $normalized;
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

            $label = trim((string) ($attribute->label ?: $attribute->key));
            $value = trim((string) $attributeValue->display_value);

            if ($label === '' || $value === '' || $value === '-') {
                continue;
            }

            $map[$label] = $value;
        }

        return $map;
    }

    /**
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
