<x-layouts.public
    title="About"
    description="Meet CenterThis, a Gauteng furniture, decor and event equipment hire company serving celebrations of every size."
>
    <section class="relative overflow-hidden py-20 sm:py-28 lg:py-36">
        <div class="site-container relative z-10 grid gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">The CenterThis story</p>
                <h1 class="mt-5 max-w-5xl font-display text-[clamp(4.5rem,10vw,9rem)] leading-[0.78]">We make space for <span class="italic text-primary">meaning.</span></h1>
            </div>
            <div class="max-w-lg lg:pb-3">
                <p class="text-lg leading-8 text-black/60">CenterThis is an event hire company built around a simple idea: people should be able to access beautiful, practical event pieces without having to buy and store them.</p>
                <a href="#story" class="mt-7 inline-flex items-center gap-3 text-sm font-bold">
                    Read our story
                    <span class="flex size-9 items-center justify-center rounded-full bg-ink text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 4v15m0 0 6-6m-6 6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
        <div class="absolute -top-28 right-[8%] z-0 size-80 rounded-full border-70 border-accent/70"></div>
    </section>

    <section id="story" class="scroll-mt-24 bg-ink py-20 text-white sm:py-28">
        <div class="site-container grid gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative">
                <img src="{{ asset('images/events/traditional.webp') }}" alt="Event lounge furniture and decor" class="aspect-4/5 w-full rounded-4xl object-cover" loading="eager">
                <div class="absolute -right-3 -bottom-6 max-w-56 rounded-3xl bg-accent p-5 text-ink sm:-right-6">
                    <p class="font-display text-3xl leading-none">Personal first.<br><span class="italic text-primary">Always.</span></p>
                </div>
            </div>

            <div class="lg:pl-10">
                <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">How it started</p>
                <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">A love for the little things became a studio for the <span class="italic text-secondary">big things.</span></h2>
                <div class="mt-8 grid gap-5 text-base leading-7 text-white/60">
                    <p>CenterThis began with intimate family celebrations, handmade details, borrowed tables, and the realisation that good event pieces were difficult to source in one place. One event led to another, and a useful, distinctive hire collection started to grow.</p>
                    <p>Today, we supply furniture, decor, linen, lighting, audio equipment, and bespoke keepsakes for events throughout Gauteng. Customers choose the pieces that suit their plans and book them for the dates they need.</p>
                    <p>We focus on maintaining a dependable collection, clear availability, and helpful product guidance. Decorating and setup remain in the hands of the customer, venue, planner, or decorator.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">Signature events</p>
                    <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">The moments we know <span class="italic text-primary">by heart.</span></h2>
                </div>
                <p class="max-w-2xl text-base leading-7 text-black/60 lg:justify-self-end">Furniture, decor, equipment, and one-off personalised details for the celebrations our customers plan and create.</p>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden rounded-4xl bg-black/10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Weddings', 'Ceremony chairs, reception tables, linen, lighting, signage pieces, and decor accents.'],
                    ['Traditional ceremonies', 'Furniture and decor pieces suited to meaningful family and cultural celebrations.'],
                    ['Birthday parties', 'Tables, chairs, plinths, backdrops, linen, and equipment for milestone celebrations.'],
                    ['Tables, chairs & linen', 'A practical hire collection selected to work hard and photograph beautifully.'],
                    ['Bespoke laser-cut items', 'Place names, signs, cake toppers, keepsakes, and personalised finishing touches.'],
                    ['Bridal showers', 'Tables, chairs, linen, plinths, and decor pieces for intimate gatherings.'],
                    ['Baby showers', 'Practical hire items and decor accents for welcoming a new arrival.'],
                    ['Engagement parties', 'Furniture, lighting, linen, and decor for the first celebration of the next chapter.'],
                    ['Thank-you gifts', 'Personalised gifts for bridal parties, hosts, teams, and the people who showed up.'],
                ] as $item)
                    <article class="group min-h-64 bg-surface p-6 transition hover:bg-accent sm:p-8">
                        <div class="flex h-full flex-col justify-between">
                            <span class="font-display text-5xl italic text-primary/20">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3 class="font-display text-3xl leading-none">{{ $item[0] }}</h3>
                                <p class="mt-4 text-sm leading-6 text-black/55">{{ $item[1] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-primary py-20 text-white sm:py-28">
        <div class="site-container">
            <div class="grid gap-12 lg:grid-cols-[0.75fr_1.25fr]">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-primary-soft uppercase">Why CenterThis</p>
                    <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">Calm hands. Sharp eyes. <span class="italic text-accent">Good energy.</span></h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['A useful range in one place', 'Furniture, linen, decor, lighting, audio, and bespoke pieces can be booked together.'],
                        ['Availability you can trust', 'Stock is reserved for your full date range so another booking cannot claim the same units.'],
                        ['Practical, event-ready pieces', 'Our collection is selected for real use, guest comfort, and a strong finished result.'],
                        ['Local Gauteng service', 'We understand event dates, collection planning, and hire logistics across the province.'],
                    ] as [$title, $copy])
                        <article class="rounded-3xl border border-white/15 bg-white/6 p-6">
                            <div class="mb-10 flex size-10 items-center justify-center rounded-full bg-accent font-bold text-ink">{{ $loop->iteration }}</div>
                            <h3 class="font-display text-3xl leading-none">{{ $title }}</h3>
                            <p class="mt-4 text-sm leading-6 text-white/55">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container grid gap-12 lg:grid-cols-2">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">Areas we serve</p>
                <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">From Pretoria to Joburg, and <span class="italic text-primary">everywhere between.</span></h2>
                <p class="mt-7 max-w-xl text-base leading-7 text-black/60">We work throughout Gauteng, including Pretoria, Johannesburg, Centurion, Midrand, and surrounding areas. Travel and collection logistics are confirmed per event.</p>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach (['Pretoria', 'Johannesburg', 'Centurion', 'Midrand', 'East Rand', 'West Rand', 'Surrounding Gauteng'] as $area)
                        <span class="rounded-full border border-black/15 bg-white/50 px-4 py-2 text-sm font-semibold">{{ $area }}</span>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">Questions, answered</p>
                <div class="mt-5 divide-y divide-black/10 border-y border-black/10">
                    @foreach ([
                        ['How far in advance should we book?', 'Book as early as possible for popular dates and larger quantities. Short-notice bookings may still be available because stock is checked against your exact date range.'],
                        ['Can we hire only a few items?', 'Yes. You can book individual item types or combine furniture, linen, decor, lighting, and audio in one booking.'],
                        ['Do you decorate or set up the venue?', 'No. CenterThis supplies the booked hire items. Decorating, arranging, setup, and breakdown are handled by you, your venue, planner, or decorator.'],
                        ['Can our planner or decorator collect and use the items?', 'Yes. Your appointed event professional can coordinate the hired items with you, subject to the booking and collection arrangements.'],
                        ['How do we know what is available?', 'Availability is checked for your selected dates and quantities before the booking is completed.'],
                    ] as [$question, $answer])
                        <details class="group py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-5 font-semibold">
                                {{ $question }}
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-ink text-white transition group-open:rotate-45">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </summary>
                            <p class="max-w-xl pt-4 text-sm leading-6 text-black/55">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="site-container">
            <div class="flex flex-col items-start justify-between gap-8 rounded-4xl bg-accent p-7 sm:p-10 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] uppercase">Your turn</p>
                    <h2 class="mt-4 max-w-3xl font-display text-5xl leading-[0.92] sm:text-7xl">Bring us the date. We will help with the <span class="italic text-primary">rest.</span></h2>
                </div>
                <a href="{{ route('home') }}#inquire" class="inline-flex shrink-0 items-center gap-3 rounded-full bg-ink px-6 py-3.5 text-sm font-bold text-white">Start an inquiry</a>
            </div>
        </div>
    </section>
</x-layouts.public>
