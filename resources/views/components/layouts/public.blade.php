@props([
    'title' => null,
    'description' => 'Furniture, decor, linen, lighting and equipment hire for events across Gauteng.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $description }}">
        <title>{{ $title ? $title.' | CenterThis' : 'CenterThis | Events worth gathering for' }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen overflow-x-hidden antialiased">
        <header
            x-data="{ open: false, cartCount: {{ app(\App\Support\RentalCart::class)->count() }} }"
            x-on:cart-updated.window="cartCount = $event.detail.count"
            class="sticky top-0 z-50 border-b border-black/7 bg-canvas/88 backdrop-blur-xl"
        >
            <div class="site-container flex h-18 items-center justify-between gap-6">
                <x-site-logo class="relative z-50"/>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
                    @foreach ([
                        'home' => 'Home',
                        'about' => 'About',
                        'services' => 'Services',
                        'portfolio' => 'Portfolio',
                        'catalogue' => 'Hire collection',
                    ] as $route => $label)
                        <a
                            href="{{ route($route) }}"
                            @class([
                                'rounded-full px-4 py-2 text-sm font-semibold transition',
                                'bg-ink text-white' => request()->routeIs($route),
                                'text-ink/65 hover:bg-white/70 hover:text-ink' => ! request()->routeIs($route),
                            ])
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="hidden lg:block">
                    <a href="{{ route('cart') }}" class="group inline-flex items-center gap-2 rounded-full bg-accent px-5 py-2.5 text-sm font-bold transition hover:bg-ink hover:text-white">
                        Hire basket
                        <span x-cloak x-show="cartCount > 0" x-text="cartCount" class="flex min-w-5 items-center justify-center rounded-full bg-ink px-1.5 py-0.5 text-[0.65rem] text-white group-hover:bg-white group-hover:text-ink"></span>
                        <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <button
                    type="button"
                    class="relative z-50 flex size-11 items-center justify-center rounded-full border border-black/10 bg-white/60 lg:hidden"
                    x-on:click="open = ! open"
                    x-bind:aria-expanded="open"
                    aria-controls="mobile-navigation"
                    aria-label="Toggle navigation"
                >
                    <svg x-show="! open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 8h16M4 16h16" stroke-linecap="round"/>
                    </svg>
                    <svg x-cloak x-show="open" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div
                id="mobile-navigation"
                x-cloak
                x-show="open"
                x-transition.opacity
                class="fixed inset-x-0 top-18 h-[calc(100dvh-4.5rem)] bg-canvas lg:hidden"
            >
                <nav class="site-container flex h-full flex-col justify-between py-8" aria-label="Mobile navigation">
                    <div class="grid">
                        @foreach ([
                            'home' => 'Home',
                            'about' => 'About us',
                            'services' => 'Services',
                            'portfolio' => 'Portfolio',
                            'catalogue' => 'Hire collection',
                            'cart' => 'Hire basket',
                        ] as $route => $label)
                            <a href="{{ route($route) }}" class="border-b border-black/10 py-4 font-display text-4xl {{ request()->routeIs($route) ? 'text-primary' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="grid gap-4">
                        <p class="text-sm leading-6 text-black/55">Furniture, decor and equipment hire across Gauteng.</p>
                        <a href="{{ route('cart') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-ink px-6 py-4 text-sm font-bold text-white">
                            Review hire basket
                            <span x-cloak x-show="cartCount > 0" x-text="cartCount" class="rounded-full bg-accent px-2 py-0.5 text-xs text-ink"></span>
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-ink text-white">
            <div class="site-container grid gap-14 py-16 sm:py-20 lg:grid-cols-[1.3fr_0.7fr_0.7fr]">
                <div>
                    <x-site-logo class="text-white [&>span:first-child]:bg-accent [&>span:first-child]:text-ink [&>span:last-child_span]:text-accent"/>
                    <p class="mt-6 max-w-md font-display text-4xl leading-[1.02] sm:text-5xl">Make room for the moment.</p>
                    <p class="mt-5 max-w-md text-sm leading-6 text-white/55">Furniture, decor, linen, lighting, audio, and bespoke items available to hire for events throughout Gauteng.</p>
                </div>

                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] text-accent uppercase">Explore</p>
                    <div class="mt-5 grid gap-3 text-sm text-white/65">
                        <a class="transition hover:text-white" href="{{ route('about') }}">Our story</a>
                        <a class="transition hover:text-white" href="{{ route('services') }}">What we do</a>
                        <a class="transition hover:text-white" href="{{ route('portfolio') }}">Recent work</a>
                        <a class="transition hover:text-white" href="{{ route('catalogue') }}">Hire collection</a>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] text-accent uppercase">Find us</p>
                    <div class="mt-5 grid gap-3 text-sm text-white/65">
                        <p>Gauteng, South Africa</p>
                        <a class="transition hover:text-white" href="mailto:hello@centerthis.co.za">hello@centerthis.co.za</a>
                        <p>Visits by appointment</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10">
                <div class="site-container flex flex-col gap-3 py-5 text-xs text-white/35 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ now()->year }} CenterThis. All moments reserved.</p>
                    <p>Pretoria · Johannesburg · Centurion · Midrand · Gauteng</p>
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
