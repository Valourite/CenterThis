<?php

use App\Actions\CreateBooking;
use App\Exceptions\InsufficientAvailabilityException;
use App\Services\CartQuoteService;
use App\Support\RentalCart;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $notes = '';

    public string $collectionDate = '';

    public string $returnDate = '';

    /** @var array<int, int> */
    public array $quantities = [];

    private RentalCart $cart;

    private CartQuoteService $quotes;

    private CreateBooking $createBooking;

    public function boot(
        RentalCart $cart,
        CartQuoteService $quotes,
        CreateBooking $createBooking,
    ): void {
        $this->cart = $cart;
        $this->quotes = $quotes;
        $this->createBooking = $createBooking;
    }

    public function mount(): void
    {
        $dates = $this->cart->dates();

        $this->quantities = $this->cart->items();
        $this->collectionDate = $dates['collection_date'] ?? '';
        $this->returnDate = $dates['return_date'] ?? '';

        if ($this->quantities === [] || $this->collectionDate === '' || $this->returnDate === '') {
            $this->redirectRoute('cart');
        }
    }

    #[Computed]
    public function quote(): array
    {
        return $this->quotes->quote(
            $this->quantities,
            $this->collectionDate,
            $this->returnDate,
        );
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'collectionDate' => ['required', 'date', 'after_or_equal:today'],
            'returnDate' => ['required', 'date', 'after_or_equal:collectionDate'],
        ]);

        $quote = $this->quote;

        if ($quote['missing_variant_ids'] !== []) {
            $this->addError('cart', 'One or more items are no longer offered. Return to your basket to review it.');

            return;
        }

        if (! $quote['all_available']) {
            $this->addError('cart', 'Availability changed before checkout. Return to your basket and adjust the dates or quantities.');

            return;
        }

        try {
            $booking = $this->createBooking->execute([
                'customer' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ],
                'collection_date' => $validated['collectionDate'],
                'return_date' => $validated['returnDate'],
                'items' => collect($this->quantities)
                    ->map(fn (int $quantity, int $variantId): array => [
                        'variant_id' => $variantId,
                        'quantity' => $quantity,
                    ])
                    ->values()
                    ->all(),
                'notes' => $validated['notes'] ?: null,
            ]);
        } catch (InsufficientAvailabilityException) {
            unset($this->quote);
            $this->addError('cart', 'Another booking took some of this stock. Return to your basket to review current availability.');

            return;
        }

        $this->cart->clear();
        $url = URL::temporarySignedRoute(
            'booking.confirmation',
            now()->addDay(),
            ['booking' => $booking->reference],
        );

        $this->redirect($url);
    }
};
?>

<div>
    <section class="bg-ink py-16 text-white sm:py-24">
        <div class="site-container">
            <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">Guest checkout</p>
            <h1 class="mt-4 max-w-5xl font-display text-6xl leading-[0.86] sm:text-8xl">Review the hire. <span class="italic text-primary-soft">Reserve the items.</span></h1>
            <p class="mt-6 max-w-2xl text-base leading-7 text-white/60">Submit your details to create the booking. We will contact you about collection arrangements; no payment is taken online.</p>
        </div>
    </section>

    <section class="py-12 sm:py-18">
        <div class="site-container grid gap-6 lg:grid-cols-[1fr_24rem] lg:items-start">
            <form wire:submit="submit" class="rounded-4xl bg-surface p-6 sm:p-9">
                <div>
                    <p class="text-xs font-bold tracking-[0.18em] text-primary uppercase">Customer details</p>
                    <h2 class="mt-3 font-display text-4xl">Who should we contact?</h2>
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <x-form.field label="Full name" name="name" label-class="text-black/60">
                        <input id="name" type="text" wire:model.blur="name" autocomplete="name" class="w-full rounded-xl border border-black/12 bg-canvas px-4 py-3 outline-none focus:border-primary" placeholder="e.g. Lerato Mokoena">
                    </x-form.field>
                    <x-form.field label="Email address" name="email" label-class="text-black/60">
                        <input id="email" type="email" wire:model.blur="email" autocomplete="email" class="w-full rounded-xl border border-black/12 bg-canvas px-4 py-3 outline-none focus:border-primary" placeholder="you@example.com">
                    </x-form.field>
                    <x-form.field label="Phone number" name="phone" label-class="text-black/60">
                        <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel" class="w-full rounded-xl border border-black/12 bg-canvas px-4 py-3 outline-none focus:border-primary" placeholder="+27">
                    </x-form.field>
                    <div class="rounded-xl bg-canvas p-4 text-sm leading-6 text-black/55">
                        <p class="font-bold text-ink">Hire dates</p>
                        <p class="mt-1">{{ \Illuminate\Support\Carbon::parse($collectionDate)->format('j M Y') }} to {{ \Illuminate\Support\Carbon::parse($returnDate)->format('j M Y') }}</p>
                        <a href="{{ route('cart') }}" class="mt-2 inline-block font-bold text-primary underline underline-offset-4">Change dates or quantities</a>
                    </div>
                </div>

                <div class="mt-5">
                    <x-form.field label="Collection notes (optional)" name="notes" label-class="text-black/60">
                        <textarea id="notes" wire:model.blur="notes" rows="5" class="w-full resize-none rounded-xl border border-black/12 bg-canvas px-4 py-3 outline-none focus:border-primary" placeholder="Anything we should know when arranging collection?"></textarea>
                    </x-form.field>
                </div>

                @error('cart')
                    <p class="mt-5 rounded-xl bg-danger/10 p-4 text-sm font-semibold text-danger">{{ $message }}</p>
                @enderror

                <div class="mt-7 flex flex-col gap-4 border-t border-black/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="max-w-md text-xs leading-5 text-black/45">Submitting creates a pending booking and reserves available stock for the full date range.</p>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex min-w-52 items-center justify-center rounded-full bg-primary px-6 py-3.5 text-sm font-bold text-white transition hover:bg-ink disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="submit">Submit booking</span>
                        <span wire:loading wire:target="submit">Reserving items...</span>
                    </button>
                </div>
            </form>

            <aside class="rounded-4xl bg-primary p-6 text-white lg:sticky lg:top-24 sm:p-8">
                <p class="text-xs font-bold tracking-[0.18em] text-primary-soft uppercase">Order summary</p>
                <div class="mt-5 divide-y divide-white/12">
                    @foreach ($this->quote['lines'] as $line)
                        <div wire:key="checkout-line-{{ $line['variant']->id }}" class="py-4">
                            <div class="flex justify-between gap-4">
                                <div>
                                    <p class="font-bold">{{ $line['variant']->product->name }}</p>
                                    <p class="mt-1 text-xs text-white/50">{{ $line['variant']->label }} · Qty {{ $line['quantity'] }}</p>
                                </div>
                                <p class="text-sm font-bold">R{{ number_format($line['breakdown']->rentalCents / 100, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-4 grid gap-4 border-t border-white/15 pt-5 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-white/55">Rental subtotal</dt>
                        <dd>R{{ number_format($this->quote['rental_cents'] / 100, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-white/55">Refundable deposit</dt>
                        <dd>R{{ number_format($this->quote['deposit_cents'] / 100, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-white/15 pt-4 text-base font-bold">
                        <dt>Total</dt>
                        <dd>R{{ number_format($this->quote['grand_total_cents'] / 100, 2) }}</dd>
                    </div>
                </dl>
                <p class="mt-5 text-xs leading-5 text-white/40">VAT-exclusive. The deposit is displayed separately and included in the total shown.</p>
            </aside>
        </div>
    </section>
</div>
