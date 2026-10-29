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
#[Temperature(0.3)]
#[Timeout(120)]
class CatalogProductAttributes implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You complete product specification values for an Iranian e-commerce catalog.

Rules:
- Fill every provided attribute when possible for the given model/product.
- For select/multiselect, choose ONLY from the provided option values (use the option `value`, not the label).
- For number attributes return numeric values only.
- For boolean attributes return true/false.
- For date attributes return Jalali format Y/m/d when known, otherwise null.
- If a value is unknown, return null (or [] for multiselect).
- Do not invent certifications or impossible specs.
- Prefer factual values for the given model hint.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'values' => $schema->array()->items(
                $schema->object(fn (JsonSchema $item) => [
                    'attribute_id' => $item->integer()->required(),
                    'value' => $item->string()->nullable(),
                    'number_value' => $item->number()->nullable(),
                    'boolean_value' => $item->boolean()->nullable(),
                    'list_value' => $item->array()->items($schema->string())->nullable(),
                ])
            )->required(),
        ];
    }
}
