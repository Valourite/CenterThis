<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'images',
        'active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'images' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            if (! $product->canBeDeleted()) {
                throw new RuntimeException($product->deleteBlockedMessage());
            }
        });

        static::updated(function (Product $product): void {
            Storage::disk('public')->delete(array_values(array_diff(
                $product->normalizeImagePaths($product->getOriginal('images')),
                $product->normalizeImagePaths($product->images),
            )));
        });

        //Only delete the images after the product has been force deleted
        static::forceDeleted(function (Product $product): void {
            Storage::disk('public')->delete($product->normalizeImagePaths($product->images));
        });
    }

    public function canBeDeleted(): bool
    {
        return ! $this->hasBookings();
    }

    public function deleteBlockedMessage(): string
    {
        return 'This product cannot be deleted because one or more variants are linked to bookings.';
    }

    public function hasBookings(): bool
    {
        return Variant::query()
            ->withTrashed()
            ->whereBelongsTo($this)
            ->whereHas('bookingItems')
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function imageUrls(): array
    {
        return array_map(
            fn (string $path): string => Storage::disk('public')->url($path),
            $this->normalizeImagePaths($this->images),
        );
    }

    public function primaryImageUrl(): ?string
    {
        $path = $this->normalizeImagePaths($this->images)[0] ?? null;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * @return list<string>
     */
    private function normalizeImagePaths(mixed $images): array
    {
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (! is_array($images)) {
            return [];
        }

        return array_values(array_filter(
            $images,
            fn (mixed $path): bool => is_string($path) && $path !== '',
        ));
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    /**
     * @return HasMany<Variant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }
}
