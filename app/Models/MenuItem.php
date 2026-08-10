<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;
    protected $fillable = ['category_id','name','description','price','image','is_available','stock'];

    protected $appends = ['image_url'];

    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get full accessor URL for dish image.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('images/hero_food.png');
        }

        $img = trim($this->image);

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
}
