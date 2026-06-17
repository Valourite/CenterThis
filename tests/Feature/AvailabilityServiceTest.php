<?php

use App\Enums\BookingStatus;
use App\Exceptions\InsufficientAvailabilityException;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Variant;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new AvailabilityService();

    $product = Product::create([
        'name' => 'Folding chair',
        'slug' => 'folding-chair',
    ]);

    $this->variant = Variant::create([
        'product_id' => $product->id,
        'label' => 'Black, with cushion',
        'quantity' => 100,
        'base_rate' => 25.00,
        'deposit_amount' => 50.00,
    ]);
});

function bookVariant(
    Variant $variant,
    int $quantity,
    string $collection,
    string $return,
    BookingStatus $status = BookingStatus::Confirmed,
): Booking {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'c'.uniqid().'@example.test',
    ]);

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'status' => $status,
        'collection_date' => $collection,
        'return_date' => $return,
    ]);

    BookingItem::create([
        'booking_id' => $booking->id,
        'variant_id' => $variant->id,
        'quantity' => $quantity,
        'unit_rate' => 25.00,
    ]);

    return $booking;
}

it('reports full quantity when there are no bookings', function () {
    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05'))->toBe(100);
});

it('subtracts overlapping live bookings from availability', function () {
    bookVariant($this->variant, 60, '2026-07-02', '2026-07-04');

    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05'))->toBe(40);
});

it('ignores bookings whose window does not overlap', function () {
    bookVariant($this->variant, 60, '2026-07-10', '2026-07-12');

    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05'))->toBe(100);
});

it('does not count bookings in terminal statuses', function () {
    bookVariant($this->variant, 60, '2026-07-02', '2026-07-04', BookingStatus::Completed);
    bookVariant($this->variant, 20, '2026-07-02', '2026-07-04', BookingStatus::Cancelled);
    bookVariant($this->variant, 10, '2026-07-02', '2026-07-04', BookingStatus::Released);

    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05'))->toBe(100);
});

it('treats a shared boundary day as an overlap', function () {
    bookVariant($this->variant, 70, '2026-06-28', '2026-07-01');

    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05'))->toBe(30);
});

it('excludes the ignored booking when recalculating', function () {
    $booking = bookVariant($this->variant, 60, '2026-07-02', '2026-07-04');

    expect($this->service->availableQuantity($this->variant, '2026-07-01', '2026-07-05', $booking->id))->toBe(100);
});

it('reflects remaining capacity in isAvailable', function () {
    bookVariant($this->variant, 80, '2026-07-02', '2026-07-04');

    expect($this->service->isAvailable($this->variant, 20, '2026-07-01', '2026-07-05'))->toBeTrue();
    expect($this->service->isAvailable($this->variant, 21, '2026-07-01', '2026-07-05'))->toBeFalse();
});

it('throws when assertAvailable cannot satisfy the request', function () {
    bookVariant($this->variant, 95, '2026-07-02', '2026-07-04');

    $this->service->assertAvailable($this->variant, 10, '2026-07-01', '2026-07-05');
})->throws(InsufficientAvailabilityException::class);