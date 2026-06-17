<?php

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\PricingRules\Pages\ListPricingRules;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\OptionsRelationManager;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Widgets\OperationsOverview;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\User;
use App\Models\Variant;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    actingAs(User::factory()->create());
});

it('requires authentication for the admin panel', function () {
    auth()->logout();

    $this->get('/admin')
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('renders the admin resource lists and dashboard widget', function () {
    Livewire::test(ListCategories::class)
        ->assertSuccessful()
        ->assertSee('Category merchandising');
    Livewire::test(ListProducts::class)
        ->assertSuccessful()
        ->assertSee('Catalogue control room');
    Livewire::test(ListCustomers::class)
        ->assertSuccessful()
        ->assertSee('Customer enquiry trail');
    Livewire::test(ListBookings::class)
        ->assertSuccessful()
        ->assertSee('Bookings and stock movement');
    Livewire::test(ListPricingRules::class)
        ->assertSuccessful()
        ->assertSee('Rule orchestration');
    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->assertSee('Admin users');
    Livewire::test(OperationsOverview::class)->assertSuccessful();
});

it('creates an admin user with a hashed password through the resource', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New Admin',
            'email' => 'new-admin@centerthis.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'new-admin@centerthis.test')->firstOrFail();

    expect($user->name)->toBe('New Admin')
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('keeps the existing password when editing a user without a new one', function () {
    $user = User::factory()->create(['password' => Hash::make('original-password')]);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => 'Renamed Admin',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Renamed Admin')
        ->and(Hash::check('original-password', $user->password))->toBeTrue();
});

it('renders product option and variant relation managers', function () {
    $product = Product::query()->create([
        'name' => 'Chair',
        'slug' => 'chair',
    ]);

    Livewire::test(EditProduct::class, ['record' => $product->id])
        ->assertSeeLivewire(OptionsRelationManager::class)
        ->assertSee('Variants');
});

it('creates a variant and syncs option values through the pivot', function () {
    [$product, $colourValue, $sizeValue] = productWithOptionValues();

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'label' => 'Black large',
            'sku' => 'CHAIR-BLK-L',
            'quantity' => 20,
            'base_rate' => '25.00',
            'deposit_amount' => '50.00',
            'active' => true,
            'optionValues' => [$colourValue->id, $sizeValue->id],
        ])
        ->assertHasNoFormErrors();

    $variant = Variant::query()->where('sku', 'CHAIR-BLK-L')->firstOrFail();

    expect($variant->product->is($product))->toBeTrue()
        ->and($variant->optionValues()->pluck('product_option_values.id')->all())
        ->toEqualCanonicalizing([$colourValue->id, $sizeValue->id]);
});

it('rejects multiple values from the same product option', function () {
    [$product, $colourValue] = productWithOptionValues();
    $otherColour = ProductOptionValue::query()->create([
        'product_option_id' => $colourValue->product_option_id,
        'value' => 'White',
        'position' => 1,
    ]);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'label' => 'Invalid variant',
            'quantity' => 10,
            'base_rate' => '25.00',
            'deposit_amount' => '50.00',
            'active' => true,
            'optionValues' => [$colourValue->id, $otherColour->id],
        ])
        ->assertHasFormErrors(['optionValues']);

    expect(Variant::query()->where('label', 'Invalid variant')->exists())->toBeFalse();
});

it('renders booking items without exposing mutation actions', function () {
    [$booking, $item] = bookingForAdmin(BookingStatus::Confirmed);

    Livewire::test(ItemsRelationManager::class, [
        'ownerRecord' => $booking,
        'pageClass' => EditBooking::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords(collect([$item]))
        ->assertActionDoesNotExist(TestAction::make(CreateAction::class)->table());
});

it('renders booking status actions for live records', function () {
    [$booking] = bookingForAdmin(BookingStatus::Pending);

    Livewire::test(ListBookings::class)
        ->assertCanSeeTableRecords(collect([$booking]))
        ->assertActionVisible(TestAction::make('collect')->table($booking))
        ->assertActionVisible(TestAction::make('release')->table($booking))
        ->assertActionHidden(TestAction::make('return')->table($booking));
});

it('moves a booking through collected and returned statuses', function () {
    [$booking] = bookingForAdmin(BookingStatus::Confirmed);

    Livewire::test(EditBooking::class, ['record' => $booking->id])
        ->callAction('collect')
        ->assertNotified();

    expect($booking->refresh()->status)->toBe(BookingStatus::Collected)
        ->and($booking->collected_at)->not->toBeNull();

    Livewire::test(EditBooking::class, ['record' => $booking->id])
        ->callAction('return')
        ->assertNotified();

    expect($booking->refresh()->status)->toBe(BookingStatus::Completed)
        ->and($booking->returned_at)->not->toBeNull();
});

it('releases stock from a live booking', function () {
    [$booking] = bookingForAdmin(BookingStatus::Pending);

    Livewire::test(EditBooking::class, ['record' => $booking->id])
        ->callAction('release')
        ->assertNotified();

    expect($booking->refresh()->status)->toBe(BookingStatus::Released);
});

/**
 * @return array{Product, ProductOptionValue, ProductOptionValue}
 */
function productWithOptionValues(): array
{
    $product = Product::query()->create([
        'name' => 'Chair',
        'slug' => 'chair',
    ]);

    $colour = ProductOption::query()->create([
        'product_id' => $product->id,
        'name' => 'Colour',
        'position' => 0,
    ]);

    $size = ProductOption::query()->create([
        'product_id' => $product->id,
        'name' => 'Size',
        'position' => 1,
    ]);

    return [
        $product,
        ProductOptionValue::query()->create([
            'product_option_id' => $colour->id,
            'value' => 'Black',
            'position' => 0,
        ]),
        ProductOptionValue::query()->create([
            'product_option_id' => $size->id,
            'value' => 'Large',
            'position' => 0,
        ]),
    ];
}

/**
 * @return array{Booking, BookingItem}
 */
function bookingForAdmin(BookingStatus $status): array
{
    $product = Product::query()->create([
        'name' => 'Table',
        'slug' => 'table-'.str()->random(8),
    ]);

    $variant = Variant::query()->create([
        'product_id' => $product->id,
        'label' => 'Round',
        'quantity' => 10,
        'base_rate' => '30.00',
        'deposit_amount' => '100.00',
    ]);

    $customer = Customer::query()->create([
        'name' => 'Admin Test Customer',
        'email' => str()->random(8).'@example.test',
    ]);

    $booking = Booking::query()->create([
        'customer_id' => $customer->id,
        'status' => $status,
        'collection_date' => '2026-07-01',
        'return_date' => '2026-07-03',
        'rental_subtotal' => '90.00',
        'deposit_total' => '100.00',
        'grand_total' => '190.00',
    ]);

    $item = BookingItem::query()->create([
        'booking_id' => $booking->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
        'unit_rate' => '30.00',
        'unit_deposit' => '100.00',
        'line_total' => '90.00',
    ]);

    return [$booking, $item];
}
