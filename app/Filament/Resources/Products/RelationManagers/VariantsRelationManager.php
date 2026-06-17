<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\ProductOptionValue;
use Closure;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedTag)
                    ->helperText('Customer-facing name for this bookable variant.')
                    ->live(onBlur: true)
                    ->partiallyRenderComponentsAfterStateUpdated(['sku'])
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            return;
                        }

                        $set('sku', Str::slug($state));
                    }),

                TextInput::make('sku')
                    ->label('Stock Keeping Unit')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::OutlinedHashtag)
                    ->helperText('Optional internal stock code for this variant.'),

                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedArchiveBox)
                    ->helperText('Total units owned for this variant. Availability is calculated from bookings.'),

                TextInput::make('base_rate')
                    ->label('Base Rate')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('R')
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                    ->helperText('Daily rental rate before pricing rules are applied.'),

                TextInput::make('deposit_amount')
                    ->label('Deposit Amount')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('R')
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedReceiptRefund)
                    ->helperText('Refundable deposit charged per unit of this variant.'),

                Toggle::make('active')
                    ->required()
                    ->default(true)
                    ->helperText('Inactive variants are hidden from customers and cannot be booked.'),

                Select::make('optionValues')
                    ->label('Option Values')
                    ->multiple()
                    ->prefixIcon(Heroicon::OutlinedSquares2x2)
                    ->helperText('Select the option values that define this variant.')
                    ->relationship(
                        name: 'optionValues',
                        titleAttribute: 'value',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->with('option')
                            ->whereHas(
                                'option',
                                fn (Builder $query): Builder => $query
                                    ->where('product_id', $this->getOwnerRecord()->getKey()),
                            ),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (ProductOptionValue $record): string => $record->displayLabel(),
                    )
                    ->rules([
                        function (): Closure {
                            return function (string $attribute, mixed $value, Closure $fail): void {
                                $selectedIds = collect(is_array($value) ? $value : [])
                                    ->filter()
                                    ->map(fn (mixed $id): int => (int) $id);
                                $values = ProductOptionValue::query()
                                    ->whereKey($selectedIds)
                                    ->whereHas(
                                        'option',
                                        fn (Builder $query): Builder => $query
                                            ->where('product_id', $this->getOwnerRecord()->getKey()),
                                    )
                                    ->get();

                                if ($values->count() !== $selectedIds->count()) {
                                    $fail('Every selected option value must belong to this product.');

                                    return;
                                }

                                if ($values->groupBy('product_option_id')->contains(fn ($group): bool => $group->count() > 1)) {
                                    $fail('Select no more than one value for each product option.');
                                }
                            };
                        },
                    ])
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->searchable()
                    ->placeholder('No SKU'),
                TextColumn::make('optionValues.value')
                    ->label('Options')
                    ->badge()
                    ->separator(','),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('base_rate')
                    ->money('ZAR')
                    ->sortable(),
                TextColumn::make('deposit_amount')
                    ->money('ZAR')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
                    ->label('Manage')
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->button()
                    ->outlined()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
