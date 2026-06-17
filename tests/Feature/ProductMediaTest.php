<?php

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\BookingItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    actingAs(User::factory()->create());
    Storage::fake('public');
});

it('allows an admin to upload and order multiple product images', function () {
    $product = Product::factory()->create();

    Livewire::test(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'images' => [
                UploadedFile::fake()->image('chair-front.jpg', 1200, 900),
                UploadedFile::fake()->image('chair-side.jpg', 1200, 900),
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $images = $product->refresh()->images;

    expect($images)->toHaveCount(2);
    Storage::disk('public')->assertExists($images);
});

it('removes files deleted from a product gallery', function () {
    Storage::disk('public')->put('products/keep.webp', 'keep');
    Storage::disk('public')->put('products/remove.webp', 'remove');

    $product = Product::factory()->create([
        'images' => [
            'products/keep.webp',
            'products/remove.webp',
        ],
    ]);

    $product->update([
        'images' => ['products/keep.webp'],
    ]);

    Storage::disk('public')->assertExists('products/keep.webp');
    Storage::disk('public')->assertMissing('products/remove.webp');
});

it('removes product images when a product is deleted', function () {
    Storage::disk('public')->put('products/delete-front.webp', 'front');
    Storage::disk('public')->put('products/delete-side.webp', 'side');

    $product = Product::factory()->create([
        'images' => [
            'products/delete-front.webp',
            'products/delete-side.webp',
        ],
    ]);

    $product->delete();

    expect($product->refresh()->trashed())->toBeTrue();

    //force delete product to remove images
    $product->forceDelete();
    
    Storage::disk('public')->assertMissing([
        'products/delete-front.webp',
        'products/delete-side.webp',
    ]);
});

it('prevents deleting a product when any variant has bookings', function () {
    Storage::disk('public')->put('products/booked-front.webp', 'front');

    $product = Product::factory()->create([
        'images' => ['products/booked-front.webp'],
    ]);
    $variant = Variant::factory()->for($product)->create();
    BookingItem::factory()->for($variant)->create();

    expect(fn () => $product->delete())
        ->toThrow(RuntimeException::class, $product->deleteBlockedMessage());

    expect($product->refresh()->trashed())->toBeFalse()
        ->and($product->canBeDeleted())->toBeFalse()
        ->and($product->hasBookings())->toBeTrue();

    Storage::disk('public')->assertExists('products/booked-front.webp');
});

it('renders the product gallery and active variants on the public detail page', function () {
    $product = Product::factory()->create([
        'name' => 'Oak Harvest Table',
        'slug' => 'oak-harvest-table',
        'description' => 'A warm timber table for guest dining and display areas.',
        'images' => [
            'products/oak-table-front.webp',
            'products/oak-table-detail.webp',
        ],
    ]);
    Variant::factory()->for($product)->create([
        'label' => '2.4 metre',
        'base_rate' => '650.00',
        'deposit_amount' => '1200.00',
    ]);

    $this->get(route('catalogue.product', $product))
        ->assertSuccessful()
        ->assertSee('Oak Harvest Table')
        ->assertSee('2.4 metre')
        ->assertSee('R650.00')
        ->assertSee('/storage/products/oak-table-front.webp', escape: false)
        ->assertSee('/storage/products/oak-table-detail.webp', escape: false)
        ->assertSee('Add to hire basket');
});

it('does not expose inactive products on public detail routes', function () {
    $product = Product::factory()->inactive()->create();
    Variant::factory()->for($product)->create();

    $this->get(route('catalogue.product', $product))
        ->assertNotFound();
});
