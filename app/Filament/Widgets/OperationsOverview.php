<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Variant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Operations';

    protected function getStats(): array
    {
        $today = today();

        return [
            Stat::make(
                'Live bookings',
                Booking::query()->whereIn('status', BookingStatus::liveValues())->count(),
            )
                ->description('Pending, confirmed, or collected')
                ->color(BookingStatus::Collected->color()),
            Stat::make(
                'Collections next 7 days',
                Booking::query()
                    ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                    ->whereBetween('collection_date', [$today, $today->copy()->addDays(7)])
                    ->count(),
            )
                ->description('Bookings requiring preparation')
                ->color(BookingStatus::Confirmed->color()),
            Stat::make(
                'Returns due',
                Booking::query()
                    ->where('status', BookingStatus::Collected->value)
                    ->whereDate('return_date', '<=', $today)
                    ->count(),
            )
                ->description('Collected bookings due today or overdue')
                ->color(BookingStatus::Collected->color()),
            Stat::make(
                'Active stock units',
                Variant::query()->where('active', true)->sum('quantity'),
            )
                ->description('Across active variants')
                ->color(BookingStatus::Completed->color()),
        ];
    }
}
