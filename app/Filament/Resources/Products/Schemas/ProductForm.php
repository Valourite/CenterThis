<?php

namespace App\Filament\Resources\Products\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SelectTree::make('category_id')
                    ->relationship('category', 'name', 'parent_id')
                    ->enableBranchNode(false)
                    ->searchable()
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedFolder)
                    ->helperText('Places this product inside a catalogue category.'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedShoppingBag)
                    ->helperText('The product name shown in admin and the hire collection.')
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
                    ->helperText('The URL-friendly version used for the product page.'),
                Textarea::make('description')
                    ->rows(5)
                    ->helperText('Briefly explain what the item is used for and any hire notes.')
                    ->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Product images')
                    ->helperText('The first image is used as the cover. Drag images to change their order.')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '4:3',
                        '1:1',
                        '16:9',
                    ])
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxFiles(10)
                    ->maxSize(5120)
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->required()
                    ->default(true)
                    ->helperText('Inactive products are hidden from the public hire collection.'),
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
