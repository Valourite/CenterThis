<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('variant.label')
            ->columns([
                TextColumn::make('variant.label')
                    ->label('Variant')
                    ->searchable(),
                TextColumn::make('variant.sku')
                    ->label('SKU')
                    ->placeholder('No SKU'),
                TextColumn::make('quantity'),
                TextColumn::make('unit_rate')
                    ->money('ZAR'),
                TextColumn::make('unit_deposit')
                    ->money('ZAR'),
                TextColumn::make('line_total')
                    ->money('ZAR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
