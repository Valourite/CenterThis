<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $collectionDate = CarbonImmutable::instance(fake()->dateTimeBetween('-30 days', '+60 days'));
        $rentalSubtotal = fake()->randomFloat(2, 100, 5000);
        $depositTotal = fake()->randomFloat(2, 100, 3000);

        return [
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(BookingStatus::cases()),
            'collection_date' => $collectionDate,
            'return_date' => $collectionDate->addDays(fake()->numberBetween(1, 7)),
            'rental_subtotal' => $rentalSubtotal,
            'deposit_total' => $depositTotal,
            'grand_total' => $rentalSubtotal + $depositTotal,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => BookingStatus::Pending]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => ['status' => BookingStatus::Confirmed]);
    }
}
