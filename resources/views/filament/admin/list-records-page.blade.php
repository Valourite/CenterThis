@php
    $toneStyles = [
        'primary' => '--ct-tone: var(--color-primary); --ct-tone-soft: var(--color-primary-soft);',
        'secondary' => '--ct-tone: var(--color-secondary); --ct-tone-soft: var(--color-secondary-soft);',
        'info' => '--ct-tone: var(--color-info); --ct-tone-soft: var(--color-info-soft);',
        'success' => '--ct-tone: var(--color-success); --ct-tone-soft: var(--color-success-soft);',
        'warning' => '--ct-tone: var(--color-warning); --ct-tone-soft: var(--color-warning-soft);',
        'danger' => '--ct-tone: var(--color-danger); --ct-tone-soft: var(--color-danger-soft);',
        'gray' => '--ct-tone: var(--color-muted); --ct-tone-soft: var(--color-muted-soft);',
    ];
@endphp

<x-filament-panels::page>
    <div class="ct-admin-shell space-y-6">
        <section class="ct-admin-hero overflow-hidden rounded-4xl border border-white/70 bg-linear-to-br from-primary-strong via-primary to-secondary p-6 text-white shadow-2xl shadow-stone-950/10 ring-1 ring-black/5 dark:border-white/10 dark:from-black dark:via-primary-deep dark:to-secondary-strong sm:p-8">
            <div class="relative z-10 grid gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(24rem,34rem)] xl:items-end">
                <div class="space-y-5">
                    <div class="flex flex-wrap flex-col items-start gap-2">
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-accent">
                            {{ $this->getHeroEyebrow() }}
                        </span>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->getHeroBadges() as $badge)
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/80 ring-1 ring-white/15">
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="max-w-3xl space-y-3">
                        <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ $this->getHeroTitle() }}
                        </h1>

                        <p class="text-base leading-7 text-white/75 sm:text-lg">
                            {{ $this->getHeroDescription() }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->getHeroStats() as $stat)
                        <article
                            class="ct-admin-stat-card rounded-3xl border p-4 ring-1 backdrop-blur-md"
                            style="{{ $toneStyles[$stat['tone']] ?? $toneStyles['primary'] }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-white/65">{{ $stat['label'] }}</p>
                            </div>

                            <p class="mt-3 text-3xl font-semibold tracking-tight text-white">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-white/55">{{ $stat['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="ct-admin-table-card">
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
