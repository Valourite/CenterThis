<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Models\Booking;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = BookingResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Operations desk';
    }

    public function getHeroTitle(): string
    {
        return 'Bookings and stock movement';
    }

    public function getHeroDescription(): string
    {
        return 'Track live hire windows, prepare upcoming collections, and release stock early only when an admin explicitly changes the booking status.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Availability derived', 'Inclusive windows', 'Manual status control'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        $today = today();

        return [
            $this->heroStat('Live bookings', Booking::query()->whereIn('status', BookingStatus::liveValues())->count(), 'Occupying stock windows.', BookingStatus::Collected->color()),
            $this->heroStat('Next 7 days', Booking::query()
                ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                ->whereBetween('collection_date', [$today, $today->copy()->addDays(7)])
                ->count(), 'Collections to prepare.', BookingStatus::Pending->color()),
            $this->heroStat('Returns due', Booking::query()
                ->where('status', BookingStatus::Collected->value)
                ->whereDate('return_date', '<=', $today)
                ->count(), 'Collected bookings due back.', BookingStatus::Collected->color()),
            $this->heroStat('Booked value', 'R '.number_format((float) Booking::query()->sum('grand_total'), 2), 'Rental plus deposits.', BookingStatus::Completed->color()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
