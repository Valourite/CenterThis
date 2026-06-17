<?php

namespace App\Models;

use Database\Factories\PricingRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @return HasMany<PricingRuleScope, $this>
     */
    public function scopeTargets(): HasMany
    {
        return $this->hasMany(PricingRuleScope::class);
    }

    /**
     * Scope record ids this rule is limited to (empty for global rules).
     *
     * @return array<int, int>
     */
    public function scopeTargetIds(): array
    {
        return $this->scopeTargets
            ->pluck('scope_id')
            ->map(fn (int $id): int => (int) $id)
            ->all();
    }

    /**
     * Replace this rule's scope targets with the given record ids.
     *
     * @param  array<int, int|string>  $ids
     */
    public function syncScopeTargets(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $this->scopeTargets()->delete();

        if ($ids !== []) {
            $this->scopeTargets()->createMany(
                array_map(static fn (int $id): array => ['scope_id' => $id], $ids),
            );
        }

        $this->setRelation('scopeTargets', $this->scopeTargets()->get());
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
