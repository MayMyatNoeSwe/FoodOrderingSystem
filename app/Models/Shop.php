<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'address',
        'phone',
        'email',
        'opening_hours',
        'status',
        'owner_id',
    ];

    protected $casts = [
        'opening_hours' => 'array',
    ];

    /**
     * The shop's owner (a user with role = shop_owner).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }


    /**
     * Menu items belonging to this shop.
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Orders placed for this shop.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if the shop is open for business.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get a fully qualified logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if (empty($this->logo)) {
            return asset('images/hero_food.png');
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset($this->logo);
    }

    /**
     * Get a fully qualified cover image URL.
     */
    public function getCoverImageUrlAttribute(): string
    {
        if (empty($this->cover_image)) {
            return asset('images/hero_food.png');
        }
        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }
        return asset($this->cover_image);
    }
}
