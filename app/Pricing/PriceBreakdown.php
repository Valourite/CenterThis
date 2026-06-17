<?php

namespace App\Pricing;

class PriceBreakdown
{
    /**
     * @param array<int, array{label: string, cents: int}> $adjustments
     */
    public function __construct(
        public readonly int $baseRentalCents,
        public readonly int $rentalCents,
        public readonly int $depositCents,
        public readonly array $adjustments = [],
    ) {
    }

    public function adjustmentTotalCents(): int
    {
        return array_sum(array_column($this->adjustments, 'cents'));
    }

    public function grandTotalCents(): int
    {
        return $this->rentalCents + $this->depositCents;
    }

    public function baseRentalSubtotal(): string
    {
        return $this->toDecimal($this->baseRentalCents);
    }

    public function rentalSubtotal(): string
    {
        return $this->toDecimal($this->rentalCents);
    }

    public function depositTotal(): string
    {
        return $this->toDecimal($this->depositCents);
    }

    public function grandTotal(): string
    {
        return $this->toDecimal($this->grandTotalCents());
    }

    private function toDecimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
