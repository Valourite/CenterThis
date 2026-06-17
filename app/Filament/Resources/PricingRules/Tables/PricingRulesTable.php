<?php

namespace App\Filament\Resources\PricingRules\Tables;

use App\Models\PricingRule;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PricingRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('effect_direction')
                    ->label('Effect')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PricingRule::effectDirections()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'discount' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('effect_type')
                    ->label('Calculation')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PricingRule::effectTypes()[$state] ?? $state),
                TextColumn::make('effect_value')
                    ->label('Value')
                    ->formatStateUsing(fn (string $state): string => $state),
                TextColumn::make('scope')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PricingRule::scopes()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('scope_targets_count')
                    ->label('Targets')
                    ->counts('scopeTargets')
                    ->badge()
                    ->formatStateUsing(fn (int $state, PricingRule $record): string => $record->scope === 'global'
                        ? 'All'
                        : (string) $state),
                TextColumn::make('priority')
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->options(PricingRule::scopes()),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ])
                    ->label('Manage')
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->button()
                    ->outlined()
                    ->color('gray'),
            ])
            ->defaultSort('priority')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
