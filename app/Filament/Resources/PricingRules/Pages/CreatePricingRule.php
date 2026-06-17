<?php

namespace App\Filament\Resources\PricingRules\Pages;

use App\Filament\Resources\PricingRules\PricingRuleResource;
use App\Models\PricingRule;
use Filament\Resources\Pages\CreateRecord;

class CreatePricingRule extends CreateRecord
{
    protected static string $resource = PricingRuleResource::class;

    /** @var array<int, int|string> */
    protected array $scopeIds = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->scopeIds = $data['scope_ids'] ?? [];
        unset($data['scope_ids']);

        if (($data['scope'] ?? 'global') === 'global') {
            $this->scopeIds = [];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var PricingRule $record */
        $record = $this->record;
        $record->syncScopeTargets($this->scopeIds);
    }
}
