<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\Variant;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = ProductResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Hire catalogue';
    }

    public function getHeroTitle(): string
    {
        return 'Catalogue control room';
    }

    public function getHeroDescription(): string
    {
        return 'Shape the customer-facing hire collection, manage product galleries, and keep variants ready for availability checks.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Images', 'Options', 'Variants'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [
            $this->heroStat('Active products', Product::query()->where('active', true)->count(), 'Visible on the hire collection.', 'success'),
            $this->heroStat('Variant records', Variant::query()->count(), 'Bookable stock contracts.', 'primary'),
            $this->heroStat('Stock units', Variant::query()->where('active', true)->sum('quantity'), 'Active variant quantity.', 'warning'),
            $this->heroStat('Galleries', Product::query()->whereNotNull('images')->count(), 'Products with media attached.', 'gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
