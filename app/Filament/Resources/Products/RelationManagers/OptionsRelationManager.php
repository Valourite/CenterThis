<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->helperText('Option group name, such as colour, size, or finish.'),

                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedQueueList)
                    ->helperText('Controls option order. Lower numbers show first.'),

                Repeater::make('values')
                    ->relationship()
                    ->helperText('Add the choices available for this option, such as white, black, or 2 m.')
                    ->schema([
                        TextInput::make('value')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('White, 2m etc.')
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->helperText('One selectable value for this option.'),

                        TextInput::make('position')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefixIcon(Heroicon::OutlinedQueueList)
                            ->helperText('Controls value order. Lower numbers show first.'),
                    ])
                    ->orderColumn('position')
                    ->columns(2)
                    ->addActionLabel('Add new option')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('values.value')
                    ->label('Values')
                    ->badge()
                    ->separator(','),
                TextColumn::make('position')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
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
                ]),
            ])
            ->defaultSort('position');
    }
}
