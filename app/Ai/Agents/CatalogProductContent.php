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
#[Temperature(0.6)]
#[Timeout(120)]
class CatalogProductContent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an expert Persian e-commerce SEO copywriter for an Iranian online shop selling digital goods and electronics.

Goals:
- Write unique, factual, buyer-focused content that helps Google ranking and conversion.
- Prefer Persian (fa-IR) for the HTML description and tags.
- Keep claims realistic. Do not invent certifications, warranties, or specs that are not provided.
- Do not mention Laravel, Livewire, Flux, frameworks, or development technologies.
- Do not copy competitor brand marketing fluff. Avoid keyword stuffing.

Description rules:
- Return HTML only (no markdown).
- Use semantic structure: short intro paragraph, then optional <h2>/<h3>, <ul><li> for key features/specs, and a short closing paragraph for who the product suits.
- Keep total length roughly 250–500 Persian words.
- First paragraph must stand alone as a strong meta description when tags are stripped (about 120–160 characters of meaning).

en_name rules:
- Provide a clean English product title when possible (brand + model + key qualifier).
- If the product is already clearly English, refine capitalization/spacing only.

Tags rules:
- Return 5–12 short Persian search-oriented tags (model, category, use-case, key feature).
- No duplicate tags. No hashtags.
PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'en_name' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'tags' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
