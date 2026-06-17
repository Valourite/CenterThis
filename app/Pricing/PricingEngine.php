<?php

namespace App\Pricing;

use App\Models\PricingRule as PricingRuleModel;
use App\Models\Product;
use Carbon\CarbonImmutable;

class PricingEngine
{
    public function price(PricingContext $context): PriceBreakdown
    {
        $baseRentalCents = $this->baseRentalCents($context);
        $rentalCents = $baseRentalCents;
        $depositCents = $this->depositCents($context);
        $adjustments = [];

        foreach ($this->activeRules() as $rule) {
            if (! $this->ruleApplies($rule, $context)) {
                continue;
            }

            $before = $rentalCents;
            $rentalCents = max(0, $this->applyRule($rule, $context, $rentalCents));

            $adjustments[] = [
                'label' => $rule->name,
                'cents' => $rentalCents - $before,
            ];
        }

        return new PriceBreakdown(
            baseRentalCents: $baseRentalCents,
            rentalCents: $rentalCents,
            depositCents: $depositCents,
            adjustments: $adjustments,
        );
    }

    private function baseRentalCents(PricingContext $context): int
    {
        return $this->toCents($context->variant->base_rate)
            * $context->quantity
            * $context->days();
    }

    private function depositCents(PricingContext $context): int
    {
        return $this->toCents($context->variant->deposit_amount)
            * $context->quantity;
    }

    /**
     * @return iterable<PricingRuleModel>
     */
    private function activeRules(): iterable
    {
        return PricingRuleModel::query()
            ->with('scopeTargets')
            ->where('active', true)
            ->orderBy('priority')
            ->cursor();
    }

    private function ruleApplies(PricingRuleModel $rule, PricingContext $context): bool
    {
        return $this->scopeMatches($rule, $context)
            && $this->dateRangeMatches($rule, $context)
            && $this->weekdaysMatch($rule, $context)
            && $this->daysMatch($rule, $context)
            && $this->quantityMatches($rule, $context);
    }

    private function applyRule(PricingRuleModel $rule, PricingContext $context, int $rentalCents): int
    {
        $effectCents = match ($rule->effect_type) {
            'percentage' => (int) round($rentalCents * (((float) $rule->effect_value) / 100)),
            'fixed_booking' => $this->toCents($rule->effect_value),
            'fixed_item' => $this->toCents($rule->effect_value) * $context->quantity,
            'fixed_day' => $this->toCents($rule->effect_value) * $context->quantity * $context->days(),
            'override_unit_rate' => ($this->toCents($rule->effect_value) * $context->quantity * $context->days()) - $rentalCents,
            default => 0,
        };

        if ($rule->effect_type === 'override_unit_rate') {
            return $rentalCents + $effectCents;
        }

        return match ($rule->effect_direction) {
            'discount' => $rentalCents - $effectCents,
            default => $rentalCents + $effectCents,
        };
    }

    private function scopeMatches(PricingRuleModel $rule, PricingContext $context): bool
    {
        if ($rule->scope === 'global') {
            return true;
        }

        $product = $context->variant->product;

        $targetId = match ($rule->scope) {
            'variant' => $context->variant->id,
            'product' => $context->variant->product_id,
            'category' => $product instanceof Product ? $product->category_id : null,
            default => null,
        };

        if ($targetId === null) {
            return false;
        }

        return in_array((int) $targetId, $rule->scopeTargetIds(), true);
    }

    private function dateRangeMatches(PricingRuleModel $rule, PricingContext $context): bool
    {
        if ($rule->starts_at !== null && $context->returnDate->lt(CarbonImmutable::parse($rule->starts_at))) {
            return false;
        }

        if ($rule->ends_at !== null && $context->collectionDate->gt(CarbonImmutable::parse($rule->ends_at))) {
            return false;
        }

        return true;
    }

    private function weekdaysMatch(PricingRuleModel $rule, PricingContext $context): bool
    {
        $weekdays = $rule->apply_weekdays ?? [];

        if ($weekdays === []) {
            return true;
        }

        $weekdays = array_map('intval', $weekdays);

        foreach ($this->datesInWindow($context) as $date) {
            if (in_array($date->dayOfWeek, $weekdays, true)) {
                return true;
            }
        }

        return false;
    }

    private function daysMatch(PricingRuleModel $rule, PricingContext $context): bool
    {
        $days = $context->days();

        if ($rule->min_days !== null && $days < $rule->min_days) {
            return false;
        }

        if ($rule->max_days !== null && $days > $rule->max_days) {
            return false;
        }

        return true;
    }

    private function quantityMatches(PricingRuleModel $rule, PricingContext $context): bool
    {
        if ($rule->min_quantity !== null && $context->quantity < $rule->min_quantity) {
            return false;
        }

        if ($rule->max_quantity !== null && $context->quantity > $rule->max_quantity) {
            return false;
        }

        return true;
    }

    /**
     * @return iterable<CarbonImmutable>
     */
    private function datesInWindow(PricingContext $context): iterable
    {
        for (
            $date = $context->collectionDate;
            $date->lessThanOrEqualTo($context->returnDate);
            $date = $date->addDay()
        ) {
            yield $date;
        }
    }

    private function toCents(int|float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
