<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        PricingRule::query()->updateOrCreate(
            ['name' => 'Weekend surcharge'],
            [
                'type' => 'configurable',
                'effect_direction' => 'surcharge',
                'effect_type' => 'percentage',
                'effect_value' => 10,
                'config' => null,
                'scope' => 'global',
                'starts_at' => null,
                'ends_at' => null,
                'min_days' => null,
                'max_days' => null,
                'min_quantity' => null,
                'max_quantity' => null,
                'apply_weekdays' => [0, 6],
                'priority' => 10,
                'active' => true,
            ],
        );

        PricingRule::query()->updateOrCreate(
            ['name' => 'Four-day hire discount'],
            [
                'type' => 'configurable',
                'effect_direction' => 'discount',
                'effect_type' => 'percentage',
                'effect_value' => 15,
                'config' => null,
                'scope' => 'global',
                'starts_at' => null,
                'ends_at' => null,
                'min_days' => 4,
                'max_days' => null,
                'min_quantity' => null,
                'max_quantity' => null,
                'apply_weekdays' => null,
                'priority' => 20,
                'active' => true,
            ],
        );
    }
}
