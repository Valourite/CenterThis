<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->prefixIcon(Heroicon::OutlinedHashtag)
                    ->helperText('The auto-generated booking reference shared with the customer.')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('customer_id')
                    ->relationship(name: 'customer', titleAttribute: 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->name} ({$record->email})")
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->helperText('The guest customer linked to this booking.')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->options(BookingStatus::class)
                    ->prefixIcon(Heroicon::OutlinedCheckCircle)
                    ->helperText('Controls whether this booking still occupies stock.')
                    ->disabled()
                    ->dehydrated(false),
                DatePicker::make('collection_date')
                    ->prefixIcon(Heroicon::OutlinedCalendarDays)
                    ->helperText('The first date the items are reserved for collection.')
                    ->disabled()
                    ->dehydrated(false),
                DatePicker::make('return_date')
                    ->prefixIcon(Heroicon::OutlinedCalendarDateRange)
                    ->helperText('The final reserved date before stock becomes available again.')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('rental_subtotal')
                    ->prefix('R')
                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                    ->helperText('Rental charge before the refundable deposit.')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('deposit_total')
                    ->prefix('R')
                    ->prefixIcon(Heroicon::OutlinedReceiptRefund)
                    ->helperText('Refundable deposit held against the booking items.')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('grand_total')
                    ->prefix('R')
                    ->prefixIcon(Heroicon::OutlinedWallet)
                    ->helperText('Rental subtotal plus refundable deposit.')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->rows(5)
                    ->helperText('Internal notes for staff handling this booking.')
                    ->columnSpanFull(),
            ]);
    }
}
