<x-layouts.public :title="$product->name" :description="$product->description ?: 'View details, images, variants, and hire rates for ' . $product->name . '.'">
    @php($images = $product->imageUrls())

    <section class="border-b border-black/8 bg-surface py-5">
        <div class="site-container flex flex-wrap items-center gap-2 text-xs font-semibold text-black/45">
            <a href="{{ route('catalogue') }}" class="transition hover:text-primary">Hire collection</a>
            <span>/</span>
            @if ($product->category)
                <a href="{{ route('catalogue') }}#{{ $product->category->slug }}"
                    class="transition hover:text-primary">{{ $product->category->name }}</a>
                <span>/</span>
            @endif
            <span class="text-ink">{{ $product->name }}</span>
        </div>
    </section>

    @if (session('cart_status'))
        <div class="site-container pt-6">
            <div
                class="flex flex-col gap-3 rounded-2xl bg-primary px-5 py-4 text-sm text-white sm:flex-row sm:items-center sm:justify-between">
                <p>{{ session('cart_status') }}</p>
                <a href="{{ route('cart') }}" class="font-bold text-accent underline underline-offset-4">View hire
                    basket</a>
            </div>
        </div>
    @endif

    @if ($errors->has('quantity') || $errors->has('variant_id'))
        <div class="site-container pt-6">
            <p class="rounded-2xl bg-danger/10 px-5 py-4 text-sm font-semibold text-danger">
                {{ $errors->first('quantity') ?: $errors->first('variant_id') }}
            </p>
        </div>
    @endif

    <section class="py-8 sm:py-14">
        <div class="site-container grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)] lg:items-start">
            <div x-data="{ activeImage: @js($images[0] ?? null) }" class="grid gap-3">
                <div class="relative aspect-4/3 overflow-hidden rounded-4xl bg-surface-muted">
                    @if ($images !== [])
                        <template x-if="activeImage">
                            <img :src="activeImage" alt="{{ $product->name }}" class="size-full object-cover">
                        </template>
                    @else
                        <div class="flex size-full items-center justify-center bg-accent">
                            <span
                                class="font-display text-[12rem] italic leading-none text-ink/15">{{ mb_substr($product->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                @if (count($images) > 1)
                    <div class="grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach ($images as $image)
                            <button type="button" x-on:click="activeImage = @js($image)"
                                x-bind:class="activeImage === @js($image) ? 'ring-2 ring-primary ring-offset-2 ring-offset-canvas' : 'opacity-70 hover:opacity-100'"
                                class="aspect-square overflow-hidden rounded-xl bg-surface-muted transition"
                                aria-label="View image {{ $loop->iteration }} of {{ $product->name }}">
                                <img src="{{ $image }}" alt="" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:sticky lg:top-24">
                @if ($product->category)
                    <p class="text-xs font-bold tracking-[0.2em] text-primary uppercase">{{ $product->category->name }}</p>
                @endif
                <h1 class="mt-3 font-display text-6xl leading-[0.88] sm:text-7xl">{{ $product->name }}</h1>
                <p class="mt-6 text-base leading-8 text-black/60">{{ $product->description }}</p>

                <div class="mt-8 rounded-[1.75rem] bg-ink p-5 text-white sm:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold tracking-[0.18em] text-accent uppercase">Choose a variant</p>
                            <h2 class="mt-2 font-display text-3xl">Add to your hire basket</h2>
                        </div>
                        <a href="{{ route('cart') }}"
                            class="text-xs font-bold text-white/55 underline underline-offset-4 hover:text-white">View
                            basket</a>
                    </div>

                    <div class="mt-6 grid gap-3">
                        @foreach ($product->variants as $variant)
                            <form method="POST" action="{{ route('cart.items.store') }}"
                                class="rounded-2xl border border-white/12 bg-white/5 p-4">
                                @csrf
                                <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                                <div class="flex items-start justify-between gap-5">
                                    <div>
                                        <p class="font-bold">{{ $variant->label }}</p>
                                        @if ($variant->optionValues->isNotEmpty())
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach ($variant->optionValues as $value)
                                                    <span
                                                        class="rounded-full bg-white/10 px-2.5 py-1 text-[0.65rem] text-white/60">{{ $value->option->name }}:
                                                        {{ $value->value }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">R{{ number_format((float) $variant->base_rate, 2) }} <span
                                                class="text-xs font-medium text-white/40">/ day</span></p>
                                        <p class="mt-1 text-xs text-white/45">Deposit
                                            R{{ number_format((float) $variant->deposit_amount, 2) }} each</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <label for="quantity-{{ $variant->id }}" class="sr-only">Quantity for
                                        {{ $variant->label }}</label>
                                    <input id="quantity-{{ $variant->id }}" name="quantity" type="number" min="1"
                                        max="{{ $variant->quantity }}" value="1"
                                        class="w-20 rounded-full border border-white/15 bg-white/8 px-3 py-2.5 text-center text-sm font-bold text-white outline-none focus:border-accent">
                                    <button type="submit"
                                        class="inline-flex flex-1 items-center justify-center rounded-full bg-accent px-4 py-2.5 text-xs font-bold text-ink transition hover:bg-white">
                                        Add to hire basket
                                    </button>
                                </div>
                                <p class="mt-3 text-xs text-white/35">{{ $variant->quantity }} held in the collection.
                                    Date-specific availability is checked in your basket.</p>
                            </form>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-surface p-4">
                        <p class="font-bold">VAT-exclusive</p>
                        <p class="mt-1 text-xs leading-5 text-black/45">Rates shown exclude VAT.</p>
                    </div>
                    <div class="rounded-2xl bg-surface p-4">
                        <p class="font-bold">Refundable deposit</p>
                        <p class="mt-1 text-xs leading-5 text-black/45">Displayed separately in your basket.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-black/8 py-14 sm:py-20">
            <div class="site-container">
                <div class="flex items-end justify-between gap-5">
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-primary uppercase">More in the collection</p>
                        <h2 class="mt-2 font-display text-5xl">You may also need</h2>
                    </div>
                    <a href="{{ route('catalogue') }}"
                        class="hidden text-sm font-bold underline underline-offset-4 sm:block">View all items</a>
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedProducts as $relatedProduct)
                        <a href="{{ route('catalogue.product', $relatedProduct) }}"
                            class="group overflow-hidden rounded-3xl bg-surface">
                            <div class="aspect-4/3 overflow-hidden bg-surface-muted">
                                @if ($relatedProduct->primaryImageUrl())
                                    <img src="{{ $relatedProduct->primaryImageUrl() }}" alt="{{ $relatedProduct->name }}"
                                        class="size-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex size-full items-center justify-center bg-surface-strong">
                                        <span
                                            class="font-display text-7xl italic text-ink/15">{{ mb_substr($relatedProduct->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-end justify-between gap-4 p-5">
                                <div>
                                    <h3 class="font-display text-3xl">{{ $relatedProduct->name }}</h3>
                                    <p class="mt-2 text-sm text-black/45">From
                                        R{{ number_format((float) $relatedProduct->variants->first()->base_rate, 2) }} / day</p>
                                </div>
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-ink text-white transition group-hover:bg-primary">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>