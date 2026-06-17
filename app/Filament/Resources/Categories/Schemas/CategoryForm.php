<?php

namespace App\Filament\Resources\Categories\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedFolder)
                    ->helperText('The category name customers and admins will see.')
                    ->live(onBlur: true)
                    ->partiallyRenderComponentsAfterStateUpdated(['slug'])
                    ->afterStateUpdated(function($state, $set){
                        if(!$state) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::OutlinedLink)
                    ->helperText('The URL-friendly version of the category name.'),

                SelectTree::make('parent_id')
                    ->relationship('parent', 'name', 'parent_id')
                    ->enableBranchNode(true)
                    ->searchable()
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedFolderOpen)
                    ->helperText('Choose a parent only if this should sit under another category.'),

                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedQueueList)
                    ->helperText('Controls display order. Lower numbers show first.'),
            ]);
    }
}
