<x-layouts.public
    title="Services"
    description="Furniture, decor, linen, lighting, audio and bespoke item hire for weddings, private celebrations and corporate events across Gauteng."
>
    <section class="relative isolate overflow-hidden bg-ink py-20 text-white sm:py-28 lg:py-36">
        <div class="noise absolute inset-0 -z-10 opacity-10"></div>
        <div class="site-container grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">What we do</p>
                <h1 class="mt-5 font-display text-[clamp(4.5rem,10vw,9rem)] leading-[0.78]">The pieces for <span class="italic text-secondary">the occasion.</span></h1>
            </div>
            <p class="max-w-lg text-lg leading-8 text-white/60 lg:pb-3">Choose furniture, decor, linen, lighting, audio, and bespoke items from one hire collection, then arrange them to suit your own event plan.</p>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container grid gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative">
                <img src="{{ asset('images/events/wedding.webp') }}" alt="Wedding chairs, tables and ceremony decor" class="aspect-3/2 w-full rounded-4xl object-cover" loading="eager">
                <span class="absolute -bottom-5 left-6 rounded-full bg-accent px-5 py-3 text-xs font-bold tracking-[0.16em] uppercase">Wedding hire</span>
            </div>
            <div class="lg:pl-10">
                <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">01 · Weddings</p>
                <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">The practical pieces behind <span class="italic text-primary">the whole day.</span></h2>
                <p class="mt-6 text-base leading-7 text-black/60">Book the items needed for the ceremony, reception, dining areas, speeches, and evening celebration. Your planner, decorator, venue, or event team remains in control of the layout and setup.</p>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    @foreach (['Ceremony chairs', 'Reception tables', 'Table linen', 'Decor and plinths', 'Welcome signs', 'Festoon lighting', 'Audio equipment', 'Bespoke thank-you gifts'] as $item)
                        <div class="flex gap-3 rounded-xl bg-white/55 p-3 text-sm">
                            <span class="mt-1 size-2 shrink-0 rounded-full bg-primary"></span>
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-secondary py-20 sm:py-28">
        <div class="site-container grid gap-12 lg:grid-cols-2 lg:items-center">
            <div class="order-2 lg:order-1 lg:pr-10">
                <p class="text-xs font-bold tracking-[0.22em] uppercase">02 · Private events</p>
                <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">Hire what you need for <span class="italic text-primary">your kind of gathering.</span></h2>
                <p class="mt-6 text-base leading-7 text-black/60">Birthdays, showers, engagements, anniversaries, and intimate dinners all need a different mix of furniture and decor. Select only the pieces and quantities that fit your venue and guest list.</p>
                <div class="mt-8 grid gap-4">
                    @foreach ([
                        ['Birthday celebrations', 'Tables, chairs, plinths, linen, backdrops, and audio for milestone events and garden parties.'],
                        ['Bridal & baby showers', 'Furniture and decor pieces for dining, gifts, refreshments, and photo areas.'],
                        ['Engagements & intimate dinners', 'Long tables, chairs, linen, lighting, and personalised details for smaller guest lists.'],
                    ] as [$title, $copy])
                        <article class="border-t border-black/20 pt-4">
                            <h3 class="font-display text-3xl">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-black/55">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <img src="{{ asset('images/events/celebration.webp') }}" alt="Colourful birthday and shower tablescape" class="aspect-4/5 w-full rounded-4xl object-cover" loading="lazy">
            </div>
        </div>
    </section>

    <section class="bg-ink py-20 text-white sm:py-28">
        <div class="site-container grid gap-12 lg:grid-cols-2 lg:items-center">
            <img src="{{ asset('images/events/corporate.webp') }}" alt="Contemporary corporate product launch" class="aspect-3/2 w-full rounded-4xl object-cover" loading="lazy">
            <div class="lg:pl-10">
                <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">03 · Corporate events</p>
                <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">Reliable hire pieces for <span class="italic text-secondary">professional events.</span></h2>
                <p class="mt-6 text-base leading-7 text-white/60">Source matching furniture, presentation equipment, lighting, and practical event items for launches, conferences, dinners, and team functions without purchasing stock for a once-off event.</p>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach (['Product launches', 'Year-end functions', 'Awards evenings', 'Conferences', 'Executive dinners', 'Team celebrations', 'Brand activations'] as $item)
                        <span class="rounded-full border border-white/15 px-4 py-2 text-sm text-white/70">{{ $item }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container">
            <div class="grid gap-5 lg:grid-cols-2">
                <article class="relative overflow-hidden rounded-4xl bg-accent p-7 sm:p-10">
                    <p class="text-xs font-bold tracking-[0.22em] uppercase">Hire collection</p>
                    <h2 class="mt-5 max-w-xl font-display text-5xl leading-[0.92] sm:text-6xl">Tables, chairs, linen, lighting, audio, and the useful beautiful things.</h2>
                    <p class="mt-6 max-w-xl text-sm leading-6 text-black/60">Book individual pieces for your date range. Rates are shown per rental day, with availability checked against existing bookings.</p>
                    <a href="{{ route('catalogue') }}" class="mt-8 inline-flex items-center rounded-full bg-ink px-6 py-3.5 text-sm font-bold text-white">Browse hire pieces</a>
                    <div class="absolute -right-16 -bottom-16 size-48 rounded-full border-35 border-primary"></div>
                </article>

                <article class="rounded-4xl bg-primary p-7 text-white sm:p-10">
                    <p class="text-xs font-bold tracking-[0.22em] text-primary-soft uppercase">Bespoke details</p>
                    <h2 class="mt-5 max-w-xl font-display text-5xl leading-[0.92] sm:text-6xl">Made for this guest list, this story, this one day.</h2>
                    <p class="mt-6 max-w-xl text-sm leading-6 text-white/60">Laser-cut signage, personalised place names, cake toppers, welcome details, bridal party pieces, and thank-you gifts made to your supplied wording and requirements.</p>
                    <a href="{{ route('home') }}#inquire" class="mt-8 inline-flex items-center rounded-full bg-white px-6 py-3.5 text-sm font-bold text-ink">Ask about a custom piece</a>
                </article>
            </div>
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="site-container">
            <div class="border-t border-black/10 pt-16">
                <div class="grid gap-8 lg:grid-cols-[0.65fr_1.35fr]">
                    <div>
                        <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">How it works</p>
                        <h2 class="mt-4 font-display text-5xl leading-none sm:text-6xl">From item list to confirmed hire.</h2>
                    </div>
                    <div class="grid gap-px overflow-hidden rounded-[1.75rem] bg-black/10 sm:grid-cols-2">
                        @foreach ([
                            ['01', 'Choose the items', 'Browse the collection and decide which products and quantities your event requires.'],
                            ['02', 'Select the dates', 'Availability is checked across the entire hire window before the booking is accepted.'],
                            ['03', 'Confirm the booking', 'Provide the customer details, review the rental and deposit totals, and reserve the stock.'],
                            ['04', 'Collect and return', 'Collect the booked items by appointment and return them at the end of the agreed hire period.'],
                        ] as [$number, $title, $copy])
                            <article class="bg-surface p-6 sm:p-8">
                                <span class="font-display text-4xl italic text-primary/25">{{ $number }}</span>
                                <h3 class="mt-10 font-display text-3xl">{{ $title }}</h3>
                                <p class="mt-3 text-sm leading-6 text-black/55">{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-accent py-16 sm:py-20">
        <div class="site-container flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-end">
            <h2 class="max-w-4xl font-display text-5xl leading-[0.9] sm:text-7xl">Know the date? Start checking <span class="italic text-primary">the collection.</span></h2>
            <a href="{{ route('catalogue') }}" class="inline-flex shrink-0 rounded-full bg-ink px-6 py-3.5 text-sm font-bold text-white">Browse hire items</a>
        </div>
    </section>
</x-layouts.public>
