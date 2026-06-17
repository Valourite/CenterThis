<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $pricing_rule_id
 * @property int $scope_id
 */
class PricingRuleScope extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pricing_rule_id',
        'scope_id',
    ];

    protected function casts(): array
    {
        return [
            'scope_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<PricingRule, $this>
     */
    public function pricingRule(): BelongsTo
    {
        return $this->belongsTo(PricingRule::class);
    }
}
