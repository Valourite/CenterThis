<x-layouts.public
    title="Hire Collection"
    description="Browse furniture, decor, linen, lighting, and equipment available to hire across Gauteng."
>
    <section class="relative isolate overflow-hidden bg-ink py-18 text-white sm:py-26">
        <img src="{{ asset('images/events/hire-collection.webp') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover opacity-25" aria-hidden="true">
        <div class="absolute inset-0 -z-10 bg-linear-to-r from-ink via-ink/92 to-ink/55"></div>
        <div class="site-container grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
            <div>
                <p class="text-xs font-bold tracking-[0.22em] text-accent uppercase">The hire collection</p>
                <h1 class="mt-5 max-w-5xl font-display text-[clamp(4.2rem,9vw,8rem)] leading-[0.8]">
                    Find the pieces that <span class="italic text-primary-soft">fit the moment.</span>
                </h1>
            </div>
            <div class="max-w-lg lg:justify-self-end lg:pb-3">
                <p class="text-lg leading-8 text-white/65">Explore the collection by category, open any item for full details and images, then choose the exact variant and quantity you need.</p>
                <a href="{{ route('cart') }}" class="mt-6 inline-flex items-center gap-3 rounded-full bg-accent px-5 py-3 text-sm font-bold text-ink">
                    Review hire basket
                    @if (app(\App\Support\RentalCart::class)->count() > 0)
                        <span class="rounded-full bg-ink px-2 py-0.5 text-xs text-white">{{ app(\App\Support\RentalCart::class)->count() }}</span>
                    @endif
                </a>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <div class="sticky top-18 z-40 overflow-x-auto border-b border-black/10 bg-canvas/94 backdrop-blur">
            <nav class="site-container flex min-w-max gap-2 py-3" aria-label="Hire categories">
                @foreach ($categories as $category)
                    <a href="#{{ $category->slug }}" class="rounded-full border border-black/10 bg-white/60 px-4 py-2 text-xs font-bold transition hover:bg-primary hover:text-white">
                        {{ $category->name }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    @if (session('cart_status'))
        <div class="site-container pt-8">
            <div class="flex flex-col gap-3 rounded-2xl bg-primary px-5 py-4 text-sm text-white sm:flex-row sm:items-center sm:justify-between">
                <p>{{ session('cart_status') }}</p>
                <a href="{{ route('cart') }}" class="font-bold text-accent underline underline-offset-4">View hire basket</a>
            </div>
        </div>
    @endif

    <section class="py-14 sm:py-22">
        <div class="site-container grid gap-18">
            @forelse ($categories as $category)
                <section id="{{ $category->slug }}" class="scroll-mt-36">
                    <livewire:catalogue-category
                        :key="'category-'.$category->id"
                        :category-id="$category->id"
                        :category-name="$category->name"
                        :iteration="$loop->iteration"
                    />
                </section>
            @empty
                <div class="rounded-4xl bg-surface p-8 text-center sm:p-16">
                    <p class="font-display text-5xl">The collection is being arranged.</p>
                    <p class="mx-auto mt-4 max-w-lg text-black/55">Our hire pieces will appear here as soon as the catalogue has been loaded.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="pb-20 sm:pb-28">
        <div class="site-container">
            <div class="grid gap-8 rounded-4xl bg-primary p-7 text-white sm:p-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="text-xs font-bold tracking-[0.2em] text-primary-soft uppercase">Simple hire process</p>
                    <p class="mt-4 max-w-md text-sm leading-7 text-white/60">Open an item, select a variant and quantity, then choose the dates for the whole basket. Availability and pricing are calculated before checkout.</p>
                </div>
                <h2 class="font-display text-5xl leading-[0.92] sm:text-7xl">One basket. One date range. <span class="italic text-accent">Your event.</span></h2>
            </div>
        </div>
    </section>
</x-layouts.public>
