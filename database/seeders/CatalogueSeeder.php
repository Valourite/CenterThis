<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->catalogue() as $categoryData) {
                $category = $this->category(
                    $categoryData['name'],
                    $categoryData['slug'],
                    $categoryData['position'],
                );

                foreach ($categoryData['products'] as $productData) {
                    $this->product($category, $productData);
                }
            }
        });
    }

    private function category(string $name, string $slug, int $position): Category
    {
        $category = Category::withTrashed()->firstOrNew(['slug' => $slug]);
        $category->fill([
            'parent_id' => null,
            'name' => $name,
            'position' => $position,
        ]);
        $category->forceFill(['deleted_at' => null])->save();

        return $category;
    }

    /**
     * @param  array{
     *     name: string,
     *     slug: string,
     *     description: string,
     *     position: int,
     *     palette: array{primary: string, secondary: string, accent: string},
     *     options: array<string, array<int, string>>,
     *     variants: array<int, array{
     *         sku: string,
     *         label: string,
     *         quantity: int,
     *         base_rate: string,
     *         deposit_amount: string,
     *         option_values?: array<string, string>
     *     }>
     * }  $data
     */
    private function product(Category $category, array $data): void
    {
        $product = Product::withTrashed()->firstOrNew(['slug' => $data['slug']]);
        $product->fill([
            'category_id' => $category->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'images' => $this->galleryImages($data),
            'active' => true,
            'position' => $data['position'],
        ]);
        $product->forceFill(['deleted_at' => null])->save();

        /** @var array<string, array<string, int>> $optionValueIds */
        $optionValueIds = [];

        foreach ($data['options'] as $optionPosition => $optionValues) {
            $optionName = (string) $optionPosition;
            $position = array_search($optionName, array_keys($data['options']), true);

            $option = ProductOption::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'name' => $optionName,
                ],
                ['position' => (int) $position],
            );

            foreach ($optionValues as $valuePosition => $value) {
                $optionValue = ProductOptionValue::query()->updateOrCreate(
                    [
                        'product_option_id' => $option->id,
                        'value' => $value,
                    ],
                    ['position' => $valuePosition],
                );

                $optionValueIds[$optionName][$value] = $optionValue->id;
            }
        }

        foreach ($data['variants'] as $variantData) {
            $variant = Variant::withTrashed()->firstOrNew(['sku' => $variantData['sku']]);
            $variant->fill([
                'product_id' => $product->id,
                'label' => $variantData['label'],
                'quantity' => $variantData['quantity'],
                'base_rate' => $variantData['base_rate'],
                'deposit_amount' => $variantData['deposit_amount'],
                'active' => true,
            ]);
            $variant->forceFill(['deleted_at' => null])->save();

            $valueIds = [];

            foreach ($variantData['option_values'] ?? [] as $optionName => $value) {
                $valueIds[] = $optionValueIds[$optionName][$value];
            }

            $variant->optionValues()->sync($valueIds);
        }
    }

    /**
     * @param  array{name: string, slug: string, palette: array{primary: string, secondary: string, accent: string}}  $product
     * @return list<string>
     */
    private function galleryImages(array $product): array
    {
        $paths = [];

        foreach (['hero', 'detail', 'styled'] as $index => $view) {
            $path = "products/{$product['slug']}-{$view}.svg";

            Storage::disk('public')->put(
                $path,
                $this->svg(
                    product: $product['name'],
                    view: $view,
                    number: $index + 1,
                    primary: $product['palette']['primary'],
                    secondary: $product['palette']['secondary'],
                    accent: $product['palette']['accent'],
                ),
                'public',
            );

            $paths[] = $path;
        }

        return $paths;
    }

    private function svg(
        string $product,
        string $view,
        int $number,
        string $primary,
        string $secondary,
        string $accent,
    ): string {
        $title = htmlspecialchars($product, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars(str($view)->headline()->toString(), ENT_QUOTES, 'UTF-8');
        $initial = htmlspecialchars(mb_substr($product, 0, 1), ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 1200" role="img" aria-labelledby="title desc">
  <title id="title">{$title} {$label}</title>
  <desc id="desc">Generated catalogue image {$number} for {$title}</desc>
  <defs>
    <linearGradient id="bg" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="{$primary}"/>
      <stop offset="54%" stop-color="{$secondary}"/>
      <stop offset="100%" stop-color="{$accent}"/>
    </linearGradient>
    <radialGradient id="glow" cx="72%" cy="22%" r="62%">
      <stop offset="0%" stop-color="#fffdf8" stop-opacity=".58"/>
      <stop offset="100%" stop-color="#fffdf8" stop-opacity="0"/>
    </radialGradient>
    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="34" stdDeviation="34" flood-color="#0d100e" flood-opacity=".24"/>
    </filter>
  </defs>
  <rect width="1600" height="1200" fill="url(#bg)"/>
  <rect width="1600" height="1200" fill="url(#glow)"/>
  <path d="M0 880 C260 760 420 970 690 820 C940 680 1120 790 1600 640 L1600 1200 L0 1200 Z" fill="#fffdf8" opacity=".18"/>
  <path d="M-80 190 C230 80 340 240 560 120 C810 -18 1010 130 1680 24" fill="none" stroke="#fffdf8" stroke-opacity=".20" stroke-width="34"/>
  <g filter="url(#shadow)">
    <rect x="238" y="278" width="1124" height="574" rx="58" fill="#fffdf8" opacity=".88"/>
    <rect x="300" y="340" width="1000" height="450" rx="42" fill="{$primary}" opacity=".10"/>
    <circle cx="520" cy="562" r="150" fill="{$accent}" opacity=".58"/>
    <rect x="672" y="438" width="442" height="72" rx="36" fill="{$secondary}" opacity=".52"/>
    <rect x="642" y="552" width="536" height="52" rx="26" fill="{$primary}" opacity=".32"/>
    <rect x="715" y="650" width="390" height="38" rx="19" fill="{$secondary}" opacity=".30"/>
  </g>
  <text x="520" y="614" text-anchor="middle" font-family="Georgia, serif" font-size="210" font-style="italic" fill="{$primary}" opacity=".78">{$initial}</text>
  <text x="800" y="990" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" font-weight="700" letter-spacing="8" fill="#fffdf8" opacity=".82">CENTERTHIS HIRE COLLECTION</text>
  <text x="800" y="1048" text-anchor="middle" font-family="Georgia, serif" font-size="76" fill="#fffdf8">{$title}</text>
  <text x="800" y="1110" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="700" letter-spacing="5" fill="#fffdf8" opacity=".76">{$label} / IMAGE {$number}</text>
</svg>
SVG;
    }

    /**
     * @return list<array{
     *     name: string,
     *     slug: string,
     *     position: int,
     *     products: list<array{
     *         name: string,
     *         slug: string,
     *         description: string,
     *         position: int,
     *         palette: array{primary: string, secondary: string, accent: string},
     *         options: array<string, list<string>>,
     *         variants: list<array{
     *             sku: string,
     *             label: string,
     *             quantity: int,
     *             base_rate: string,
     *             deposit_amount: string,
     *             option_values?: array<string, string>
     *         }>
     *     }>
     * }>
     */
    private function catalogue(): array
    {
        return [
            [
                'name' => 'Furniture',
                'slug' => 'furniture',
                'position' => 10,
                'products' => [
                    [
                        'name' => 'Folding Chair',
                        'slug' => 'folding-chair',
                        'description' => 'Practical folding chairs for ceremonies, conferences, overflow seating, and casual dining.',
                        'position' => 10,
                        'palette' => ['primary' => '#1f3a30', 'secondary' => '#80644d', 'accent' => '#d3c49f'],
                        'options' => ['Colour' => ['Black', 'White']],
                        'variants' => [
                            ['sku' => 'CHAIR-FOLD-BLK', 'label' => 'Black', 'quantity' => 100, 'base_rate' => '25.00', 'deposit_amount' => '50.00', 'option_values' => ['Colour' => 'Black']],
                            ['sku' => 'CHAIR-FOLD-WHT', 'label' => 'White', 'quantity' => 80, 'base_rate' => '28.00', 'deposit_amount' => '50.00', 'option_values' => ['Colour' => 'White']],
                        ],
                    ],
                    [
                        'name' => 'Clear Tiffany Chair',
                        'slug' => 'clear-tiffany-chair',
                        'description' => 'Elegant transparent Tiffany chairs suited to weddings, showers, and formal guest seating.',
                        'position' => 20,
                        'palette' => ['primary' => '#2f463a', 'secondary' => '#b9ae9d', 'accent' => '#fbfaf6'],
                        'options' => ['Cushion' => ['White', 'Ivory', 'Black']],
                        'variants' => [
                            ['sku' => 'CHAIR-TIFF-CLEAR-WHT', 'label' => 'Clear / White cushion', 'quantity' => 120, 'base_rate' => '42.00', 'deposit_amount' => '85.00', 'option_values' => ['Cushion' => 'White']],
                            ['sku' => 'CHAIR-TIFF-CLEAR-IVY', 'label' => 'Clear / Ivory cushion', 'quantity' => 90, 'base_rate' => '44.00', 'deposit_amount' => '85.00', 'option_values' => ['Cushion' => 'Ivory']],
                            ['sku' => 'CHAIR-TIFF-CLEAR-BLK', 'label' => 'Clear / Black cushion', 'quantity' => 60, 'base_rate' => '46.00', 'deposit_amount' => '85.00', 'option_values' => ['Cushion' => 'Black']],
                        ],
                    ],
                    [
                        'name' => 'Velvet Lounge Set',
                        'slug' => 'velvet-lounge-set',
                        'description' => 'Soft seating sets for lounge pockets, photo areas, bridal suites, and VIP corners.',
                        'position' => 30,
                        'palette' => ['primary' => '#22352f', 'secondary' => '#6f5a46', 'accent' => '#a9b3a2'],
                        'options' => ['Colour' => ['Olive', 'Champagne', 'Charcoal']],
                        'variants' => [
                            ['sku' => 'LOUNGE-VEL-OLV', 'label' => 'Olive velvet set', 'quantity' => 3, 'base_rate' => '950.00', 'deposit_amount' => '2500.00', 'option_values' => ['Colour' => 'Olive']],
                            ['sku' => 'LOUNGE-VEL-CHAMP', 'label' => 'Champagne velvet set', 'quantity' => 2, 'base_rate' => '1050.00', 'deposit_amount' => '2500.00', 'option_values' => ['Colour' => 'Champagne']],
                            ['sku' => 'LOUNGE-VEL-CHAR', 'label' => 'Charcoal velvet set', 'quantity' => 2, 'base_rate' => '980.00', 'deposit_amount' => '2500.00', 'option_values' => ['Colour' => 'Charcoal']],
                        ],
                    ],
                    [
                        'name' => 'Round Banquet Table',
                        'slug' => 'round-banquet-table',
                        'description' => 'Commercial round tables for guest dining, family seating, and reception layouts.',
                        'position' => 40,
                        'palette' => ['primary' => '#4f3c2f', 'secondary' => '#1f3a30', 'accent' => '#d8d1c5'],
                        'options' => ['Size' => ['1.5 m', '1.8 m']],
                        'variants' => [
                            ['sku' => 'TABLE-RND-150', 'label' => '1.5 m', 'quantity' => 20, 'base_rate' => '180.00', 'deposit_amount' => '500.00', 'option_values' => ['Size' => '1.5 m']],
                            ['sku' => 'TABLE-RND-180', 'label' => '1.8 m', 'quantity' => 15, 'base_rate' => '220.00', 'deposit_amount' => '600.00', 'option_values' => ['Size' => '1.8 m']],
                        ],
                    ],
                    [
                        'name' => 'Cocktail Table',
                        'slug' => 'cocktail-table',
                        'description' => 'Tall cocktail tables for welcome drinks, networking areas, and casual standing service.',
                        'position' => 50,
                        'palette' => ['primary' => '#171a17', 'secondary' => '#80644d', 'accent' => '#f1ede4'],
                        'options' => ['Top' => ['White', 'Wood', 'Black']],
                        'variants' => [
                            ['sku' => 'TABLE-COCK-WHT', 'label' => 'White top', 'quantity' => 18, 'base_rate' => '120.00', 'deposit_amount' => '300.00', 'option_values' => ['Top' => 'White']],
                            ['sku' => 'TABLE-COCK-WOOD', 'label' => 'Wood top', 'quantity' => 12, 'base_rate' => '150.00', 'deposit_amount' => '350.00', 'option_values' => ['Top' => 'Wood']],
                            ['sku' => 'TABLE-COCK-BLK', 'label' => 'Black top', 'quantity' => 10, 'base_rate' => '140.00', 'deposit_amount' => '350.00', 'option_values' => ['Top' => 'Black']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Linen',
                'slug' => 'linen',
                'position' => 20,
                'products' => [
                    [
                        'name' => 'Rectangular Tablecloth',
                        'slug' => 'rectangular-tablecloth',
                        'description' => 'Event-grade rectangular tablecloths in common table sizes and neutral colours.',
                        'position' => 10,
                        'palette' => ['primary' => '#fbfaf6', 'secondary' => '#b9ae9d', 'accent' => '#1f3a30'],
                        'options' => ['Size' => ['2.4 m', '3.0 m'], 'Colour' => ['White', 'Black']],
                        'variants' => [
                            ['sku' => 'LINEN-RECT-24-WHT', 'label' => '2.4 m / White', 'quantity' => 30, 'base_rate' => '90.00', 'deposit_amount' => '150.00', 'option_values' => ['Size' => '2.4 m', 'Colour' => 'White']],
                            ['sku' => 'LINEN-RECT-24-BLK', 'label' => '2.4 m / Black', 'quantity' => 20, 'base_rate' => '95.00', 'deposit_amount' => '150.00', 'option_values' => ['Size' => '2.4 m', 'Colour' => 'Black']],
                            ['sku' => 'LINEN-RECT-30-WHT', 'label' => '3.0 m / White', 'quantity' => 25, 'base_rate' => '110.00', 'deposit_amount' => '180.00', 'option_values' => ['Size' => '3.0 m', 'Colour' => 'White']],
                            ['sku' => 'LINEN-RECT-30-BLK', 'label' => '3.0 m / Black', 'quantity' => 15, 'base_rate' => '115.00', 'deposit_amount' => '180.00', 'option_values' => ['Size' => '3.0 m', 'Colour' => 'Black']],
                        ],
                    ],
                    [
                        'name' => 'Round Tablecloth',
                        'slug' => 'round-tablecloth',
                        'description' => 'Round tablecloths cut for banquet tables, cake tables, and signing tables.',
                        'position' => 20,
                        'palette' => ['primary' => '#f1ede4', 'secondary' => '#a9b3a2', 'accent' => '#80644d'],
                        'options' => ['Size' => ['3.0 m', '3.3 m'], 'Colour' => ['Ivory', 'Stone', 'Forest']],
                        'variants' => [
                            ['sku' => 'LINEN-RND-30-IVY', 'label' => '3.0 m / Ivory', 'quantity' => 20, 'base_rate' => '120.00', 'deposit_amount' => '200.00', 'option_values' => ['Size' => '3.0 m', 'Colour' => 'Ivory']],
                            ['sku' => 'LINEN-RND-30-STN', 'label' => '3.0 m / Stone', 'quantity' => 18, 'base_rate' => '125.00', 'deposit_amount' => '200.00', 'option_values' => ['Size' => '3.0 m', 'Colour' => 'Stone']],
                            ['sku' => 'LINEN-RND-33-FOR', 'label' => '3.3 m / Forest', 'quantity' => 10, 'base_rate' => '150.00', 'deposit_amount' => '240.00', 'option_values' => ['Size' => '3.3 m', 'Colour' => 'Forest']],
                        ],
                    ],
                    [
                        'name' => 'Napkin Set',
                        'slug' => 'napkin-set',
                        'description' => 'Cloth napkins packed in sets for formal table settings and tasting events.',
                        'position' => 30,
                        'palette' => ['primary' => '#d8d1c5', 'secondary' => '#1f3a30', 'accent' => '#fffdf8'],
                        'options' => ['Colour' => ['Ivory', 'Sage', 'Charcoal'], 'Pack' => ['Set of 10']],
                        'variants' => [
                            ['sku' => 'LINEN-NAP-IVY-10', 'label' => 'Ivory / Set of 10', 'quantity' => 30, 'base_rate' => '65.00', 'deposit_amount' => '100.00', 'option_values' => ['Colour' => 'Ivory', 'Pack' => 'Set of 10']],
                            ['sku' => 'LINEN-NAP-SAGE-10', 'label' => 'Sage / Set of 10', 'quantity' => 24, 'base_rate' => '70.00', 'deposit_amount' => '100.00', 'option_values' => ['Colour' => 'Sage', 'Pack' => 'Set of 10']],
                            ['sku' => 'LINEN-NAP-CHAR-10', 'label' => 'Charcoal / Set of 10', 'quantity' => 18, 'base_rate' => '75.00', 'deposit_amount' => '100.00', 'option_values' => ['Colour' => 'Charcoal', 'Pack' => 'Set of 10']],
                        ],
                    ],
                    [
                        'name' => 'Gauze Table Runner',
                        'slug' => 'gauze-table-runner',
                        'description' => 'Soft gauze runners for guest tables, grazing tables, and intimate setups.',
                        'position' => 40,
                        'palette' => ['primary' => '#a9b3a2', 'secondary' => '#80644d', 'accent' => '#f1ede4'],
                        'options' => ['Colour' => ['Sage', 'Sand', 'Mocha'], 'Length' => ['3 m']],
                        'variants' => [
                            ['sku' => 'LINEN-RUN-SAGE-3', 'label' => 'Sage / 3 m', 'quantity' => 26, 'base_rate' => '55.00', 'deposit_amount' => '90.00', 'option_values' => ['Colour' => 'Sage', 'Length' => '3 m']],
                            ['sku' => 'LINEN-RUN-SAND-3', 'label' => 'Sand / 3 m', 'quantity' => 24, 'base_rate' => '55.00', 'deposit_amount' => '90.00', 'option_values' => ['Colour' => 'Sand', 'Length' => '3 m']],
                            ['sku' => 'LINEN-RUN-MOCHA-3', 'label' => 'Mocha / 3 m', 'quantity' => 18, 'base_rate' => '60.00', 'deposit_amount' => '90.00', 'option_values' => ['Colour' => 'Mocha', 'Length' => '3 m']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Lighting',
                'slug' => 'lighting',
                'position' => 30,
                'products' => [
                    [
                        'name' => 'Festoon Light String',
                        'slug' => 'festoon-light-string',
                        'description' => 'Warm-white festoon lighting for tents, courtyards, patios, and outdoor events.',
                        'position' => 10,
                        'palette' => ['primary' => '#171a17', 'secondary' => '#d3c49f', 'accent' => '#fffdf8'],
                        'options' => ['Length' => ['10 m', '20 m']],
                        'variants' => [
                            ['sku' => 'LIGHT-FEST-10', 'label' => '10 m', 'quantity' => 25, 'base_rate' => '140.00', 'deposit_amount' => '350.00', 'option_values' => ['Length' => '10 m']],
                            ['sku' => 'LIGHT-FEST-20', 'label' => '20 m', 'quantity' => 15, 'base_rate' => '240.00', 'deposit_amount' => '500.00', 'option_values' => ['Length' => '20 m']],
                        ],
                    ],
                    [
                        'name' => 'LED Uplighter',
                        'slug' => 'led-uplighter',
                        'description' => 'Compact LED uplighters for walls, stages, backdrops, and brand-colour washes.',
                        'position' => 20,
                        'palette' => ['primary' => '#1f3a30', 'secondary' => '#171a17', 'accent' => '#d3c49f'],
                        'options' => ['Mode' => ['Warm white', 'RGB']],
                        'variants' => [
                            ['sku' => 'LIGHT-UP-WARM', 'label' => 'Warm white', 'quantity' => 18, 'base_rate' => '180.00', 'deposit_amount' => '450.00', 'option_values' => ['Mode' => 'Warm white']],
                            ['sku' => 'LIGHT-UP-RGB', 'label' => 'RGB colour wash', 'quantity' => 16, 'base_rate' => '220.00', 'deposit_amount' => '550.00', 'option_values' => ['Mode' => 'RGB']],
                        ],
                    ],
                    [
                        'name' => 'Candle Lantern Set',
                        'slug' => 'candle-lantern-set',
                        'description' => 'Lantern sets for aisles, entrances, lounge corners, and table clusters.',
                        'position' => 30,
                        'palette' => ['primary' => '#80644d', 'secondary' => '#d3c49f', 'accent' => '#fffdf8'],
                        'options' => ['Finish' => ['Black metal', 'Brass'], 'Pack' => ['Set of 6']],
                        'variants' => [
                            ['sku' => 'LIGHT-LANT-BLK-6', 'label' => 'Black metal / Set of 6', 'quantity' => 12, 'base_rate' => '160.00', 'deposit_amount' => '350.00', 'option_values' => ['Finish' => 'Black metal', 'Pack' => 'Set of 6']],
                            ['sku' => 'LIGHT-LANT-BRASS-6', 'label' => 'Brass / Set of 6', 'quantity' => 8, 'base_rate' => '210.00', 'deposit_amount' => '450.00', 'option_values' => ['Finish' => 'Brass', 'Pack' => 'Set of 6']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Audio',
                'slug' => 'audio',
                'position' => 40,
                'products' => [
                    [
                        'name' => 'Powered PA Speaker',
                        'slug' => 'powered-pa-speaker',
                        'description' => 'Portable powered speaker for speeches, background music, and smaller private events.',
                        'position' => 10,
                        'palette' => ['primary' => '#171a17', 'secondary' => '#4a4d46', 'accent' => '#a9b3a2'],
                        'options' => ['Size' => ['12 inch', '15 inch']],
                        'variants' => [
                            ['sku' => 'AUDIO-PA-12', 'label' => '12-inch powered speaker', 'quantity' => 12, 'base_rate' => '450.00', 'deposit_amount' => '1500.00', 'option_values' => ['Size' => '12 inch']],
                            ['sku' => 'AUDIO-PA-15', 'label' => '15-inch powered speaker', 'quantity' => 8, 'base_rate' => '620.00', 'deposit_amount' => '2200.00', 'option_values' => ['Size' => '15 inch']],
                        ],
                    ],
                    [
                        'name' => 'Wireless Microphone Kit',
                        'slug' => 'wireless-microphone-kit',
                        'description' => 'Wireless microphone kits for ceremonies, speeches, MCs, and panel discussions.',
                        'position' => 20,
                        'palette' => ['primary' => '#0d100e', 'secondary' => '#80644d', 'accent' => '#d8d1c5'],
                        'options' => ['Kit' => ['Handheld pair', 'Lapel and handheld']],
                        'variants' => [
                            ['sku' => 'AUDIO-MIC-HAND-2', 'label' => 'Handheld pair', 'quantity' => 6, 'base_rate' => '380.00', 'deposit_amount' => '1200.00', 'option_values' => ['Kit' => 'Handheld pair']],
                            ['sku' => 'AUDIO-MIC-LAPEL-HAND', 'label' => 'Lapel and handheld', 'quantity' => 4, 'base_rate' => '420.00', 'deposit_amount' => '1400.00', 'option_values' => ['Kit' => 'Lapel and handheld']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Decor',
                'slug' => 'decor',
                'position' => 50,
                'products' => [
                    [
                        'name' => 'Backdrop Arch',
                        'slug' => 'backdrop-arch',
                        'description' => 'Freestanding arches for ceremonies, photo moments, seating plans, and display backdrops.',
                        'position' => 10,
                        'palette' => ['primary' => '#1f3a30', 'secondary' => '#d8d1c5', 'accent' => '#80644d'],
                        'options' => ['Shape' => ['Round', 'Arched', 'Double panel'], 'Finish' => ['White', 'Wood']],
                        'variants' => [
                            ['sku' => 'DECOR-ARCH-RND-WHT', 'label' => 'Round / White', 'quantity' => 3, 'base_rate' => '650.00', 'deposit_amount' => '1800.00', 'option_values' => ['Shape' => 'Round', 'Finish' => 'White']],
                            ['sku' => 'DECOR-ARCH-ARCH-WOOD', 'label' => 'Arched / Wood', 'quantity' => 2, 'base_rate' => '780.00', 'deposit_amount' => '2000.00', 'option_values' => ['Shape' => 'Arched', 'Finish' => 'Wood']],
                            ['sku' => 'DECOR-ARCH-DOUBLE-WHT', 'label' => 'Double panel / White', 'quantity' => 2, 'base_rate' => '900.00', 'deposit_amount' => '2400.00', 'option_values' => ['Shape' => 'Double panel', 'Finish' => 'White']],
                        ],
                    ],
                    [
                        'name' => 'Plinth Set',
                        'slug' => 'plinth-set',
                        'description' => 'Nested plinths for cakes, florals, welcome drinks, products, and gift displays.',
                        'position' => 20,
                        'palette' => ['primary' => '#fffdf8', 'secondary' => '#b9ae9d', 'accent' => '#1f3a30'],
                        'options' => ['Finish' => ['White', 'Black', 'Oak']],
                        'variants' => [
                            ['sku' => 'DECOR-PLINTH-WHT', 'label' => 'White set of 3', 'quantity' => 5, 'base_rate' => '360.00', 'deposit_amount' => '900.00', 'option_values' => ['Finish' => 'White']],
                            ['sku' => 'DECOR-PLINTH-BLK', 'label' => 'Black set of 3', 'quantity' => 3, 'base_rate' => '390.00', 'deposit_amount' => '950.00', 'option_values' => ['Finish' => 'Black']],
                            ['sku' => 'DECOR-PLINTH-OAK', 'label' => 'Oak set of 3', 'quantity' => 2, 'base_rate' => '440.00', 'deposit_amount' => '1100.00', 'option_values' => ['Finish' => 'Oak']],
                        ],
                    ],
                    [
                        'name' => 'Centrepiece Vase Set',
                        'slug' => 'centrepiece-vase-set',
                        'description' => 'Neutral glass and ceramic vases for dining tables, cocktail tables, and display surfaces.',
                        'position' => 30,
                        'palette' => ['primary' => '#d8d1c5', 'secondary' => '#a9b3a2', 'accent' => '#80644d'],
                        'options' => ['Material' => ['Glass', 'Ceramic'], 'Pack' => ['Set of 12']],
                        'variants' => [
                            ['sku' => 'DECOR-VASE-GLASS-12', 'label' => 'Glass / Set of 12', 'quantity' => 10, 'base_rate' => '180.00', 'deposit_amount' => '350.00', 'option_values' => ['Material' => 'Glass', 'Pack' => 'Set of 12']],
                            ['sku' => 'DECOR-VASE-CER-12', 'label' => 'Ceramic / Set of 12', 'quantity' => 6, 'base_rate' => '240.00', 'deposit_amount' => '480.00', 'option_values' => ['Material' => 'Ceramic', 'Pack' => 'Set of 12']],
                        ],
                    ],
                    [
                        'name' => 'Welcome Sign Stand',
                        'slug' => 'welcome-sign-stand',
                        'description' => 'Stands for welcome signs, seating plans, menus, schedules, and branded boards.',
                        'position' => 40,
                        'palette' => ['primary' => '#80644d', 'secondary' => '#1f3a30', 'accent' => '#f1ede4'],
                        'options' => ['Finish' => ['Black', 'White', 'Gold']],
                        'variants' => [
                            ['sku' => 'DECOR-SIGN-BLK', 'label' => 'Black stand', 'quantity' => 8, 'base_rate' => '120.00', 'deposit_amount' => '350.00', 'option_values' => ['Finish' => 'Black']],
                            ['sku' => 'DECOR-SIGN-WHT', 'label' => 'White stand', 'quantity' => 6, 'base_rate' => '120.00', 'deposit_amount' => '350.00', 'option_values' => ['Finish' => 'White']],
                            ['sku' => 'DECOR-SIGN-GLD', 'label' => 'Gold stand', 'quantity' => 4, 'base_rate' => '150.00', 'deposit_amount' => '450.00', 'option_values' => ['Finish' => 'Gold']],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Bespoke Items',
                'slug' => 'bespoke-items',
                'position' => 60,
                'products' => [
                    [
                        'name' => 'Laser Cut Place Names',
                        'slug' => 'laser-cut-place-names',
                        'description' => 'Reusable sample sets for choosing finishes and sizes before ordering bespoke place names.',
                        'position' => 10,
                        'palette' => ['primary' => '#d3c49f', 'secondary' => '#80644d', 'accent' => '#1f3a30'],
                        'options' => ['Finish' => ['Raw wood', 'Gold acrylic', 'Black acrylic'], 'Sample Pack' => ['Set of 20']],
                        'variants' => [
                            ['sku' => 'BESPOKE-PLACE-WOOD-20', 'label' => 'Raw wood / Set of 20', 'quantity' => 6, 'base_rate' => '280.00', 'deposit_amount' => '300.00', 'option_values' => ['Finish' => 'Raw wood', 'Sample Pack' => 'Set of 20']],
                            ['sku' => 'BESPOKE-PLACE-GOLD-20', 'label' => 'Gold acrylic / Set of 20', 'quantity' => 4, 'base_rate' => '360.00', 'deposit_amount' => '400.00', 'option_values' => ['Finish' => 'Gold acrylic', 'Sample Pack' => 'Set of 20']],
                            ['sku' => 'BESPOKE-PLACE-BLK-20', 'label' => 'Black acrylic / Set of 20', 'quantity' => 4, 'base_rate' => '340.00', 'deposit_amount' => '400.00', 'option_values' => ['Finish' => 'Black acrylic', 'Sample Pack' => 'Set of 20']],
                        ],
                    ],
                    [
                        'name' => 'Thank You Gift Box',
                        'slug' => 'thank-you-gift-box',
                        'description' => 'Display gift box samples for bridal parties, speakers, sponsors, and guest thank-you tables.',
                        'position' => 20,
                        'palette' => ['primary' => '#f1ede4', 'secondary' => '#80644d', 'accent' => '#a9b3a2'],
                        'options' => ['Box Colour' => ['Ivory', 'Kraft', 'Black'], 'Pack' => ['Set of 10']],
                        'variants' => [
                            ['sku' => 'BESPOKE-GIFT-IVY-10', 'label' => 'Ivory / Set of 10', 'quantity' => 8, 'base_rate' => '220.00', 'deposit_amount' => '250.00', 'option_values' => ['Box Colour' => 'Ivory', 'Pack' => 'Set of 10']],
                            ['sku' => 'BESPOKE-GIFT-KRAFT-10', 'label' => 'Kraft / Set of 10', 'quantity' => 8, 'base_rate' => '200.00', 'deposit_amount' => '250.00', 'option_values' => ['Box Colour' => 'Kraft', 'Pack' => 'Set of 10']],
                            ['sku' => 'BESPOKE-GIFT-BLK-10', 'label' => 'Black / Set of 10', 'quantity' => 5, 'base_rate' => '240.00', 'deposit_amount' => '300.00', 'option_values' => ['Box Colour' => 'Black', 'Pack' => 'Set of 10']],
                        ],
                    ],
                ],
            ],
        ];
    }
}
