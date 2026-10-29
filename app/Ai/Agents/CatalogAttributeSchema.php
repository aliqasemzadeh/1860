<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('gap')]
#[MaxTokens(4096)]
#[Temperature(0.4)]
#[Timeout(120)]
class CatalogAttributeSchema implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You design e-commerce product specification schemas for an Iranian online shop.

Given a product kind (example: پرینتر, لپ‌تاپ, گوشی), propose useful specification attributes buyers and Google care about.

Rules:
- Prefer Persian labels.
- Use only these types: text, textarea, number, boolean, date, select, multiselect.
- For select/multiselect, always provide realistic options with stable ASCII/slug `value` and Persian `label`.
- Use snake_case ASCII keys unique within the response (example: print_speed, print_technology).
- Group related attributes under Persian group names (example: مشخصات فنی, ویژگی‌های چاپ).
- Suggest 8–16 attributes. Avoid duplicates and fluff.
- Do not invent brand-specific marketing claims; keep attributes generic for the product kind.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'groups' => $schema->array()->items(
                $schema->object(fn (JsonSchema $group) => [
                    'name' => $group->string()->required(),
                    'attributes' => $group->array()->items(
                        $schema->object(fn (JsonSchema $attribute) => [
                            'key' => $attribute->string()->required(),
                            'label' => $attribute->string()->required(),
                            'type' => $attribute->string()->enum([
                                'text',
                                'textarea',
                                'number',
                                'boolean',
                                'date',
                                'select',
                                'multiselect',
                            ])->required(),
                            'is_required' => $attribute->boolean()->required(),
                            'options' => $attribute->array()->items(
                                $schema->object(fn (JsonSchema $option) => [
                                    'value' => $option->string()->required(),
                                    'label' => $option->string()->required(),
                                ])
                            )->nullable(),
                        ])
                    )->required(),
                ])
            )->required(),
        ];
    }
}
