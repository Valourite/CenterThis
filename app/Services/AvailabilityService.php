<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\InsufficientAvailabilityException;
use App\Models\BookingItem;
use App\Models\Variant;
use Illuminate\Support\Carbon;

class AvailabilityService
{
    /**
     * Units of the given variant that are free across the whole window.
     *
     * Conservative model: any booking in a live (inventory-occupying) status
     * whose [collection_date, return_date] range touches the requested window
     * is counted in full. This never overbooks; it can occasionally refuse a
     * booking that a precise peak-usage calculation would have allowed. Swap in
     * a day-by-day sweep here later if utilisation becomes a concern.
     *
     * The window is inclusive on both ends, so a booking returning on the same
     * day a new one collects is treated as a conflict. A turnaround buffer would
     * be applied here (e.g. comparing against return_date + N days).
     */
    public function availableQuantity(
        Variant $variant,
        \DateTimeInterface|string $start,
        \DateTimeInterface|string $end,
        ?int $ignoreBookingId = null,
    ): int {
        $start = Carbon::parse($start)->toDateString();
        $end = Carbon::parse($end)->toDateString();

        $committed = (int) BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('booking_items.variant_id', $variant->id)
            ->whereIn('bookings.status', BookingStatus::liveValues())
            ->where('bookings.collection_date', '<=', $end)
            ->where('bookings.return_date', '>=', $start)
            ->when(
                $ignoreBookingId !== null,
                fn ($query) => $query->where('bookings.id', '!=', $ignoreBookingId),
            )
            ->sum('booking_items.quantity');

        return max(0, $variant->quantity - $committed);
    }

    public function isAvailable(
        Variant $variant,
        int $quantity,
        \DateTimeInterface|string $start,
        \DateTimeInterface|string $end,
        ?int $ignoreBookingId = null,
    ): bool {
        return $this->availableQuantity($variant, $start, $end, $ignoreBookingId) >= $quantity;
    }

    /**
     * Guard for the booking-confirmation transaction. Call this after locking
     * the relevant rows so the window is re-checked under the lock and can't be
     * oversold by a concurrent request.
     *
     * @throws InsufficientAvailabilityException
     */
    public function assertAvailable(
        Variant $variant,
        int $quantity,
        \DateTimeInterface|string $start,
        \DateTimeInterface|string $end,
        ?int $ignoreBookingId = null,
    ): void {
        $available = $this->availableQuantity($variant, $start, $end, $ignoreBookingId);

        if ($available < $quantity) {
            throw new InsufficientAvailabilityException($variant, $quantity, $available);
        }
    }
}