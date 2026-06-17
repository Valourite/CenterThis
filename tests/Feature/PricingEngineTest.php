<?php

use App\Models\Product;
use App\Models\PricingRule;
use App\Models\Variant;
use App\Pricing\PricingContext;
use App\Pricing\PricingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $product = Product::create(['name' => 'Round table', 'slug' => 'round-table']);

    $this->variant = Variant::create([
        'product_id' => $product->id,
        'label' => '1.5m',
        'quantity' => 10,
        'base_rate' => 30.00,
        'deposit_amount' => 100.00,
    ]);

    $this->engine = new PricingEngine();
});

it('prices the base rental as rate times quantity times days', function () {
    $context = PricingContext::make($this->variant, 4, '2026-07-01', '2026-07-03');

    $breakdown = $this->engine->price($context);

    expect($breakdown->rentalCents)->toBe(36000)
        ->and($breakdown->rentalSubtotal())->toBe('360.00');
});

it('computes the deposit as deposit amount times quantity', function () {
    $context = PricingContext::make($this->variant, 4, '2026-07-01', '2026-07-03');

    $breakdown = $this->engine->price($context);

    expect($breakdown->depositCents)->toBe(40000)
        ->and($breakdown->depositTotal())->toBe('400.00');
});

it('combines rental and deposit into the grand total', function () {
    $context = PricingContext::make($this->variant, 4, '2026-07-01', '2026-07-03');

    expect($this->engine->price($context)->grandTotal())->toBe('760.00');
});

it('bills a single-day booking as one rental day', function () {
    $context = PricingContext::make($this->variant, 1, '2026-07-01', '2026-07-01');

    expect($this->engine->price($context)->rentalCents)->toBe(3000);
});

it('applies no adjustments when no active rules exist', function () {
    $context = PricingContext::make($this->variant, 2, '2026-07-01', '2026-07-02');

    expect($this->engine->price($context)->adjustments)->toBe([]);
});

it('applies a configurable percentage surcharge immediately from the database', function () {
    PricingRule::create([
        'type' => 'configurable',
        'name' => 'Peak demand surcharge',
        'effect_direction' => 'surcharge',
        'effect_type' => 'percentage',
        'effect_value' => 10,
        'scope' => 'global',
        'priority' => 10,
        'active' => true,
    ]);

    $breakdown = $this->engine->price(PricingContext::make($this->variant, 2, '2026-07-01', '2026-07-02'));

    expect($breakdown->rentalSubtotal())->toBe('132.00')
        ->and($breakdown->adjustments)->toBe([
            [
                'label' => 'Peak demand surcharge',
                'cents' => 1200,
            ],
        ]);
});

it('applies configurable discounts only when conditions match', function () {
    PricingRule::create([
        'type' => 'configurable',
        'name' => 'Four-day discount',
        'effect_direction' => 'discount',
        'effect_type' => 'percentage',
        'effect_value' => 20,
        'scope' => 'global',
        'min_days' => 4,
        'priority' => 10,
        'active' => true,
    ]);

    $shortHire = $this->engine->price(PricingContext::make($this->variant, 1, '2026-07-01', '2026-07-03'));
    $longHire = $this->engine->price(PricingContext::make($this->variant, 1, '2026-07-01', '2026-07-04'));

    expect($shortHire->adjustments)->toBe([])
        ->and($longHire->rentalSubtotal())->toBe('96.00')
        ->and($longHire->adjustments)->toBe([
            [
                'label' => 'Four-day discount',
                'cents' => -2400,
            ],
        ]);
});

it('can limit configurable rules by weekday and product scope', function () {
    PricingRule::create([
        'type' => 'configurable',
        'name' => 'Weekend product surcharge',
        'effect_direction' => 'surcharge',
        'effect_type' => 'fixed_item',
        'effect_value' => 25,
        'scope' => 'product',
        'scope_id' => $this->variant->product_id,
        'apply_weekdays' => [0, 6],
        'priority' => 10,
        'active' => true,
    ]);

    $weekdayHire = $this->engine->price(PricingContext::make($this->variant, 2, '2026-07-01', '2026-07-02'));
    $weekendHire = $this->engine->price(PricingContext::make($this->variant, 2, '2026-07-04', '2026-07-04'));

    expect($weekdayHire->adjustments)->toBe([])
        ->and($weekendHire->rentalSubtotal())->toBe('110.00')
        ->and($weekendHire->adjustments)->toBe([
            [
                'label' => 'Weekend product surcharge',
                'cents' => 5000,
            ],
        ]);
});

it('can override the unit rental rate with a configurable rule', function () {
    PricingRule::create([
        'type' => 'configurable',
        'name' => 'Promotional unit rate',
        'effect_direction' => 'discount',
        'effect_type' => 'override_unit_rate',
        'effect_value' => 20,
        'scope' => 'variant',
        'scope_id' => $this->variant->id,
        'priority' => 10,
        'active' => true,
    ]);

    $breakdown = $this->engine->price(PricingContext::make($this->variant, 2, '2026-07-01', '2026-07-02'));

    expect($breakdown->rentalSubtotal())->toBe('80.00')
        ->and($breakdown->adjustments)->toBe([
            [
                'label' => 'Promotional unit rate',
                'cents' => -4000,
            ],
        ]);
});
