<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Variant>
 */
class VariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = rtrim(fake()->sentence(2), '.');

        return [
            'product_id' => Product::factory(),
            'sku' => Str::upper(fake()->unique()->bothify('EQ-####-??')),
            'label' => Str::title($label),
            'quantity' => fake()->numberBetween(5, 100),
            'base_rate' => fake()->randomFloat(2, 20, 750),
            'deposit_amount' => fake()->randomFloat(2, 50, 1500),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
