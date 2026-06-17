<?php

namespace App\Support;

class RentalCart
{
    private const SESSION_KEY = 'rental_cart';

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        $items = session()->get(self::SESSION_KEY.'.items', []);

        return collect(is_array($items) ? $items : [])
            ->mapWithKeys(fn (mixed $quantity, int|string $variantId): array => [
                (int) $variantId => max(1, (int) $quantity),
            ])
            ->all();
    }

    public function add(int $variantId, int $quantity): void
    {
        $items = $this->items();
        $items[$variantId] = ($items[$variantId] ?? 0) + $quantity;

        $this->putItems($items);
    }

    public function update(int $variantId, int $quantity): void
    {
        $items = $this->items();

        if (! array_key_exists($variantId, $items)) {
            return;
        }

        $items[$variantId] = $quantity;

        $this->putItems($items);
    }

    public function remove(int $variantId): void
    {
        $items = $this->items();
        unset($items[$variantId]);

        $this->putItems($items);
    }

    public function quantity(int $variantId): int
    {
        return $this->items()[$variantId] ?? 0;
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    /**
     * @return array{collection_date: ?string, return_date: ?string}
     */
    public function dates(): array
    {
        return [
            'collection_date' => session()->get(self::SESSION_KEY.'.collection_date'),
            'return_date' => session()->get(self::SESSION_KEY.'.return_date'),
        ];
    }

    public function setDates(string $collectionDate, string $returnDate): void
    {
        session()->put(self::SESSION_KEY.'.collection_date', $collectionDate);
        session()->put(self::SESSION_KEY.'.return_date', $returnDate);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @param  array<int, int>  $items
     */
    private function putItems(array $items): void
    {
        session()->put(self::SESSION_KEY.'.items', $items);
    }
}
