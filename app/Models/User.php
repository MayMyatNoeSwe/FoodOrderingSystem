<?php

namespace App\Models;

use App\Models\Order;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone_number',
        'city',
        'detail_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return isset($this->role) && $this->role === 'admin';
    }

    /**
     * Check if user has shop_owner role
     */
    public function isShopOwner(): bool
    {
        return isset($this->role) && $this->role === 'shop_owner';
    }

    /**
     * Check if user has rider role
     */
    public function isRider(): bool
    {
        return isset($this->role) && $this->role === 'rider';
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return isset($this->status) && $this->status === 'banned';
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return !isset($this->status) || $this->status === 'active';
    }

    /**
     * Get the orders placed by the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the deliveries assigned to the rider.
     */
    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'rider_id');
    }

    /**
     * Get the shop owned by this user (if shop_owner role).
     */
    public function ownedShop(): HasOne
    {
        return $this->hasOne(Shop::class, 'owner_id');
    }

    /**
     * Get complaints submitted by the user.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}