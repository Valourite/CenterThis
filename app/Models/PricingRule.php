<?php

namespace App\Models;

use Database\Factories\PricingRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, mixed>|null $config
 * @property array<int, int>|null $apply_weekdays
 */
class PricingRule extends Model
{
    /** @use HasFactory<PricingRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'effect_direction',
        'effect_type',
        'effect_value',
        'config',
        'scope',
        'scope_id',
        'starts_at',
        'ends_at',
        'min_days',
        'max_days',
        'min_quantity',
        'max_quantity',
        'apply_weekdays',
        'priority',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'effect_value' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'min_days' => 'integer',
            'max_days' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'apply_weekdays' => 'array',
            'priority' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function effectDirections(): array
    {
        return [
            'surcharge' => 'Add to price',
            'discount' => 'Subtract from price',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function effectTypes(): array
    {
        return [
            'percentage' => 'Percentage of rental subtotal',
            'fixed_booking' => 'Fixed amount per booking line',
            'fixed_item' => 'Fixed amount per item',
            'fixed_day' => 'Fixed amount per item per day',
            'override_unit_rate' => 'Set unit rental rate',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function scopes(): array
    {
        return [
            'global' => 'Global',
            'category' => 'Category',
            'product' => 'Product',
            'variant' => 'Variant',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function weekdays(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }
}
