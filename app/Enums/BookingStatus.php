<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Collected = 'collected';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Released = 'released';

    /**
     * Statuses that still occupy inventory and therefore count
     * against availability for an overlapping window.
     *
     * @return array<int, self>
     */
    public static function live(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::Collected,
        ];
    }

    /**
     * Convenience for whereIn() against the string column.
     *
     * @return array<int, string>
     */
    public static function liveValues(): array
    {
        return array_map(fn(self $status) => $status->value, self::live());
    }

    public function occupiesInventory(): bool
    {
        return in_array($this, self::live(), true);
    }

    /**
     * @inheritDoc
     */
    public function getColor(): string
    {
        return $this->color();
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Collected => 'blue',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Released => 'red',
        };
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
