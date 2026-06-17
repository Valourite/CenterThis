<?php

namespace App\Filament\Resources\PricingRules\Schemas;

use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Variant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PricingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('type')
                    ->default('configurable'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedTag)
                    ->helperText('A clear admin name for this pricing rule.'),
                Select::make('effect_direction')
                    ->label('Price effect')
                    ->options(PricingRule::effectDirections())
                    ->required()
                    ->default('surcharge')
                    ->prefixIcon(Heroicon::OutlinedArrowsUpDown)
                    ->helperText('Choose whether this rule adds to or subtracts from the rental subtotal.')
                    ->hidden(fn (Get $get): bool => $get('effect_type') === 'override_unit_rate'),
                Select::make('effect_type')
                    ->options(PricingRule::effectTypes())
                    ->required()
                    ->default('percentage')
                    ->live()
                    ->prefixIcon(Heroicon::OutlinedCalculator)
                    ->helperText('Controls how the amount below changes the rental subtotal.'),
                TextInput::make('effect_value')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('R / %')
                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                    ->helperText('Use a percentage for percentage rules, or a Rand amount for fixed and override rules.'),
                Select::make('scope')
                    ->options(PricingRule::scopes())
                    ->required()
                    ->default('global')
                    ->live()
                    ->prefixIcon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->helperText('Sets which catalogue records this rule can apply to.'),
                Select::make('scope_ids')
                    ->label('Scoped records')
                    ->options(fn (Get $get): array => self::scopeOptions((string) $get('scope')))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('scope') !== 'global')
                    ->hidden(fn (Get $get): bool => $get('scope') === 'global')
                    ->prefixIcon(Heroicon::OutlinedHashtag)
                    ->helperText('Choose the categories, products, or variants this rule is limited to.'),
                DatePicker::make('starts_at')
                    ->prefixIcon(Heroicon::OutlinedCalendarDays)
                    ->helperText('Optional first date this rule can apply.'),
                DatePicker::make('ends_at')
                    ->prefixIcon(Heroicon::OutlinedCalendarDateRange)
                    ->helperText('Optional final date this rule can apply.'),
                Select::make('apply_weekdays')
                    ->label('Applies on weekdays')
                    ->options(PricingRule::weekdays())
                    ->multiple()
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->helperText('Leave empty for any day. If selected, the booking window must include one of these days.')
                    ->columnSpanFull(),
                TextInput::make('min_days')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->helperText('Optional minimum hire days before this rule applies.'),
                TextInput::make('max_days')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedCalendar)
                    ->helperText('Optional maximum hire days for this rule.'),
                TextInput::make('min_quantity')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedSquaresPlus)
                    ->helperText('Optional minimum quantity before this rule applies.'),
                TextInput::make('max_quantity')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->prefixIcon(Heroicon::OutlinedSquares2x2)
                    ->helperText('Optional maximum quantity for this rule.'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefixIcon(Heroicon::OutlinedQueueList)
                    ->helperText('Controls rule order. Lower numbers run first.'),
                Toggle::make('active')
                    ->required()
                    ->default(true)
                    ->helperText('Inactive rules stay saved but are skipped by pricing.'),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function scopeOptions(string $scope): array
    {
        return match ($scope) {
            'category' => Category::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'product' => Product::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all(),
            'variant' => Variant::query()
                ->with('product')
                ->orderBy('sku')
                ->get()
                ->mapWithKeys(fn (Variant $variant): array => [
                    $variant->id => trim($variant->product->name.' - '.$variant->label),
                ])
                ->all(),
            default => [],
        };
    }
}
