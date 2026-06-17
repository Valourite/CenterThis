<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Variant;
use App\Support\RentalCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CartItemController extends Controller
{
    public function store(StoreCartItemRequest $request, RentalCart $cart): RedirectResponse
    {
        $validated = $request->validated();
        $variant = Variant::query()
            ->with('product:id,name,active')
            ->where('active', true)
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->whereKey((int) $validated['variant_id'])
            ->firstOrFail();
        $requestedQuantity = $cart->quantity($variant->id) + $validated['quantity'];

        if ($requestedQuantity > $variant->quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$variant->quantity} units can be added for this item.",
            ]);
        }

        $cart->add($variant->id, $validated['quantity']);

        return back()->with(
            'cart_status',
            "{$variant->product->name} ({$variant->label}) was added to your hire basket.",
        );
    }
}
