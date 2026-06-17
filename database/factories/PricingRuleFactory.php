<?php

namespace Database\Factories;

use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingRule>
 */
class PricingRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'configurable',
            'name' => fake()->words(3, true),
            'effect_direction' => 'discount',
            'effect_type' => 'percentage',
            'effect_value' => fake()->numberBetween(5, 20),
            'config' => null,
            'scope' => 'global',
            'starts_at' => null,
            'ends_at' => null,
            'min_days' => fake()->numberBetween(3, 7),
            'max_days' => null,
            'min_quantity' => null,
            'max_quantity' => null,
            'apply_weekdays' => null,
            'priority' => fake()->numberBetween(0, 100),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
