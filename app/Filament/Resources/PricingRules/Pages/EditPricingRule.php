<?php

namespace App\Filament\Resources\PricingRules\Pages;

use App\Filament\Resources\PricingRules\PricingRuleResource;
use App\Models\PricingRule;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPricingRule extends EditRecord
{
    protected static string $resource = PricingRuleResource::class;

    /** @var array<int, int|string> */
    protected array $scopeIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var PricingRule $record */
        $record = $this->record;
        $data['scope_ids'] = $record->scopeTargetIds();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->scopeIds = $data['scope_ids'] ?? [];
        unset($data['scope_ids']);

        if (($data['scope'] ?? 'global') === 'global') {
            $this->scopeIds = [];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var PricingRule $record */
        $record = $this->record;
        $record->syncScopeTargets($this->scopeIds);
    }
}
