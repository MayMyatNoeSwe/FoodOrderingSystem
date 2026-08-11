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
        'delivery_township',
        'region_type',
        'delivery_phone',
        'status',
        'payment_method',
        'payment_status',
        'payment_screenshot',
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

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if (isset($order->payment_method)) {
                if ($order->payment_method === 'cod') {
                    $order->payment_status = 'unpaid'; // Pay on delivery
                } elseif (in_array($order->payment_method, ['kbzpay', 'wavepay'])) {
                    // Needs admin to verify screenshot before confirming
                    $order->payment_status = 'pending_verification';
                }
            }
        });
    }
}

