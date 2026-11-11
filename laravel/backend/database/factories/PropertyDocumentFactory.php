<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PropertyDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDocument>
 */
class PropertyDocumentFactory extends Factory
{
    protected $model = PropertyDocument::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            // NOT NULL in the live schema.
            'content' => fake()->paragraphs(2, true),
            'type' => 'rules',
            'version' => '1.0',
            'is_active' => true,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}