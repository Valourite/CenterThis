<?php

use App\Actions\CreateBooking;
use App\Enums\BookingStatus;
use App\Exceptions\InsufficientAvailabilityException;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $product = Product::query()->create([
        'name' => 'Round table',
        'slug' => 'round-table',
    ]);

    $this->variant = Variant::query()->create([
        'product_id' => $product->id,
        'label' => '1.5m',
        'quantity' => 5,
        'base_rate' => '30.00',
        'deposit_amount' => '100.00',
    ]);

    $this->action = app(CreateBooking::class);
});

function bookingData(Variant $variant, int $quantity = 2): array
{
    return [
        'customer' => [
            'name' => 'Jane Customer',
            'email' => 'JANE@example.test',
            'phone' => '0821234567',
        ],
        'collection_date' => '2026-07-01',
        'return_date' => '2026-07-03',
        'items' => [
            [
                'variant_id' => $variant->id,
                'quantity' => $quantity,
            ],
        ],
        'notes' => 'Collect after 10:00.',
    ];
}

it('emails the customer a booking confirmation with their reference', function () {
    Mail::fake();

    $booking = $this->action->execute(bookingData($this->variant));

    Mail::assertSent(BookingConfirmation::class, function (BookingConfirmation $mail) use ($booking): bool {
        return $mail->booking->is($booking)
            && $mail->hasTo('jane@example.test');
    });
});

it('creates a pending booking with priced items and totals', function () {
    $booking = $this->action->execute(bookingData($this->variant));

    expect($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->reference)->toMatch('/^BK-2026-[A-Z0-9]{6}$/')
        ->and($booking->rental_subtotal)->toBe('180.00')
        ->and($booking->deposit_total)->toBe('200.00')
        ->and($booking->grand_total)->toBe('380.00')
        ->and($booking->notes)->toBe('Collect after 10:00.')
        ->and($booking->customer->email)->toBe('jane@example.test')
        ->and($booking->items)->toHaveCount(1);

    $item = $booking->items->first();

    expect($item->variant_id)->toBe($this->variant->id)
        ->and($item->quantity)->toBe(2)
        ->and($item->unit_rate)->toBe('30.00')
        ->and($item->unit_deposit)->toBe('100.00')
        ->and($item->line_total)->toBe('180.00');

    $this->assertModelExists($booking);
    $this->assertModelExists($item);
});

it('reuses a customer matched by email', function () {
    $customer = Customer::query()->create([
        'name' => 'Existing Name',
        'email' => 'jane@example.test',
        'phone' => '0115550100',
    ]);

    $booking = $this->action->execute(bookingData($this->variant, 1));

    expect($booking->customer->is($customer))->toBeTrue()
        ->and(Customer::query()->count())->toBe(1)
        ->and($customer->fresh()->name)->toBe('Existing Name');
});

it('aggregates duplicate variant lines before checking and pricing', function () {
    $data = bookingData($this->variant);
    $data['items'][] = [
        'variant_id' => $this->variant->id,
        'quantity' => 3,
    ];

    $booking = $this->action->execute($data);

    expect($booking->items)->toHaveCount(1)
        ->and($booking->items->first()->quantity)->toBe(5)
        ->and($booking->rental_subtotal)->toBe('450.00')
        ->and($booking->deposit_total)->toBe('500.00')
        ->and($booking->grand_total)->toBe('950.00');
});

it('rolls back the customer and booking when availability is insufficient', function () {
    $existingCustomer = Customer::query()->create([
        'name' => 'Existing Customer',
        'email' => 'existing@example.test',
    ]);

    $existingBooking = Booking::query()->create([
        'customer_id' => $existingCustomer->id,
        'status' => BookingStatus::Confirmed,
        'collection_date' => '2026-07-01',
        'return_date' => '2026-07-03',
    ]);

    BookingItem::query()->create([
        'booking_id' => $existingBooking->id,
        'variant_id' => $this->variant->id,
        'quantity' => 4,
        'unit_rate' => '30.00',
        'unit_deposit' => '100.00',
        'line_total' => '360.00',
    ]);

    expect(fn () => $this->action->execute(bookingData($this->variant, 2)))
        ->toThrow(InsufficientAvailabilityException::class);

    expect(Booking::query()->count())->toBe(1)
        ->and(Customer::query()->count())->toBe(1);
});

it('allows only one of two concurrent requests to reserve the remaining stock', function () {
    $defaultConnection = DB::getDefaultConnection();
    $connection = config("database.connections.{$defaultConnection}");

    config(['database.connections.booking_concurrency' => $connection]);

    $suffix = str()->random(10);
    $database = DB::connection('booking_concurrency');

    $productId = $database->table('products')->insertGetId([
        'name' => 'Concurrent product',
        'slug' => "concurrent-product-{$suffix}",
        'active' => true,
        'position' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $variantId = $database->table('variants')->insertGetId([
        'product_id' => $productId,
        'label' => 'Concurrent variant',
        'quantity' => 5,
        'base_rate' => '30.00',
        'deposit_amount' => '100.00',
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $payload = [
        'customer' => [
            'name' => 'Concurrent Customer',
            'phone' => null,
        ],
        'collection_date' => '2026-08-01',
        'return_date' => '2026-08-03',
        'items' => [
            [
                'variant_id' => $variantId,
                'quantity' => 4,
            ],
        ],
    ];

    try {
        $results = Concurrency::run([
            function () use ($payload, $suffix): string {
                $payload['customer']['email'] = "first-{$suffix}@example.test";

                try {
                    app(CreateBooking::class)->execute($payload);

                    return 'created';
                } catch (InsufficientAvailabilityException) {
                    return 'unavailable';
                }
            },
            function () use ($payload, $suffix): string {
                $payload['customer']['email'] = "second-{$suffix}@example.test";

                try {
                    app(CreateBooking::class)->execute($payload);

                    return 'created';
                } catch (InsufficientAvailabilityException) {
                    return 'unavailable';
                }
            },
        ]);

        sort($results);

        expect($results)->toBe(['created', 'unavailable'])
            ->and((int) $database->table('booking_items')->where('variant_id', $variantId)->sum('quantity'))->toBe(4);
    } finally {
        $bookingIds = $database->table('booking_items')
            ->where('variant_id', $variantId)
            ->pluck('booking_id');
        $customerIds = $database->table('bookings')
            ->whereIn('id', $bookingIds)
            ->pluck('customer_id');

        $database->table('booking_items')->whereIn('booking_id', $bookingIds)->delete();
        $database->table('bookings')->whereIn('id', $bookingIds)->delete();
        $database->table('customers')->whereIn('id', $customerIds)->delete();
        $database->table('variants')->where('id', $variantId)->delete();
        $database->table('products')->where('id', $productId)->delete();
    }
})->skip(
    fn () => DB::getDriverName() === 'sqlite',
    'SQLite does not support the row-level FOR UPDATE locking used in production.',
);
