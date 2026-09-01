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
        'shop_id',
        'rider_id',
        'total_amount',
        'commission_amount',
        'shop_earning',
        'delivery_fee',
        'tax_amount',
        'delivery_address',
        'delivery_township',
        'region_type',
        'delivery_phone',
        'status',
        'payment_method',
        'payment_status',
        'payment_screenshot',
        'transaction_number',
        'delivery_proof_photo',
        'is_rider_settled',
        'rider_settled_at',
        'notes'
    ];
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function rider() :BelongsTo{
        return $this->belongsTo(User::class, 'rider_id');
    }
  
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class)->orderBy('created_at', 'asc');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'order_items')
                    ->withPivot('quantity', 'unit_price', 'subtotal')
                    ->withTimestamps();
    }

    /**
     * Complaints filed for this order.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->payment_status)) {
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

