<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\User;
use App\Models\Variant;
use App\Pricing\PricingContext;
use App\Pricing\PricingEngine;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('seeds a complete demonstration data set', function () {
    $this->seed();

    expect(Category::query()->count())->toBe(6)
        ->and(Product::query()->count())->toBe(20)
        ->and(ProductOption::query()->count())->toBe(29)
        ->and(ProductOptionValue::query()->count())->toBe(63)
        ->and(Variant::query()->count())->toBe(53)
        ->and(Customer::query()->count())->toBe(5)
        ->and(Booking::query()->count())->toBe(5)
        ->and(BookingItem::query()->count())->toBe(5)
        ->and(PricingRule::query()->count())->toBe(2)
        ->and(User::query()->where('email', 'admin@centerthis.co.za')->exists())->toBeTrue();

    Product::query()->each(function (Product $product): void {
        expect($product->images)->toHaveCount(3);
        Storage::disk('public')->assertExists($product->images);
    });

    foreach (BookingStatus::cases() as $status) {
        if ($status === BookingStatus::Cancelled) {
            continue;
        }

        expect(Booking::query()->where('status', $status->value)->exists())->toBeTrue();
    }

    $chair = Variant::query()->where('sku', 'CHAIR-FOLD-BLK')->firstOrFail();

    expect($chair->optionValues()->firstOrFail()->displayLabel())->toBe('Colour: Black');
});

it('can rerun the database seeder without duplicating its data', function () {
    $this->seed();
    $this->seed();

    expect(Category::query()->count())->toBe(6)
        ->and(Product::query()->count())->toBe(20)
        ->and(Variant::query()->count())->toBe(53)
        ->and(Customer::query()->count())->toBe(5)
        ->and(Booking::query()->count())->toBe(5)
        ->and(PricingRule::query()->count())->toBe(2)
        ->and(User::query()->count())->toBe(1);

    Storage::disk('public')->assertCount('products', 60);
});

it('applies the seeded pricing rules in priority order', function () {
    $this->seed();

    $variant = Variant::query()->where('sku', 'CHAIR-FOLD-BLK')->firstOrFail();
    $breakdown = app(PricingEngine::class)->price(PricingContext::make(
        $variant,
        1,
        '2026-06-20',
        '2026-06-23',
    ));

    expect($breakdown->rentalSubtotal())->toBe('93.50')
        ->and($breakdown->depositTotal())->toBe('50.00')
        ->and($breakdown->grandTotal())->toBe('143.50')
        ->and($breakdown->adjustments)->toBe([
            [
                'label' => 'Weekend surcharge',
                'cents' => 1000,
            ],
            [
                'label' => 'Four-day hire discount',
                'cents' => -1650,
            ],
        ]);
});

it('provides usable factories for every persisted domain model', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $option = ProductOption::factory()->for($product)->create();
    $optionValue = ProductOptionValue::factory()->for($option, 'option')->create();
    $variant = Variant::factory()->for($product)->create();
    $customer = Customer::factory()->create();
    $booking = Booking::factory()->for($customer)->create();
    $bookingItem = BookingItem::factory()
        ->for($booking)
        ->for($variant)
        ->create();
    $pricingRule = PricingRule::factory()->create();

    $variant->optionValues()->attach($optionValue);

    expect($bookingItem->booking->is($booking))->toBeTrue()
        ->and($bookingItem->variant->is($variant))->toBeTrue()
        ->and($variant->optionValues()->firstOrFail()->is($optionValue))->toBeTrue()
        ->and($pricingRule->exists)->toBeTrue();
});
