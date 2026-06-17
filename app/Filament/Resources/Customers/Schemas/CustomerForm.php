<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->helperText('The customer name used on bookings and admin records.'),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->helperText('Used to match guest checkout bookings to this customer.'),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedPhone)
                    ->helperText('Optional contact number for booking follow-up.'),
            ]);
    }
}
