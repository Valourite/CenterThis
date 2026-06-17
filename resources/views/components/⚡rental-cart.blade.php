<?php

use App\Models\Variant;
use App\Services\CartQuoteService;
use App\Support\RentalCart;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $collectionDate = '';

    public string $returnDate = '';

    /** @var array<int, int> */
    public array $quantities = [];

    private RentalCart $cart;

    private CartQuoteService $quotes;

    public function boot(RentalCart $cart, CartQuoteService $quotes): void
    {
        $this->cart = $cart;
        $this->quotes = $quotes;
    }

    public function mount(): void
    {
        $dates = $this->cart->dates();

        $this->collectionDate = $dates['collection_date'] ?? '';
        $this->returnDate = $dates['return_date'] ?? '';
        $this->quantities = $this->cart->items();
    }

    #[Computed]
    public function variants(): Collection
    {
        return Variant::query()
            ->select([
                'id',
                'product_id',
                'label',
                'quantity',
                'base_rate',
                'deposit_amount',
            ])
            ->with('product:id,name')
            ->whereKey(array_keys($this->quantities))
            ->where('active', true)
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->get()
            ->sortBy(fn (Variant $variant): int => array_search($variant->id, array_keys($this->quantities), true))
            ->values();
    }

    #[Computed]
    public function quote(): ?array
    {
        $window = $this->dateWindow();

        if ($window === null || $this->quantities === []) {
            return null;
        }

        return $this->quotes->quote(
            $this->quantities,
            $window['collection'],
            $window['return'],
        );
    }

    public function updatedCollectionDate(): void
    {
        unset($this->quote);
        $this->resetValidation(['collectionDate', 'returnDate', 'cart']);
    }

    public function updatedReturnDate(): void
    {
        unset($this->quote);
        $this->resetValidation(['collectionDate', 'returnDate', 'cart']);
    }

    public function updateQuantity(int $variantId): void
    {
        $variant = Variant::query()
            ->where('active', true)
            ->whereHas('product', fn ($query) => $query->where('active', true))
            ->find($variantId);

        if ($variant === null) {
            $this->remove($variantId);

            return;
        }

        $quantity = (int) ($this->quantities[$variantId] ?? 0);
        $errorKey = "quantities.{$variantId}";

        if ($quantity < 1 || $quantity > $variant->quantity) {
            $this->addError($errorKey, "Choose between 1 and {$variant->quantity}.");

            return;
        }

        $this->resetValidation([$errorKey, 'cart']);
        $this->quantities[$variantId] = $quantity;
        $this->cart->update($variantId, $quantity);
        unset($this->quote);

        $this->dispatch('cart-updated', count: $this->cart->count());
    }

    public function remove(int $variantId): void
    {
        $this->cart->remove($variantId);
        unset($this->quantities[$variantId]);
        unset($this->variants, $this->quote);
        $this->resetValidation();

        $this->dispatch('cart-updated', count: $this->cart->count());
    }

    public function clear(): void
    {
        $this->cart->clear();
        $this->quantities = [];
        $this->collectionDate = '';
        $this->returnDate = '';
        unset($this->variants, $this->quote);
        $this->resetValidation();

        $this->dispatch('cart-updated', count: 0);
    }

    public function proceedToCheckout(): void
    {
        $validated = $this->validate([
            'collectionDate' => ['required', 'date', 'after_or_equal:today'],
            'returnDate' => ['required', 'date', 'after_or_equal:collectionDate'],
        ]);

        if ($this->quantities === []) {
            $this->addError('cart', 'Add at least one item before continuing.');

            return;
        }

        $this->cart->setDates($validated['collectionDate'], $validated['returnDate']);
        unset($this->quote);

        $quote = $this->quote;

        if ($quote['missing_variant_ids'] !== []) {
            foreach ($quote['missing_variant_ids'] as $variantId) {
                $this->remove($variantId);
            }

            $this->addError('cart', 'One or more items are no longer available in the hire collection.');

            return;
        }

        if (! $quote['all_available']) {
            $this->addError('cart', 'Adjust the quantities or dates for items marked unavailable.');

            return;
        }

        $this->redirectRoute('checkout');
    }

    public function money(int $cents): string
    {
        return 'R'.number_format($cents / 100, 2);
    }

    /**
     * @return array{collection: CarbonImmutable, return: CarbonImmutable}|null
     */
    private function dateWindow(): ?array
    {
        if ($this->collectionDate === '' || $this->returnDate === '') {
            return null;
        }

        try {
            $collectionDate = CarbonImmutable::parse($this->collectionDate)->startOfDay();
            $returnDate = CarbonImmutable::parse($this->returnDate)->startOfDay();
        } catch (Throwable) {
            return null;
        }

        if ($collectionDate->isBefore(today()) || $returnDate->isBefore($collectionDate)) {
            return null;
        }

        return [
            'collection' => $collectionDate,
            'return' => $returnDate,
        ];
    }
};
?>

