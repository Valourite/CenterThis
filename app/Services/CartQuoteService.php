<?php

namespace App\Services;

use App\Models\Variant;
use App\Pricing\PriceBreakdown;
use App\Pricing\PricingContext;
use App\Pricing\PricingEngine;

class CartQuoteService
{
    public function __construct(
        private AvailabilityService $availability,
        private PricingEngine $pricing,
    ) {
    }

    /**
     * @param  array<int, int>  $itemQuantities
     * @return array{
     *     lines: list<array{
     *         variant: Variant,
     *         quantity: int,
     *         available: int,
     *         days: int,
     *         breakdown: PriceBreakdown
     *     }>,
     *     missing_variant_ids: list<int>,
     *     all_available: bool,
     *     base_rental_cents: int,
     *     adjustments: list<array{label: string, cents: int}>,
     *     rental_cents: int,
     *     deposit_cents: int,
     *     grand_total_cents: int
     * }
     */
    public function quote(
        array $itemQuantities,
        \DateTimeInterface|string $collectionDate,
        \DateTimeInterface|string $returnDate,
    ): array {
        $variants = Variant::query()
            ->select([
                'id',
                'product_id',
                'label',
                'quantity',
                'base_rate',
                'deposit_amount',
                'active',
            ])
            ->with('product:id,category_id,name,active')
            ->whereKey(array_keys($itemQuantities))
            ->where('active', true)
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->get()
            ->keyBy('id');

        $missingVariantIds = array_values(array_diff(
            array_keys($itemQuantities),
            $variants->keys()->all(),
        ));

        $rentalCents = 0;
        $depositCents = 0;
        $baseRentalCents = 0;
        $allAvailable = $missingVariantIds === [];
        $adjustments = [];
        $lines = [];

        foreach ($itemQuantities as $variantId => $quantity) {
            /** @var Variant|null $variant */
            $variant = $variants->get($variantId);

            if ($variant === null) {
                continue;
            }

            $available = $this->availability->availableQuantity(
                $variant,
                $collectionDate,
                $returnDate,
            );
            $context = PricingContext::make(
                $variant,
                $quantity,
                $collectionDate,
                $returnDate,
            );
            $breakdown = $this->pricing->price($context);

            $allAvailable = $allAvailable && $available >= $quantity;
            $baseRentalCents += $breakdown->baseRentalCents;
            $rentalCents += $breakdown->rentalCents;
            $depositCents += $breakdown->depositCents;
            $adjustments = $this->mergeAdjustments($adjustments, $breakdown->adjustments);
            $lines[] = [
                'variant' => $variant,
                'quantity' => $quantity,
                'available' => $available,
                'days' => $context->days(),
                'breakdown' => $breakdown,
            ];
        }

        return [
            'lines' => $lines,
            'missing_variant_ids' => $missingVariantIds,
            'all_available' => $allAvailable,
            'base_rental_cents' => $baseRentalCents,
            'adjustments' => array_values($adjustments),
            'rental_cents' => $rentalCents,
            'deposit_cents' => $depositCents,
            'grand_total_cents' => $rentalCents + $depositCents,
        ];
    }

    /**
     * @param  array<string, array{label: string, cents: int}>  $current
     * @param  array<int, array{label: string, cents: int}>  $next
     * @return array<string, array{label: string, cents: int}>
     */
    private function mergeAdjustments(array $current, array $next): array
    {
        foreach ($next as $adjustment) {
            $label = $adjustment['label'];

            if (! array_key_exists($label, $current)) {
                $current[$label] = [
                    'label' => $label,
                    'cents' => 0,
                ];
            }

            $current[$label]['cents'] += $adjustment['cents'];
        }

        return $current;
    }
}
