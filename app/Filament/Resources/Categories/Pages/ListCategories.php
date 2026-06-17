<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = CategoryResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Catalogue structure';
    }

    public function getHeroTitle(): string
    {
        return 'Category merchandising';
    }

    public function getHeroDescription(): string
    {
        return 'Keep the hire collection grouped clearly so customers can move from event need to bookable item quickly.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Navigation', 'Grouping', 'Display order'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [
            $this->heroStat('Categories', Category::query()->count(), 'Total catalogue groups.', 'primary'),
            $this->heroStat('Top level', Category::query()->whereNull('parent_id')->count(), 'Primary browsing sections.', 'success'),
            $this->heroStat('Products', Product::query()->count(), 'Items assigned to categories.', 'warning'),
            $this->heroStat('Archived', Category::onlyTrashed()->count(), 'Soft-deleted categories.', 'gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
