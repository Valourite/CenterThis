<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Variant;
use App\Pricing\PricingContext;
use App\Pricing\PricingEngine;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class CreateBooking
{
    public function __construct(
        private AvailabilityService $availability,
        private PricingEngine $pricing,
    ) {
    }

    /**
     * @param  array{
     *     customer: array{name: string, email: string, phone?: string|null},
     *     collection_date: \DateTimeInterface|string,
     *     return_date: \DateTimeInterface|string,
     *     items: array<int, array{variant_id: int, quantity: int}>,
     *     notes?: string|null
     * }  $data
     */
    public function execute(array $data): Booking
    {
        $collectionDate = CarbonImmutable::parse($data['collection_date'])->startOfDay();
        $returnDate = CarbonImmutable::parse($data['return_date'])->startOfDay();

        if ($returnDate->isBefore($collectionDate)) {
            throw new InvalidArgumentException('The return date must be on or after the collection date.');
        }

        $itemQuantities = $this->itemQuantities($data['items']);

        $booking = DB::transaction(function () use ($data, $collectionDate, $returnDate, $itemQuantities): Booking {
            $variants = $this->lockVariants($itemQuantities->keys()->all());

            $rentalCents = 0;
            $depositCents = 0;
            $pricedItems = [];

            foreach ($itemQuantities as $variantId => $quantity) {
                /** @var Variant $variant */
                $variant = $variants->get($variantId);

                $this->availability->assertAvailable(
                    $variant,
                    $quantity,
                    $collectionDate,
                    $returnDate,
                );

                $breakdown = $this->pricing->price(PricingContext::make(
                    $variant,
                    $quantity,
                    $collectionDate,
                    $returnDate,
                ));

                $rentalCents += $breakdown->rentalCents;
                $depositCents += $breakdown->depositCents;
                $pricedItems[] = [
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_rate' => $variant->base_rate,
                    'unit_deposit' => $variant->deposit_amount,
                    'line_total' => $breakdown->rentalSubtotal(),
                ];
            }

            $customer = Customer::query()->firstOrCreate(
                ['email' => Str::lower(trim($data['customer']['email']))],
                [
                    'name' => $data['customer']['name'],
                    'phone' => $data['customer']['phone'] ?? null,
                ],
            );

            $booking = Booking::query()->create([
                'customer_id' => $customer->id,
                'status' => BookingStatus::Pending,
                'collection_date' => $collectionDate,
                'return_date' => $returnDate,
                'rental_subtotal' => $this->toDecimal($rentalCents),
                'deposit_total' => $this->toDecimal($depositCents),
                'grand_total' => $this->toDecimal($rentalCents + $depositCents),
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->items()->createMany($pricedItems);

            return $booking->load(['customer', 'items.variant.product']);
        }, attempts: 5);

        $this->sendConfirmation($booking);

        return $booking;
    }

    private function sendConfirmation(Booking $booking): void
    {
        $email = $booking->customer?->email;

        if ($email === null || $email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new BookingConfirmation($booking));
        } catch (Throwable $exception) {
            // A failed confirmation email must never roll back a committed booking.
            Log::error('Failed to send booking confirmation email.', [
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int, array{variant_id: int, quantity: int}>  $items
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function itemQuantities(array $items): \Illuminate\Support\Collection
    {
        if ($items === []) {
            throw new InvalidArgumentException('A booking must contain at least one item.');
        }

        return collect($items)
            ->reduce(function (\Illuminate\Support\Collection $quantities, array $item) {
                $variantId = (int) $item['variant_id'];
                $quantity = (int) $item['quantity'];

                if ($variantId < 1 || $quantity < 1) {
                    throw new InvalidArgumentException('Booking items require a valid variant and positive quantity.');
                }

                $quantities->put($variantId, $quantities->get($variantId, 0) + $quantity);

                return $quantities;
            }, collect())
            ->sortKeys();
    }

    /**
     * @param  array<int, int>  $variantIds
     * @return Collection<int, Variant>
     */
    private function lockVariants(array $variantIds): Collection
    {
        $variants = Variant::query()
            ->whereKey($variantIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($variants->count() !== count($variantIds)) {
            $missingIds = array_values(array_diff($variantIds, $variants->keys()->all()));

            throw (new ModelNotFoundException)->setModel(Variant::class, $missingIds);
        }

        return $variants;
    }

    private function toDecimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
