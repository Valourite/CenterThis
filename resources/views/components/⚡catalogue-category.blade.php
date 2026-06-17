<?php

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $categoryId;

    public string $categoryName;

    public int $iteration = 1;

    public int $perPage = 9;

    public function loadMore(): void
    {
        $this->perPage += 9;
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return $this->baseQuery()
            ->select(['id', 'category_id', 'name', 'slug', 'description', 'images'])
            ->with([
                'variants' => fn ($query) => $query
                    ->select(['id', 'product_id', 'label', 'quantity', 'base_rate', 'deposit_amount'])
                    ->where('active', true)
                    ->orderBy('base_rate'),
            ])
            ->orderBy('position')
            ->take($this->perPage)
            ->get();
    }

    #[Computed]
    public function total(): int
    {
        return $this->baseQuery()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    private function baseQuery()
    {
        return Product::query()
            ->where('category_id', $this->categoryId)
            ->where('active', true)
            ->whereHas('variants', fn ($query) => $query->where('active', true));
    }
};
?>

<div>
    <div class="mb-7 flex items-end justify-between gap-5">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] text-primary uppercase">
                {{ str_pad((string) $iteration, 2, '0', STR_PAD_LEFT) }} / Collection
            </p>
            <h2 class="mt-2 font-display text-5xl leading-none sm:text-6xl">{{ $categoryName }}</h2>
        </div>
        <p class="hidden text-sm text-black/45 sm:block">{{ $this->total }} {{ Str::plural('item', $this->total) }}</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->products as $product)
            @php
                $coverImage = $product->primaryImageUrl();
                $firstVariant = $product->variants->first();
            @endphp

            <article class="group overflow-hidden rounded-[1.75rem] border border-black/8 bg-surface transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-black/10">
                <a href="{{ route('catalogue.product', $product) }}" class="relative block aspect-4/3 overflow-hidden bg-surface-muted">
                    @if ($coverImage)
                        <img src="{{ $coverImage }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div @class([
                            'flex size-full items-center justify-center',
                            'bg-accent' => $loop->iteration % 4 === 1,
                            'bg-secondary' => $loop->iteration % 4 === 2,
                            'bg-primary text-white' => $loop->iteration % 4 === 3,
                            'bg-surface-strong' => $loop->iteration % 4 === 0,
                        ])>
                            <span class="font-display text-[8rem] italic leading-none opacity-20">{{ mb_substr($product->name, 0, 1) }}</span>
                        </div>
                    @endif

                    <div class="absolute inset-x-4 top-4 flex items-center justify-between gap-3">
                        <span class="rounded-full bg-ink/90 px-3 py-1.5 text-[0.65rem] font-bold tracking-[0.14em] text-white uppercase backdrop-blur">{{ $categoryName }}</span>
                        @if (count($product->images ?? []) > 1)
                            <span class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-ink backdrop-blur">{{ count($product->images) }} images</span>
                        @endif
                    </div>
                </a>

                <div class="p-6">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <h3 class="font-display text-4xl leading-none">
                                <a href="{{ route('catalogue.product', $product) }}" class="transition group-hover:text-primary">{{ $product->name }}</a>
                            </h3>
                            <p class="mt-4 line-clamp-2 text-sm leading-6 text-black/50">{{ $product->description }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-end justify-between gap-4 border-t border-black/8 pt-5">
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-black/40 uppercase">From</p>
                            <p class="mt-1 text-lg font-bold">R{{ number_format((float) $firstVariant->base_rate, 2) }} <span class="text-xs font-medium text-black/40">/ day</span></p>
                        </div>
                        <a href="{{ route('catalogue.product', $product) }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-4 py-2.5 text-xs font-bold text-white transition hover:bg-primary">
                            View item
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if ($this->total > $this->products->count())
        <div class="mt-10 flex justify-center">
            <button
                type="button"
                wire:click="loadMore"
                wire:loading.attr="disabled"
                wire:target="loadMore"
                class="inline-flex items-center gap-2 rounded-full border border-black/15 bg-white/70 px-6 py-3 text-sm font-bold transition hover:bg-primary hover:text-white disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="loadMore">Load more {{ $categoryName }}</span>
                <span wire:loading wire:target="loadMore">Loading…</span>
            </button>
        </div>
    @endif
</div>
