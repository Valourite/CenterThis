<?php

namespace App\Models;

use Database\Factories\ProductOptionValueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOptionValue extends Model
{
    /** @use HasFactory<ProductOptionValueFactory> */
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'value',
        'position',
    ];

    /**
     * @return BelongsTo<ProductOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    /**
     * @return BelongsToMany<Variant, $this>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Variant::class, 'variant_option_value');
    }

    public function displayLabel(): string
    {
        $option = $this->getRelationValue('option');
        $optionName = $option instanceof ProductOption
            ? $option->getAttribute('name')
            : $this->option()->value('name');

        return "{$optionName}: {$this->value}";
    }
}
