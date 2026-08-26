<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'images',
        'is_available',
        'stock',
        'min_stock_level',
    ];

    protected $casts = [
        'images'          => 'array',
        'is_available'    => 'boolean',
        'stock'           => 'integer',
        'min_stock_level' => 'integer',
        'price'           => 'float',
    ];

    protected $appends = ['image_url', 'all_images'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Resolve any image path/URL to a fully qualified URL.
     */
    public static function resolveImageUrl(?string $img): string
    {
        if (empty($img)) {
            return asset('images/hero_food.png');
        }

        $img = trim($img);

        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }

        if (str_starts_with($img, '/images/') || str_starts_with($img, 'images/')) {
            return asset(ltrim($img, '/'));
        }

        if (str_starts_with($img, '/storage/') || str_starts_with($img, 'storage/')) {
            return asset(ltrim($img, '/'));
        }

        return asset('storage/' . $img);
    }

    /**
     * Get primary image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            return self::resolveImageUrl($this->image);
        }

        if (!empty($this->images) && is_array($this->images) && count($this->images) > 0) {
            return self::resolveImageUrl($this->images[0]);
        }

        return asset('images/hero_food.png');
    }

    /**
     * Get all image URLs as an array.
     */
    public function getAllImagesAttribute(): array
    {
        $urls = [];

        if (!empty($this->images) && is_array($this->images)) {
            foreach ($this->images as $img) {
                if (!empty($img)) {
                    $urls[] = self::resolveImageUrl($img);
                }
            }
        }

        if (empty($urls) && !empty($this->image)) {
            $urls[] = self::resolveImageUrl($this->image);
        }

        if (empty($urls)) {
            $urls[] = asset('images/hero_food.png');
        }

        return array_values(array_unique($urls));
    }

    /**
     * Check if item is in low stock condition.
     */
    public function isLowStock(): bool
    {
        $threshold = $this->min_stock_level ?? 10;
        return $this->stock > 0 && $this->stock <= $threshold;
    }
}
