<x-layouts.public title="Booking {{ $booking->reference }}"
    description="Your CenterThis hire booking has been received.">
    <section class="bg-primary py-16 text-white sm:py-24">
        <div class="site-container">
            <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">Booking received</p>
            <h1 class="mt-4 max-w-4xl font-display text-6xl leading-[0.86] sm:text-8xl">
                Your items are <span class="italic text-primary-soft">reserved.</span>
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-7 text-white/65">
                We have received booking {{ $booking->reference }} and will contact you to confirm collection
                arrangements. No online payment has been taken.
            </p>
        </div>
    </section>

    <section class="py-14 sm:py-20">
        <div class="site-container grid gap-6 lg:grid-cols-[1fr_22rem]">
            <div class="rounded-4xl bg-surface p-6 sm:p-9">
                <div
                    class="flex flex-col gap-3 border-b border-black/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold tracking-[0.18em] text-primary uppercase">Reference</p>
                        <h2 class="mt-2 font-display text-4xl">{{ $booking->reference }}</h2>
                    </div>
                    <p class="text-sm text-black/55">
                        {{ $booking->collection_date->format('j M Y') }} to {{ $booking->return_date->format('j M Y') }}
                    </p>
                </div>

                <div class="divide-y divide-black/8">
                    @foreach ($booking->items as $item)
                        <div class="flex items-start justify-between gap-5 py-5">
                            <div>
                                <p class="font-bold">{{ $item->variant->product->name }}</p>
                                <p class="mt-1 text-sm text-black/50">{{ $item->variant->label }} · Qty
                                    {{ $item->quantity }}</p>
                            </div>
                            <p class="font-bold">R{{ number_format((float) $item->line_total, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="rounded-4xl bg-ink p-6 text-white sm:p-8">
                <p class="text-xs font-bold tracking-[0.18em] text-accent uppercase">Booking total</p>
                <dl class="mt-6 grid gap-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-white/55">Rental subtotal</dt>
                        <dd>R{{ number_format((float) $booking->rental_subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-white/55">Refundable deposit</dt>
                        <dd>R{{ number_format((float) $booking->deposit_total, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-white/15 pt-4 text-base font-bold">
                        <dt>Total</dt>
                        <dd>R{{ number_format((float) $booking->grand_total, 2) }}</dd>
                    </div>
                </dl>
                <p class="mt-6 text-xs leading-5 text-white/45">VAT-exclusive. The deposit is refundable subject to the
                    hire terms and condition of returned items.</p>
                <a href="{{ route('catalogue') }}"
                    class="mt-8 inline-flex w-full items-center justify-center rounded-full bg-accent px-5 py-3 text-sm font-bold text-ink">
                    Return to the collection
                </a>
            </aside>
        </div>
    </section>
</x-layouts.public>