<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function storefrontVariant(array $attributes = []): Variant
{
    $product = Product::factory()->create([
        'name' => 'Clear Tiffany Chair',
    ]);

    return Variant::factory()->for($product)->create(array_merge([
        'label' => 'Clear',
        'quantity' => 10,
        'base_rate' => '100.00',
        'deposit_amount' => '50.00',
    ], $attributes));
}

it('adds active variants to the session hire basket', function () {
    $variant = storefrontVariant();

    $this->from(route('catalogue'))
        ->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertRedirect(route('catalogue'))
        ->assertSessionHas('rental_cart.items.'.$variant->id, 2)
        ->assertSessionHas('cart_status');

    $this->from(route('catalogue'))
        ->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 3,
        ])
        ->assertRedirect(route('catalogue'))
        ->assertSessionHas('rental_cart.items.'.$variant->id, 5);
});

it('rejects cart quantities above the variant collection quantity', function () {
    $variant = storefrontVariant(['quantity' => 3]);

    $this->from(route('catalogue'))
        ->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 4,
        ])
        ->assertRedirect(route('catalogue'))
        ->assertSessionHasErrors('quantity');

    expect(session('rental_cart.items', []))->toBeEmpty();
});

it('shows live availability and pricing before continuing to checkout', function () {
    $variant = storefrontVariant();
    $collectionDate = now()->addMonth()->toDateString();
    $returnDate = now()->addMonth()->addDay()->toDateString();

    $this->withSession([
        'rental_cart' => [
            'items' => [$variant->id => 2],
        ],
    ]);

    Livewire::test('rental-cart')
        ->set('collectionDate', $collectionDate)
        ->set('returnDate', $returnDate)
        ->assertSee('Available for your dates')
        ->assertSee('R400.00')
        ->assertSee('R100.00')
        ->assertSee('R500.00')
        ->call('proceedToCheckout')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('checkout');

    expect(session('rental_cart.collection_date'))->toBe($collectionDate)
        ->and(session('rental_cart.return_date'))->toBe($returnDate);
});

it('shows applied pricing rules in the cart quote', function () {
    $variant = storefrontVariant([
        'base_rate' => '25.00',
        'deposit_amount' => '50.00',
    ]);
    $collectionDate = now()->addMonth()->toDateString();
    $returnDate = now()->addMonth()->addDay()->toDateString();

    PricingRule::factory()->create([
        'name' => 'Daily handling surcharge',
        'effect_direction' => 'surcharge',
        'effect_type' => 'fixed_day',
        'effect_value' => '120.00',
        'scope' => 'global',
        'min_days' => null,
        'priority' => 10,
        'active' => true,
    ]);

    $this->withSession([
        'rental_cart' => [
            'items' => [$variant->id => 3],
        ],
    ]);

    Livewire::test('rental-cart')
        ->set('collectionDate', $collectionDate)
        ->set('returnDate', $returnDate)
        ->assertSee('Base hire')
        ->assertSee('Daily handling surcharge')
        ->assertSee('+R720.00')
        ->assertSee('Hire total')
        ->assertSee('R870.00')
        ->assertSee('Refundable deposit')
        ->assertSee('R150.00')
        ->assertSee('R1,020.00');
});

it('prevents checkout when the requested quantity is unavailable', function () {
    $variant = storefrontVariant(['quantity' => 10]);
    $collectionDate = now()->addMonth()->toDateString();
    $returnDate = now()->addMonth()->addDay()->toDateString();
    $customer = Customer::factory()->create();
    $booking = Booking::factory()->for($customer)->create([
        'status' => BookingStatus::Confirmed,
        'collection_date' => $collectionDate,
        'return_date' => $returnDate,
    ]);

    BookingItem::factory()->for($booking)->for($variant)->create([
        'quantity' => 9,
    ]);

    $this->withSession([
        'rental_cart' => [
            'items' => [$variant->id => 2],
        ],
    ]);

    Livewire::test('rental-cart')
        ->set('collectionDate', $collectionDate)
        ->set('returnDate', $returnDate)
        ->assertSee('Only 1 available for your dates')
        ->call('proceedToCheckout')
        ->assertHasErrors('cart')
        ->assertNoRedirect();
});

it('creates a pending booking from guest checkout and clears the basket', function () {
    $variant = storefrontVariant();
    $collectionDate = now()->addMonth()->toDateString();
    $returnDate = now()->addMonth()->addDay()->toDateString();

    $this->withSession([
        'rental_cart' => [
            'items' => [$variant->id => 2],
            'collection_date' => $collectionDate,
            'return_date' => $returnDate,
        ],
    ]);

    Livewire::test('guest-checkout')
        ->set('name', 'Lerato Mokoena')
        ->set('email', 'lerato@example.com')
        ->set('phone', '0825550101')
        ->set('notes', 'Collection after 10:00, please.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirectContains('/booking-confirmation/');

    $booking = Booking::query()->with(['customer', 'items'])->sole();

    expect($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->customer->email)->toBe('lerato@example.com')
        ->and($booking->items)->toHaveCount(1)
        ->and($booking->items->first()->variant_id)->toBe($variant->id)
        ->and($booking->items->first()->quantity)->toBe(2)
        ->and($booking->rental_subtotal)->toBe('400.00')
        ->and($booking->deposit_total)->toBe('100.00')
        ->and($booking->grand_total)->toBe('500.00')
        ->and(session()->has('rental_cart'))->toBeFalse();
});

it('requires a valid signature to view a booking confirmation', function () {
    $booking = Booking::factory()
        ->for(Customer::factory())
        ->create();

    $this->get(route('booking.confirmation', $booking))
        ->assertForbidden();

    $signedUrl = URL::temporarySignedRoute(
        'booking.confirmation',
        now()->addHour(),
        ['booking' => $booking->reference],
    );

    $this->get($signedUrl)
        ->assertSuccessful()
        ->assertSee($booking->reference)
        ->assertSee('No online payment has been taken');
});
