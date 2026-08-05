<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable=[
        'order_number',
        'user_id',
        'total_amount',
        'delivery_fee',
        'delivery_address',
        'delivery_phone',
        'status',
        'payment_method',
        'payment_status',
        'notes'
    ];
    public function user() :BelongsTo{
        return $this->belongsTo(User::class);
    }
  
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'order_items')
                    ->withPivot('quantity', 'unit_price', 'subtotal')
                    ->withTimestamps();
    }
}

