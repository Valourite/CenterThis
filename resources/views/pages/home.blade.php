<x-layouts.public description="Furniture, decor, linen, lighting and equipment hire for weddings, traditional ceremonies, celebrations and corporate events across Gauteng.">
    <section class="relative isolate min-h-[calc(100svh-4.5rem)] overflow-hidden bg-ink text-white">
        <img
            src="{{ asset('images/events/hero.webp') }}"
            alt="Event tables, chairs and decor at golden hour"
            class="absolute inset-0 -z-20 size-full object-cover object-[65%_center]"
            fetchpriority="high"
        >
        <div class="hero-overlay absolute inset-0 -z-10"></div>
        <div class="noise absolute inset-0 -z-10 opacity-15 mix-blend-soft-light"></div>

        <div class="site-container flex min-h-[calc(100svh-4.5rem)] flex-col justify-between gap-12 py-10 sm:py-14 lg:py-16">
            <div class="flex items-center gap-3">
                <span class="size-2 rounded-full bg-accent"></span>
                <p class="text-xs font-semibold tracking-[0.22em] text-white/70 uppercase">Gauteng event furniture & decor hire</p>
            </div>

            <div class="max-w-4xl">
                <p class="mb-5 font-display text-xl italic text-secondary sm:text-2xl">Gather beautifully.</p>
                <h1 class="font-display text-[clamp(4.2rem,10vw,9.5rem)] leading-[0.78] text-balance">
                    Events worth <span class="text-accent italic">gathering</span> for.
                </h1>
                <p class="mt-8 max-w-xl text-base leading-7 text-white/72 sm:text-lg">
                    Hire the furniture, decor, linen, lighting, and practical equipment you need to bring your own event plan to life.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('catalogue') }}" class="group inline-flex items-center justify-center gap-3 rounded-full bg-accent px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-white">
                        Browse hire items
                        <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="{{ route('portfolio') }}" class="inline-flex items-center justify-center rounded-full border border-white/30 px-6 py-3.5 text-sm font-bold text-white backdrop-blur transition hover:border-white hover:bg-white hover:text-ink">
                        See our pieces in use
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-5 border-t border-white/20 pt-5 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between">
                <p>Weddings · Traditional ceremonies · Private celebrations · Corporate events</p>
                <a href="#offer" class="inline-flex items-center gap-2 font-semibold text-white">
                    Scroll to explore
                    <svg class="size-4 animate-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 4v15m0 0 6-6m-6 6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <div class="overflow-hidden border-y border-black/10 bg-secondary py-4 text-ink">
        <div class="marquee-track flex w-max">
            @for ($i = 0; $i < 2; $i++)
                <div class="flex shrink-0 items-center">
                    @foreach (['Weddings', 'Traditional ceremonies', 'Birthday parties', 'Bridal showers', 'Corporate launches', 'Bespoke gifts', 'Furniture hire'] as $event)
                        <span class="px-5 font-display text-2xl italic sm:px-8 sm:text-3xl">{{ $event }}</span>
                        <span class="size-2 rounded-full bg-primary"></span>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    <section id="offer" class="py-20 sm:py-28">
        <div class="site-container">
            <div class="grid gap-8 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">What we bring</p>
                    <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-6xl">One collection.<br><span class="italic text-primary">Every occasion.</span></h2>
                </div>
                <p class="max-w-2xl text-base leading-7 text-black/60 lg:justify-self-end lg:text-lg">
                    Build your event from a practical, carefully selected hire collection. Choose the pieces you need, reserve them for your dates, and arrange them your way.
                </p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['01', 'Furniture hire', 'Tables, chairs, lounge pieces, and practical foundations for events of every size.', 'var(--color-primary)', 'text-white'],
                    ['02', 'Decor hire', 'Backdrops, plinths, table decor, signage pieces, and accents ready for your team to arrange.', 'var(--color-accent)', 'text-ink'],
                    ['03', 'Linen & equipment', 'Tablecloths, lighting, audio, and useful pieces booked for your exact date range.', 'var(--color-secondary)', 'text-ink'],
                    ['04', 'Bespoke items', 'Laser-cut names, custom gifts, and personalised details made for your event.', 'var(--color-ink)', 'text-white'],
                ] as [$number, $title, $copy, $colour, $textColour])
                    <article class="group flex min-h-80 flex-col justify-between overflow-hidden rounded-[1.75rem] p-6 transition duration-500 hover:-translate-y-2" style="background-color: {{ $colour }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-[0.18em] opacity-55">{{ $number }}</span>
                            <span class="flex size-10 items-center justify-center rounded-full border border-current/20 transition group-hover:rotate-45">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M7 17 17 7M8 7h9v9" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <div class="{{ $textColour }}">
                            <h3 class="font-display text-4xl leading-none">{{ $title }}</h3>
                            <p class="mt-4 text-sm leading-6 opacity-65">{{ $copy }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-ink py-20 text-white sm:py-28">
        <div class="site-container">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">Every reason to gather</p>
                    <h2 class="mt-4 max-w-3xl font-display text-5xl leading-[0.9] sm:text-7xl">Different moments.<br><span class="italic text-secondary">Same obsession.</span></h2>
                </div>
                <a href="{{ route('services') }}" class="group inline-flex items-center gap-2 text-sm font-bold">
                    Explore what we hire
                    <span class="flex size-9 items-center justify-center rounded-full border border-white/25 transition group-hover:bg-white group-hover:text-ink">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            </div>

            <div class="mt-12 grid gap-5 lg:grid-cols-12">
                <article class="event-card relative min-h-136 overflow-hidden rounded-4xl lg:col-span-7">
                    <img src="{{ asset('images/events/wedding.webp') }}" alt="Wedding furniture and ceremony decor" class="absolute inset-0 size-full object-cover transition duration-700 hover:scale-105" loading="lazy">
                    <div class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-8">
                        <p class="text-xs font-bold tracking-[0.2em] text-accent uppercase">Weddings</p>
                        <h3 class="mt-3 max-w-xl font-display text-5xl leading-none sm:text-6xl">The pieces for every part of the day.</h3>
                    </div>
                </article>

                <article class="event-card relative min-h-136 overflow-hidden rounded-4xl lg:col-span-5">
                    <img src="{{ asset('images/events/traditional.webp') }}" alt="A richly layered traditional ceremony lounge" class="absolute inset-0 size-full object-cover transition duration-700 hover:scale-105" loading="lazy">
                    <div class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-8">
                        <p class="text-xs font-bold tracking-[0.2em] text-accent uppercase">Traditional ceremonies</p>
                        <h3 class="mt-3 font-display text-5xl leading-none">Furniture and decor that honour the occasion.</h3>
                    </div>
                </article>

                <article class="event-card relative min-h-120 overflow-hidden rounded-4xl lg:col-span-5">
                    <img src="{{ asset('images/events/celebration.webp') }}" alt="A colourful private celebration tablescape" class="absolute inset-0 size-full object-cover transition duration-700 hover:scale-105" loading="lazy">
                    <div class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-8">
                        <p class="text-xs font-bold tracking-[0.2em] text-accent uppercase">Private celebrations</p>
                        <h3 class="mt-3 font-display text-5xl leading-none">Big energy for your people.</h3>
                    </div>
                </article>

                <article class="event-card relative min-h-120 overflow-hidden rounded-4xl lg:col-span-7">
                    <img src="{{ asset('images/events/corporate.webp') }}" alt="Furniture and equipment used at a corporate event" class="absolute inset-0 size-full object-cover transition duration-700 hover:scale-105" loading="lazy">
                    <div class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-8">
                        <p class="text-xs font-bold tracking-[0.2em] text-accent uppercase">Corporate events</p>
                        <h3 class="mt-3 max-w-xl font-display text-5xl leading-none sm:text-6xl">Professional does not have to mean plain.</h3>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container">
            <div class="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">The hire collection</p>
                    <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">Good bones for <span class="italic text-primary">great events.</span></h2>
                </div>
                <div class="lg:justify-self-end">
                    <p class="max-w-xl text-base leading-7 text-black/60">Browse furniture, linen, lighting, audio, and decor, then reserve the quantities you need for your event dates.</p>
                    <a href="{{ route('catalogue') }}" class="mt-6 inline-flex items-center gap-3 rounded-full bg-ink px-6 py-3.5 text-sm font-bold text-white transition hover:bg-primary">
                        Browse the hire collection
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="mt-12 overflow-hidden rounded-4xl bg-surface-muted">
                <div class="grid lg:grid-cols-2">
                    <img src="{{ asset('images/events/hire-collection.webp') }}" alt="A collection of event furniture and decor for hire" class="h-full min-h-96 w-full object-cover" loading="lazy">
                    <div class="grid gap-px bg-black/10 sm:grid-cols-2">
                        @forelse ($featuredProducts as $product)
                            @php($firstVariant = $product->variants->first())
                            <article class="flex min-h-64 flex-col justify-between bg-surface p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="rounded-full bg-canvas px-3 py-1 text-[0.65rem] font-bold tracking-[0.14em] text-black/50 uppercase">{{ $product->category?->name }}</span>
                                    <span class="font-display text-4xl italic text-primary/20">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-display text-3xl leading-none">{{ $product->name }}</h3>
                                    @if ($firstVariant)
                                        <p class="mt-3 text-sm text-black/50">From <span class="font-bold text-ink">R{{ number_format((float) $firstVariant->base_rate, 2) }}</span> / day</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="flex min-h-64 items-end bg-surface p-6 sm:col-span-2">
                                <p class="max-w-md font-display text-3xl">Our hire collection is being arranged. Check back shortly.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden bg-primary py-20 text-white sm:py-28">
        <div class="site-container">
            <div class="grid gap-12 lg:grid-cols-[0.55fr_1.45fr]">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-primary-soft uppercase">Kind words</p>
                    <h2 class="mt-4 font-display text-5xl leading-none sm:text-6xl">The morning-after messages we keep.</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ([
                        ['“The chairs and linen were exactly as described, and everything we needed was reserved for our dates.”', 'Naledi & Kabelo', 'Wedding · Pretoria'],
                        ['“The furniture gave our launch the polished look we wanted without buying pieces we would only use once.”', 'Kgomotso M.', 'Product launch · Johannesburg'],
                        ['“Booking the tables and linen was straightforward, and the team helped us understand the available options.”', 'Ayesha K.', 'Bridal shower · Centurion'],
                    ] as [$quote, $name, $event])
                        <figure class="flex min-h-80 flex-col justify-between rounded-3xl border border-white/15 bg-white/6 p-6">
                            <blockquote class="font-display text-2xl leading-snug">{{ $quote }}</blockquote>
                            <figcaption>
                                <p class="text-sm font-bold">{{ $name }}</p>
                                <p class="mt-1 text-xs text-white/45">{{ $event }}</p>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container">
            <div class="grid gap-5 lg:grid-cols-3">
                <div class="relative overflow-hidden rounded-4xl bg-accent p-7 sm:p-9 lg:col-span-2">
                    <div class="relative z-10 max-w-2xl">
                        <p class="text-xs font-bold tracking-[0.22em] uppercase">Areas we serve</p>
                        <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">Your venue, anywhere in <span class="italic text-primary">Gauteng.</span></h2>
                        <p class="mt-6 max-w-xl text-base leading-7 text-black/60">Pretoria, Johannesburg, Centurion, Midrand, and the surrounding areas. Tell us where the gathering is and we will confirm logistics in your quote.</p>
                    </div>
                    <div class="float-slow absolute -right-12 -bottom-20 flex size-64 items-center justify-center rounded-full border-40 border-primary sm:-right-8 sm:size-80"></div>
                </div>

                <div class="flex flex-col justify-between rounded-4xl bg-secondary p-7 sm:p-9">
                    <div class="flex size-12 items-center justify-center rounded-full bg-ink text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 6.5 12 13l8-6.5M5.5 19h13a1.5 1.5 0 0 0 1.5-1.5v-11A1.5 1.5 0 0 0 18.5 5h-13A1.5 1.5 0 0 0 4 6.5v11A1.5 1.5 0 0 0 5.5 19Z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="mt-16">
                        <p class="text-xs font-bold tracking-[0.2em] uppercase">Prefer email?</p>
                        <a href="mailto:admin@centerthis.co.za" class="mt-3 block wrap-break-word font-display text-3xl leading-none underline decoration-1 underline-offset-8">admin@centerthis.co.za</a>
                        <p class="mt-5 text-sm leading-6 text-black/55">Studio visits and collection by appointment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="inquire" class="scroll-mt-24 pb-20 sm:pb-28">
        <div class="site-container">
            <livewire:inquiry-form />
        </div>
    </section>
</x-layouts.public>
