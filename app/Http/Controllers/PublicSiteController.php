<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Booking;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        $featuredProducts = Product::query()
            ->select(['id', 'category_id', 'name', 'slug', 'description'])
            ->with([
                'category:id,name',
                'variants' => fn ($query) => $query
                    ->select(['id', 'product_id', 'label', 'base_rate'])
                    ->where('active', true)
                    ->orderBy('base_rate'),
            ])
            ->where('active', true)
            ->whereHas('variants', fn ($query) => $query->where('active', true))
            ->orderBy('position')
            ->limit(4)
            ->get();

        return view('pages.home', compact('featuredProducts'));
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function services(): View
    {
        return view('pages.services');
    }

    public function portfolio(): View
    {
        return view('pages.portfolio');
    }

    public function catalogue(): View
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('products', fn ($query) => $query
                ->where('active', true)
                ->whereHas('variants', fn ($variantQuery) => $variantQuery->where('active', true)))
            ->orderBy('position')
            ->get();

        return view('pages.catalogue', compact('categories'));
    }

    public function product(Product $product): View
    {
        abort_unless($product->active, 404);

        $product->load([
            'category:id,name,slug',
            'variants' => fn ($query) => $query
                ->select([
                    'id',
                    'product_id',
                    'label',
                    'quantity',
                    'base_rate',
                    'deposit_amount',
                ])
                ->where('active', true)
                ->with('optionValues.option:id,name')
                ->orderBy('base_rate'),
        ]);

        abort_if($product->variants->isEmpty(), 404);

        $relatedProducts = $product->category
            ? Product::query()
                ->select(['id', 'category_id', 'name', 'slug', 'description', 'images'])
                ->with([
                    'variants' => fn ($query) => $query
                        ->select(['id', 'product_id', 'base_rate'])
                        ->where('active', true)
                        ->orderBy('base_rate'),
                ])
                ->whereBelongsTo($product->category)
                ->whereKeyNot($product->id)
                ->where('active', true)
                ->whereHas('variants', fn ($query) => $query->where('active', true))
                ->orderBy('position')
                ->limit(3)
                ->get()
            : collect();

        return view('pages.product', compact('product', 'relatedProducts'));
    }

    public function cart(): View
    {
        return view('pages.cart');
    }

    public function checkout(): View
    {
        return view('pages.checkout');
    }

    public function bookingConfirmation(Booking $booking): View
    {
        $booking->load([
            'customer',
            'items.variant.product',
        ]);

        return view('pages.booking-confirmation', compact('booking'));
    }
}