<div>
    <section class="bg-ink py-16 text-white sm:py-24">
        <div class="site-container flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">Your hire basket</p>
                <h1 class="mt-4 font-display text-6xl leading-[0.86] sm:text-8xl">Choose the dates. <span class="italic text-primary-soft">Check the stock.</span></h1>
            </div>
            @if ($quantities !== [])
                <button type="button" wire:click="clear" wire:confirm="Remove every item from your hire basket?" class="text-sm font-semibold text-white/55 underline underline-offset-4 transition hover:text-white">
                    Clear basket
                </button>
            @endif
        </div>
    </section>

    <section class="py-12 sm:py-18">
        <div class="site-container">
            @if ($quantities === [])
                <div class="rounded-4xl bg-surface p-8 text-center sm:p-16">
                    <p class="font-display text-5xl">Your hire basket is empty.</p>
                    <p class="mx-auto mt-4 max-w-lg text-black/55">Browse the collection and add the furniture, decor, linen, or equipment you need.</p>
                    <a href="{{ route('catalogue') }}" class="mt-8 inline-flex rounded-full bg-primary px-6 py-3.5 text-sm font-bold text-white">Browse hire items</a>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-[1fr_24rem] lg:items-start">
                    <div class="grid gap-4">
                        @foreach ($this->variants as $variant)
                            @php($quotedLine = $this->quote ? collect($this->quote['lines'])->first(fn (array $line) => $line['variant']->id === $variant->id) : null)
                            <article wire:key="cart-variant-{{ $variant->id }}" class="rounded-3xl border border-black/8 bg-surface p-5 sm:p-6">
                                <div class="grid gap-5 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div>
                                        <p class="text-xs font-bold tracking-[0.16em] text-primary uppercase">{{ $variant->product->name }}</p>
                                        <h2 class="mt-2 font-display text-3xl">{{ $variant->label }}</h2>
                                        <p class="mt-3 text-sm text-black/50">
                                            R{{ number_format((float) $variant->base_rate, 2) }} per day
                                            <span class="px-1">·</span>
                                            R{{ number_format((float) $variant->deposit_amount, 2) }} refundable deposit each
                                        </p>
                                        @if ($quotedLine)
                                            <p @class([
                                                'mt-3 text-sm font-bold',
                                                'text-primary' => $quotedLine['available'] >= $quotedLine['quantity'],
                                                'text-danger' => $quotedLine['available'] < $quotedLine['quantity'],
                                            ])>
                                                @if ($quotedLine['available'] >= $quotedLine['quantity'])
                                                    Available for your dates
                                                @else
                                                    Only {{ $quotedLine['available'] }} available for your dates
                                                @endif
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label for="quantity-{{ $variant->id }}" class="sr-only">Quantity for {{ $variant->product->name }} {{ $variant->label }}</label>
                                        <input
                                            id="quantity-{{ $variant->id }}"
                                            type="number"
                                            min="1"
                                            max="{{ $variant->quantity }}"
                                            wire:model.blur="quantities.{{ $variant->id }}"
                                            wire:change="updateQuantity({{ $variant->id }})"
                                            class="w-20 rounded-full border border-black/12 bg-canvas px-4 py-2.5 text-center text-sm font-bold outline-none focus:border-primary"
                                        >
                                        <button type="button" wire:click="remove({{ $variant->id }})" class="rounded-full border border-black/10 px-4 py-2.5 text-sm font-semibold text-black/55 transition hover:border-danger hover:text-danger">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                @error("quantities.{$variant->id}")
                                    <p class="mt-3 text-sm font-semibold text-danger">{{ $message }}</p>
                                @enderror

                                @if ($quotedLine)
                                    <div class="mt-5 rounded-2xl bg-canvas p-4">
                                        <p class="text-xs font-bold tracking-[0.14em] text-black/45 uppercase">Price breakdown</p>
                                        <dl class="mt-3 grid gap-2 text-sm">
                                            <div class="flex justify-between gap-4">
                                                <dt class="text-black/55">
                                                    Base hire
                                                    <span class="text-black/35">· {{ $quotedLine['quantity'] }} × {{ $quotedLine['days'] }} {{ $quotedLine['days'] === 1 ? 'day' : 'days' }}</span>
                                                </dt>
                                                <dd class="font-semibold">{{ $this->money($quotedLine['breakdown']->baseRentalCents) }}</dd>
                                            </div>

                                            @forelse ($quotedLine['breakdown']->adjustments as $adjustment)
                                                <div class="flex justify-between gap-4">
                                                    <dt class="text-black/55">{{ $adjustment['label'] }}</dt>
                                                    <dd @class([
                                                        'font-semibold',
                                                        'text-danger' => $adjustment['cents'] > 0,
                                                        'text-primary' => $adjustment['cents'] < 0,
                                                        'text-black/45' => $adjustment['cents'] === 0,
                                                    ])>
                                                        {{ $adjustment['cents'] < 0 ? '-' : '+' }}{{ $this->money(abs($adjustment['cents'])) }}
                                                    </dd>
                                                </div>
                                            @empty
                                                <div class="flex justify-between gap-4">
                                                    <dt class="text-black/55">Pricing rules</dt>
                                                    <dd class="font-semibold text-black/45">None applied</dd>
                                                </div>
                                            @endforelse

                                            <div class="flex justify-between gap-4 border-t border-black/10 pt-2">
                                                <dt class="font-semibold">Hire total</dt>
                                                <dd class="font-bold">{{ $this->money($quotedLine['breakdown']->rentalCents) }}</dd>
                                            </div>
                                            <div class="flex justify-between gap-4">
                                                <dt class="text-black/55">Refundable deposit</dt>
                                                <dd class="font-semibold">{{ $this->money($quotedLine['breakdown']->depositCents) }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    <aside class="rounded-4xl bg-primary p-6 text-white lg:sticky lg:top-24 sm:p-8">
                        <p class="text-xs font-bold tracking-[0.18em] text-primary-soft uppercase">Availability & pricing</p>
                        <div class="mt-6 grid gap-4">
                            <div>
                                <label for="collectionDate" class="mb-2 block text-xs font-semibold text-white/65">Collection date</label>
                                <input id="collectionDate" type="date" min="{{ now()->toDateString() }}" wire:model.live="collectionDate" class="w-full rounded-xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none focus:border-accent">
                                @error('collectionDate') <p class="mt-2 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="returnDate" class="mb-2 block text-xs font-semibold text-white/65">Return date</label>
                                <input id="returnDate" type="date" min="{{ $collectionDate ?: now()->toDateString() }}" wire:model.live="returnDate" class="w-full rounded-xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none focus:border-accent">
                                @error('returnDate') <p class="mt-2 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        @if ($this->quote)
                            <dl class="mt-7 grid gap-4 border-t border-white/15 pt-6 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-white/55">Base hire</dt>
                                    <dd>{{ $this->money($this->quote['base_rental_cents']) }}</dd>
                                </div>

                                @foreach ($this->quote['adjustments'] as $adjustment)
                                    <div wire:key="cart-summary-adjustment-{{ $loop->index }}-{{ str($adjustment['label'])->slug() }}" class="flex justify-between gap-4">
                                        <dt class="text-white/55">{{ $adjustment['label'] }}</dt>
                                        <dd @class([
                                            'font-semibold',
                                            'text-danger' => $adjustment['cents'] > 0,
                                            'text-primary-soft' => $adjustment['cents'] < 0,
                                            'text-white/45' => $adjustment['cents'] === 0,
                                        ])>
                                            {{ $adjustment['cents'] < 0 ? '-' : '+' }}{{ $this->money(abs($adjustment['cents'])) }}
                                        </dd>
                                    </div>
                                @endforeach

                                <div class="flex justify-between gap-4 border-t border-white/15 pt-4">
                                    <dt class="text-white/55">Hire total</dt>
                                    <dd>{{ $this->money($this->quote['rental_cents']) }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-white/55">Refundable deposit</dt>
                                    <dd>{{ $this->money($this->quote['deposit_cents']) }}</dd>
                                </div>
                                <div class="flex justify-between gap-4 border-t border-white/15 pt-4 text-base font-bold">
                                    <dt>Total</dt>
                                    <dd>{{ $this->money($this->quote['grand_total_cents']) }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="mt-6 text-sm leading-6 text-white/55">Select a valid collection and return date to see live availability and totals.</p>
                        @endif

                        @error('cart')
                            <p class="mt-5 rounded-xl bg-white/10 p-3 text-sm font-semibold text-danger">{{ $message }}</p>
                        @enderror

                        <button type="button" wire:click="proceedToCheckout" wire:loading.attr="disabled" class="mt-7 inline-flex w-full items-center justify-center rounded-full bg-accent px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-white disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="proceedToCheckout">Continue to checkout</span>
                            <span wire:loading wire:target="proceedToCheckout">Checking availability...</span>
                        </button>
                        <p class="mt-4 text-xs leading-5 text-white/40">VAT-exclusive. No online payment is taken when you submit your booking.</p>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</div>
