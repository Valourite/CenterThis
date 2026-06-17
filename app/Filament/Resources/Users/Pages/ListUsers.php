<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = UserResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Panel access';
    }

    public function getHeroTitle(): string
    {
        return 'Admin users';
    }

    public function getHeroDescription(): string
    {
        return 'Add team members who can sign in to the admin panel, and keep their access details current.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Panel logins', 'Email + password', 'Self-service profile'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [
            $this->heroStat('Admin users', User::query()->count(), 'Accounts that can access the panel.', 'primary'),
            $this->heroStat('Added this month', User::query()->where('created_at', '>=', now()->startOfMonth())->count(), 'New accounts since the 1st.', 'success'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
