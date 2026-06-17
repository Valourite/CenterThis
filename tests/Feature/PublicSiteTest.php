<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;

it('renders every public page', function (string $route, string $copy) {
    $this->get(route($route))
        ->assertSuccessful()
        ->assertSee($copy);
})->with([
    'home' => ['home', 'Events worth'],
    'about' => ['about', 'We make space for'],
    'services' => ['services', 'The pieces for'],
    'portfolio' => ['portfolio', 'Our pieces,'],
    'catalogue' => ['catalogue', 'Find the pieces'],
]);

it('shows active catalogue products and prices', function () {
    $category = Category::factory()->create(['name' => 'Furniture']);
    $product = Product::factory()->for($category)->create(['name' => 'Statement Chair']);
    Variant::factory()->for($product)->create([
        'label' => 'Oxblood',
        'base_rate' => '125.00',
    ]);

    $this->get(route('catalogue'))
        ->assertSuccessful()
        ->assertSee('Statement Chair')
        ->assertSee('R125.00')
        ->assertSee('View item')
        ->assertSee(route('catalogue.product', $product));
});

it('previews seeded catalogue products on the home page', function () {
    $category = Category::factory()->create(['name' => 'Linen']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Textured Tablecloth',
        'position' => 1,
    ]);
    Variant::factory()->for($product)->create(['base_rate' => '90.00']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Textured Tablecloth')
        ->assertSee('R90.00');
});
