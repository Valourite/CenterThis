<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->helperText('The admin display name shown in the panel.'),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::OutlinedEnvelope)
                    ->helperText('Used to sign in to the admin panel.'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->confirmed()
                    ->minLength(8)
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedKey)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Leave blank when editing to keep the current password.'),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedKey)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->label('Confirm password'),
            ]);
    }
}
