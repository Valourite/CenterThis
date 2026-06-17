<?php

namespace App\Filament\Resources\Bookings\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class BookingStatusActions
{
    /**
     * @return array<int, Action>
     */
    public static function make(): array
    {
        return [
            Action::make('collect')
                ->label('Mark collected')
                ->icon(Heroicon::OutlinedTruck)
                ->color(BookingStatus::Collected->color())
                ->requiresConfirmation()
                ->visible(fn (Booking $record): bool => in_array(
                    self::status($record),
                    [BookingStatus::Pending, BookingStatus::Confirmed],
                    true,
                ))
                ->action(fn (Booking $record) => self::transition(
                    $record,
                    [BookingStatus::Pending, BookingStatus::Confirmed],
                    BookingStatus::Collected,
                    ['collected_at' => now()],
                    'Booking marked as collected.',
                )),
            Action::make('return')
                ->label('Mark returned')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color(BookingStatus::Completed->color())
                ->requiresConfirmation()
                ->visible(fn (Booking $record): bool => self::status($record) === BookingStatus::Collected)
                ->action(fn (Booking $record) => self::transition(
                    $record,
                    [BookingStatus::Collected],
                    BookingStatus::Completed,
                    ['returned_at' => now()],
                    'Booking marked as returned.',
                )),
            Action::make('release')
                ->label('Release stock')
                ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                ->color(BookingStatus::Released->color())
                ->requiresConfirmation()
                ->visible(fn (Booking $record): bool => self::status($record)->occupiesInventory())
                ->action(fn (Booking $record) => self::transition(
                    $record,
                    BookingStatus::live(),
                    BookingStatus::Released,
                    [],
                    'Booking stock released.',
                )),
        ];
    }

    /**
     * @param  array<int, BookingStatus>  $from
     * @param  array<string, mixed>  $attributes
     */
    private static function transition(
        Booking $booking,
        array $from,
        BookingStatus $to,
        array $attributes,
        string $message,
    ): void {
        $updated = Booking::query()
            ->whereKey($booking)
            ->whereIn('status', array_map(
                fn (BookingStatus $status): string => $status->value,
                $from,
            ))
            ->update([
                'status' => $to->value,
                ...$attributes,
            ]);

        if ($updated === 0) {
            Notification::make()
                ->title('Booking status changed before this action completed.')
                ->danger()
                ->send();

            return;
        }

        $booking->refresh();

        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    private static function status(Booking $booking): BookingStatus
    {
        $status = $booking->getAttribute('status');

        return $status instanceof BookingStatus
            ? $status
            : BookingStatus::from((string) $status);
    }
}
