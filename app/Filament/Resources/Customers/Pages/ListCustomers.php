<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Models\Booking;
use App\Models\Customer;
use App\Filament\Resources\Concerns\HasPolishedListPage;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    use HasPolishedListPage;

    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.admin.list-records-page';

    public function getHeroEyebrow(): string
    {
        return 'Guest customers';
    }

    public function getHeroTitle(): string
    {
        return 'Customer enquiry trail';
    }

    public function getHeroDescription(): string
    {
        return 'Guest checkout keeps this lightweight: one clean contact record per hiring customer, matched by email when bookings are created.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return ['Guest checkout', 'Email matching', 'No logins'];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [
            $this->heroStat('Customers', Customer::query()->count(), 'Contact records captured.', 'primary'),
            $this->heroStat('Bookings', Booking::query()->count(), 'Bookings linked to customers.', 'success'),
            $this->heroStat('Repeat customers', Customer::query()->whereHas('bookings', fn (Builder $query): Builder => $query, '>=', 2)->count(), 'Two or more bookings.', 'warning'),
            $this->heroStat('With phone', Customer::query()->whereNotNull('phone')->count(), 'Reachable by call or WhatsApp.', 'gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
