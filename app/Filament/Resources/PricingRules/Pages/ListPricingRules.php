<?php

namespace App\Filament\Resources\PricingRules\Pages;

use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Filament\Resources\PricingRules\PricingRuleResource;
use App\Models\PricingRule;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPricingRules extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = PricingRuleResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Pricing pipeline';
    }

    public function getHeroTitle(): string
    {
        return 'Rule orchestration';
    }

    public function getHeroDescription(): string
    {
        return 'Toggle ordered pricing rules without changing the booking contract. Base hire remains the fallback whenever no rule applies.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Priority order', 'Toggleable', 'Scoped'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [
            $this->heroStat('Active rules', PricingRule::query()->where('active', true)->count(), 'Applied by priority.', 'success'),
            $this->heroStat('Configured', PricingRule::query()->count(), 'Rows in the pricing table.', 'primary'),
            $this->heroStat('Global rules', PricingRule::query()->where('scope', 'global')->count(), 'Apply across catalogue.', 'warning'),
            $this->heroStat('Discount rules', PricingRule::query()->where('effect_direction', 'discount')->count(), 'Subtract from rental.', 'secondary'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
