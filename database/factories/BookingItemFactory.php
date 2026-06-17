<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingItem>
 */
class BookingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitRate = fake()->randomFloat(2, 20, 750);

        return [
            'booking_id' => Booking::factory(),
            'variant_id' => Variant::factory(),
            'quantity' => $quantity,
            'unit_rate' => $unitRate,
            'unit_deposit' => fake()->randomFloat(2, 50, 1500),
            'line_total' => $unitRate * $quantity,
        ];
    }
}
