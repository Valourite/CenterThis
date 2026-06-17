<x-layouts.public
    title="Portfolio"
    description="See CenterThis furniture, decor, linen and equipment used at weddings, traditional ceremonies, private celebrations and corporate events."
>
    <section class="py-20 sm:py-28 lg:py-36">
        <div class="site-container grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-primary uppercase">Selected work</p>
                <h1 class="mt-5 font-display text-[clamp(4.5rem,10vw,9rem)] leading-[0.78]">Our pieces, <span class="italic text-primary">in their element.</span></h1>
            </div>
            <p class="max-w-lg text-lg leading-8 text-black/60 lg:pb-3">A glimpse at how customers, venues, planners, and decorators have used hired furniture and decor across different kinds of events.</p>
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="site-container grid auto-rows-[14rem] grid-cols-2 gap-3 sm:auto-rows-[18rem] sm:gap-5 lg:grid-cols-4">
            @foreach ([
                ['wedding.webp', 'An ivory garden ceremony', 'Weddings', 'col-span-2 row-span-2'],
                ['traditional.webp', 'A modern traditional celebration', 'Ceremonies', 'row-span-2'],
                ['celebration.webp', 'Coral in the garden', 'Private events', ''],
                ['corporate.webp', 'After-dark product launch', 'Corporate', ''],
                ['hire-collection.webp', 'The useful, beautiful things', 'Hire collection', 'col-span-2'],
                ['hero.webp', 'A long table at golden hour', 'Weddings', 'col-span-2'],
            ] as [$image, $title, $type, $classes])
                <article class="event-card group relative overflow-hidden rounded-3xl {{ $classes }}">
                    <img src="{{ asset('images/events/'.$image) }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    <div class="absolute inset-x-0 bottom-0 z-10 p-4 sm:p-6">
                        <p class="text-[0.65rem] font-bold tracking-[0.18em] text-accent uppercase">{{ $type }}</p>
                        <h2 class="mt-2 font-display text-2xl leading-none text-white sm:text-4xl">{{ $title }}</h2>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="bg-ink py-20 text-white sm:py-28">
        <div class="site-container">
            <div class="grid gap-10 lg:grid-cols-[0.7fr_1.3fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">A flexible collection</p>
                    <h2 class="mt-4 font-display text-5xl leading-[0.92] sm:text-7xl">The same pieces. <span class="italic text-secondary">Entirely different events.</span></h2>
                </div>
                <p class="max-w-2xl text-base leading-7 text-white/55 lg:justify-self-end">Our collection is designed to work across many event styles. The finished result is determined by how you, your venue, or your decorator combine and arrange the hired pieces.</p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Furniture that works hard', 'var(--color-secondary)', 'text-ink'],
                    ['Decor that changes the mood', 'var(--color-primary)', 'text-white'],
                    ['Options for every guest list', 'var(--color-accent)', 'text-ink'],
                ] as [$title, $colour, $text])
                    <div class="flex aspect-square items-center justify-center rounded-full p-7 text-center sm:p-8 {{ $text }}" style="background-color: {{ $colour }}">
                        <p class="max-w-44 font-display text-3xl leading-[0.95] sm:text-4xl">{{ $title }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 sm:py-28">
        <div class="site-container grid gap-5 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="overflow-hidden rounded-4xl">
                <img src="{{ asset('images/events/hero.webp') }}" alt="Outdoor Gauteng reception table" class="min-h-120 size-full object-cover" loading="lazy">
            </div>
            <div class="flex flex-col justify-between rounded-4xl bg-accent p-7 sm:p-10">
                <span class="font-display text-8xl italic text-primary/20">“</span>
                <div>
                    <p class="font-display text-4xl leading-tight">Choose the pieces that suit your event, then make the finished space your own.</p>
                    <a href="{{ route('catalogue') }}" class="mt-8 inline-flex rounded-full bg-ink px-6 py-3.5 text-sm font-bold text-white">Browse the hire collection</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
