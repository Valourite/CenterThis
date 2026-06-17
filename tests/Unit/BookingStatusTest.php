<?php

use App\Enums\BookingStatus;

it('provides labels and filament colours for each status', function (BookingStatus $status, string $label, string $color) {
    expect($status->getLabel())->toBe($label)
        ->and($status->color())->toBe($color)
        ->and($status->getColor())->toBe($color);
})->with([
    'pending' => [BookingStatus::Pending, 'Pending', 'warning'],
    'confirmed' => [BookingStatus::Confirmed, 'Confirmed', 'info'],
    'collected' => [BookingStatus::Collected, 'Collected', 'primary'],
    'completed' => [BookingStatus::Completed, 'Completed', 'success'],
    'cancelled' => [BookingStatus::Cancelled, 'Cancelled', 'danger'],
    'released' => [BookingStatus::Released, 'Released', 'secondary'],
]);

it('keeps the live statuses aligned with inventory occupancy', function () {
    expect(BookingStatus::live())->toBe([
        BookingStatus::Pending,
        BookingStatus::Confirmed,
        BookingStatus::Collected,
    ])
        ->and(BookingStatus::liveValues())->toBe(['pending', 'confirmed', 'collected'])
        ->and(BookingStatus::Pending->occupiesInventory())->toBeTrue()
        ->and(BookingStatus::Confirmed->occupiesInventory())->toBeTrue()
        ->and(BookingStatus::Collected->occupiesInventory())->toBeTrue()
        ->and(BookingStatus::Completed->occupiesInventory())->toBeFalse()
        ->and(BookingStatus::Cancelled->occupiesInventory())->toBeFalse()
        ->and(BookingStatus::Released->occupiesInventory())->toBeFalse();
});
