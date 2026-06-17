<?php

namespace App\Pricing;

use App\Models\Variant;
use Carbon\CarbonImmutable;

class PricingContext
{
    public function __construct(
        public readonly Variant $variant,
        public readonly int $quantity,
        public readonly CarbonImmutable $collectionDate,
        public readonly CarbonImmutable $returnDate,
    ) {
    }

    public static function make(
        Variant $variant,
        int $quantity,
        \DateTimeInterface|string $collectionDate,
        \DateTimeInterface|string $returnDate,
    ): self {
        return new self(
            $variant,
            $quantity,
            CarbonImmutable::parse($collectionDate)->startOfDay(),
            CarbonImmutable::parse($returnDate)->startOfDay(),
        );
    }

    /**
     * Rental days for the window. Inclusive of both ends by default
     * (see config/pricing.php), with a floor of one day.
     */
    public function days(): int
    {
        $span = (int) $this->collectionDate->diffInDays($this->returnDate);

        if (config('pricing.inclusive_days', true)) {
            $span += 1;
        }

        return max(1, $span);
    }
}